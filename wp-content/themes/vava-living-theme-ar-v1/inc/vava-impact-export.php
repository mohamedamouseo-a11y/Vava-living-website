<?php
/**
 * Questionnaire XLSX export for VAVA booking responses.
 *
 * @package VAVA_Living
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_action_vava_export_impact_questionnaire', 'vava_handle_export_impact_questionnaire' );

function vava_questionnaire_export_field_map( array $snapshot, string $language ): array {
    $fields = array();

    foreach ( (array) ( $snapshot['groups'] ?? array() ) as $group ) {
        foreach ( (array) ( $group['fields'] ?? array() ) as $field ) {
            $field = (array) $field;
            $id    = (string) ( $field['id'] ?? '' );
            if ( '' === $id || isset( $fields[ $id ] ) ) {
                continue;
            }
            $field['export_label'] = (string) ( $field['label'][ $language ] ?? $field['label']['ar'] ?? $field['label']['en'] ?? $id );
            $fields[ $id ]         = $field;
        }
    }

    return $fields;
}

function vava_questionnaire_export_answer( array $field, $answer, string $language ): string {
    if ( is_array( $answer ) ) {
        $answer = array_map(
            static function ( $value ) use ( $field, $language ): string {
                return vava_questionnaire_export_answer( $field, $value, $language );
            },
            $answer
        );
        return implode( ', ', array_filter( $answer, 'strlen' ) );
    }

    $answer = (string) $answer;
    foreach ( (array) ( $field['options'] ?? array() ) as $option ) {
        $option = (array) $option;
        if ( $answer === (string) ( $option['value'] ?? '' ) ) {
            return (string) ( $option[ $language ] ?? $option['ar'] ?? $option['en'] ?? $answer );
        }
    }

    return $answer;
}

function vava_questionnaire_export_cell( $value ): string {
    $value = (string) $value;
    return preg_match( '/^[=+\-@]/', $value ) ? "'" . $value : $value;
}

function vava_handle_export_impact_questionnaire() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized access.', 403 );
    }
    check_admin_referer( 'vava_export_impact_questionnaire', 'vava_export_nonce' );

    $type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'impact'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ( ! in_array( $type, array( 'beginning', 'midpoint', 'impact' ), true ) ) {
        wp_die( 'Invalid questionnaire type.', 400 );
    }

    global $wpdb;
    $submissions = $wpdb->get_results(
        "SELECT pm.post_id, pm.meta_key, pm.meta_value, p.post_date
         FROM {$wpdb->postmeta} pm
         JOIN {$wpdb->posts} p ON pm.post_id = p.ID
         WHERE pm.meta_key IN ( '_vava_booking_questionnaire', '_vava_booking_impact_questionnaire' )
         ORDER BY p.post_date DESC"
    );

    $responses = array();
    $fields    = array();
    foreach ( $submissions as $submission ) {
        $data = maybe_unserialize( $submission->meta_value );
        if ( ! is_array( $data ) || $type !== sanitize_key( (string) ( $data['type'] ?? '' ) ) || ! isset( $data['answers'] ) || ! is_array( $data['answers'] ) ) {
            continue;
        }

        $language  = 'en' === (string) ( $data['language'] ?? '' ) ? 'en' : 'ar';
        $field_map = vava_questionnaire_export_field_map( (array) ( $data['snapshot'] ?? array() ), $language );
        foreach ( $field_map as $field_id => $field ) {
            if ( ! isset( $fields[ $field_id ] ) ) {
                $fields[ $field_id ] = $field;
            }
        }
        $responses[] = array(
            'booking_id' => (int) $submission->post_id,
            'data'       => $data,
            'fields'     => $field_map,
            'language'   => $language,
            'post_date'  => (string) $submission->post_date,
        );
    }

    $header = array( 'Booking ID', 'Customer Name', 'Customer Email', 'Service', 'Session Date', 'Status', 'Language', 'Submitted At' );
    foreach ( $fields as $field ) {
        $header[] = (string) $field['export_label'];
    }

    if ( ! class_exists( 'ZipArchive' ) ) {
        wp_die( 'XLSX export requires the PHP ZipArchive extension.', 500 );
    }

    $rows = array();
    foreach ( $responses as $response ) {
        $booking_id = $response['booking_id'];
        $customer   = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
        $row        = array(
            (string) $booking_id,
            (string) ( $customer['name'] ?? '' ),
            (string) ( $customer['email'] ?? '' ),
            (string) get_post_meta( $booking_id, '_vava_booking_service_title', true ),
            (string) get_post_meta( $booking_id, '_vava_booking_date', true ),
            (string) get_post_meta( $booking_id, '_vava_booking_status', true ),
            $response['language'],
            (string) ( $response['data']['completed_at'] ?? $response['post_date'] ),
        );
        foreach ( $fields as $field_id => $field ) {
            $answer = $response['data']['answers'][ $field_id ] ?? '';
            $row[]  = vava_questionnaire_export_answer( (array) ( $response['fields'][ $field_id ] ?? $field ), $answer, $response['language'] );
        }
        $rows[] = $row;
    }

    $filename = 'vava-' . $type . '-questionnaire-responses-' . gmdate( 'Y-m-d' ) . '.xlsx';

    if ( ! function_exists( 'wp_tempnam' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    $tmp = wp_tempnam( 'vava-xlsx' );
    if ( ! $tmp ) {
        wp_die( 'Unable to create temporary file for XLSX export.', 500 );
    }

    // Diagnostic: verify temp file and directory are writable
    $tmp_dir = dirname( $tmp );
    if ( ! is_writable( $tmp_dir ) ) {
        @unlink( $tmp );
        wp_die( 'Temp directory not writable: ' . $tmp_dir, 500 );
    }
    if ( ! touch( $tmp ) ) {
        @unlink( $tmp );
        wp_die( 'Cannot write to temp file: ' . $tmp, 500 );
    }

    $zip = new ZipArchive();
    $open_result = $zip->open( $tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE );
    if ( true !== $open_result ) {
        $error_msg = 'Unknown';
        switch ( $open_result ) {
            case ZipArchive::ER_EXISTS: $error_msg = 'File already exists'; break;
            case ZipArchive::ER_INCONS: $error_msg = 'Inconsistent archive'; break;
            case ZipArchive::ER_INVAL:  $error_msg = 'Invalid argument'; break;
            case ZipArchive::ER_MEMORY: $error_msg = 'Memory allocation failed'; break;
            case ZipArchive::ER_NOENT:  $error_msg = 'No such file'; break;
            case ZipArchive::ER_NOZIP:  $error_msg = 'Not a zip archive'; break;
            case ZipArchive::ER_OPEN:   $error_msg = 'Cannot open file'; break;
            case ZipArchive::ER_READ:   $error_msg = 'Read error'; break;
            case ZipArchive::ER_SEEK:   $error_msg = 'Seek error'; break;
        }
        @unlink( $tmp );
        wp_die( 'ZipArchive::open failed (' . $error_msg . '): ' . $tmp, 500 );
    }

    $escape_xml = static function ( string $value ): string {
        $value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value );
        return htmlspecialchars( $value, ENT_XML1 | ENT_QUOTES, 'UTF-8' );
    };

    $col_letter = static function ( int $index ): string {
        $letter = '';
        $index++;
        while ( $index > 0 ) {
            $index--;
            $letter  = chr( 65 + ( $index % 26 ) ) . $letter;
            $index   = intdiv( $index, 26 );
        }
        return $letter;
    };

    // [Content_Types].xml
    $zip->addFromString( '[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        . '</Types>' );

    // _rels/.rels
    $zip->addFromString( '_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '</Relationships>' );

    // xl/_rels/workbook.xml.rels
    $zip->addFromString( 'xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
        . '</Relationships>' );

    // xl/workbook.xml
    $zip->addFromString( 'xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets><sheet name="Responses" sheetId="1" r:id="rId1"/></sheets>'
        . '</workbook>' );

    // xl/worksheets/sheet1.xml — inline strings for Unicode safety
    $sheet  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $sheet .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
    $sheet .= '<sheetData>';

    // Header row
    $sheet .= '<row r="1">';
    foreach ( $header as $ci => $cell_value ) {
        $ref = $col_letter( $ci ) . '1';
        $sheet .= '<c r="' . $ref . '" t="inlineStr"><is><t>' . $escape_xml( (string) $cell_value ) . '</t></is></c>';
    }
    $sheet .= '</row>';

    // Data rows
    foreach ( $rows as $ri => $row ) {
        $row_num = $ri + 2;
        $sheet  .= '<row r="' . $row_num . '">';
        foreach ( $row as $ci => $cell_value ) {
            $ref = $col_letter( $ci ) . $row_num;
            $safe = vava_questionnaire_export_cell( (string) $cell_value );
            $sheet .= '<c r="' . $ref . '" t="inlineStr"><is><t>' . $escape_xml( $safe ) . '</t></is></c>';
        }
        $sheet .= '</row>';
    }

    $sheet .= '</sheetData></worksheet>';
    $zip->addFromString( 'xl/worksheets/sheet1.xml', $sheet );

    $zip->close();

    header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
    header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
    header( 'Content-Length: ' . (string) filesize( $tmp ) );
    header( 'Cache-Control: no-cache, no-store, must-revalidate' );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );

    readfile( $tmp );
    @unlink( $tmp );
    exit;
}
