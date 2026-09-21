<?php
/** Homepage footer wrapper around the shared footer component. */
defined( 'ABSPATH' ) || exit;
?>
</main>
</div>
<?php
// Render the shared footer after the slide-stage and app container close.
// It must live outside #stage/#app so the slide-deck transform and the
// overflow:hidden on body/.app can never clip or hide it.
get_template_part( 'template-parts/shared-footer' );
?>
<?php wp_footer(); ?>
</body>
</html>