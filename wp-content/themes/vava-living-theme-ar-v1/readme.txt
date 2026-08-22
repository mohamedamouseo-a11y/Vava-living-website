VAVA Living Arabic — Homepage V1

Current scope:
- Arabic homepage converted to front-page.php.
- Dedicated homepage header and footer preserve the approved one-page section navigation.
- Original CSS, JavaScript, images and video are loaded through WordPress.
- Homepage links target the future dedicated WordPress page slugs.
- Internal page templates and the English version are intentionally not converted yet.

Installation:
1. Upload the ZIP from Appearance > Themes > Add New > Upload Theme.
2. Activate “VAVA Living Arabic”.
3. Open the site homepage.

The registered Arabic menu locations will be used by the unified internal header and footer in later phases.

Release 1.6.2 fixes:
- Correct internal menu URLs when WordPress is installed in a subdirectory, avoiding duplicated paths such as /vavaliving/vavaliving/.
- Normalize legacy /en/ internal URLs while preserving query strings and fragments.
- Apply the same URL normalization to header, footer, homepage, About VAVA, and Paths VAVA buttons.
- Rebuild the Paths VAVA editor layout so its live preview, language switcher, update action, and section navigation behave consistently with the homepage and About VAVA editors.


Release 1.6.4 updates:
- Replace the save/update success notice with the approved centered green-wave confirmation design.
- Simplify selected About VAVA introduction and description fields to plain multiline textareas.
- Keep advanced editors aligned with the homepage toolbar and correct the paragraph-style selector width.
- Make feature and vision cards addable, removable, sortable, and no longer locked to fixed counts.
- Use one shared internal-page language switch design for About VAVA and VAVA Paths.
- Keep the homepage header on section anchors while internal headers navigate only to real WordPress pages.
- Keep the shared footer sourced exclusively from homepage footer settings across all managed pages.

Release 1.6.5 fixes:
- Restore the About VAVA feature and vision groups to their approved fixed counts: four feature cards and three vision cards.
- Remove the visible fixed-count wording without adding add/delete controls.
- Align the About VAVA vision and closing-invitation live previews with the actual frontend section layouts.
- Complete the VAVA Paths previews so supporting copy, package guidance, every package detail, comparison details, FAQ answers, and closing notes are visible.
- Keep preview updates live for text, rich text, featured states, and comparison feature availability.

Release 1.6.6 bilingual consistency fixes:
- Treat functional and structural choices as shared settings across Arabic and English while keeping text content language-specific.
- Use the footer menu selected from homepage settings in both languages, including fallback from legacy Arabic/English menu fields.
- Translate managed WordPress page labels in shared menus and open linked pages in the active VAVA language.
- Share homepage button destinations and journal source settings between both language panes.
- Share About VAVA invitation button link type, selected WordPress page, and manual destination between both languages.
- Keep fixed About VAVA feature and vision card ordering aligned between both languages while preserving translated card content.
- Share VAVA Paths package links, featured states, comparison availability states, closing links, and fixed-list ordering between both languages.
- Fall back to the available internal-header menu when only one language menu has been assigned.


Release 1.7.0 — VAVA Selections page:
- Added a new bilingual “VAVA Selections” internal page based on the approved Arabic and English static shop pages.
- Uses the shared internal-page header and the global footer managed from homepage settings.
- Replaced the former “available now” area with two configurable selection blocks: digital products and tangible selections.
- Each selection block reveals its own products inside the same page without navigating away.
- Added bilingual page settings with live previews modeled on the About VAVA editor.
- Product titles, descriptions, currency labels, and button text are localized; images, prices, visibility, order, and linked WordPress pages are shared across both languages.
- Internal product links automatically retain the active Arabic or English site language.
- Added initial four digital products from the approved static shop content; tangible selections remain ready for admin-managed products.

Release 1.7.1 — VAVA Selections collection presentation:
- Replaced the traditional two-card grid with the approved alternating image-and-overlay layout.
- Removed decorative collection icons and reduced the visual image footprint.
- Collection panels open and close without automatic page scrolling.
- Updated the WordPress live preview to match the approved front-end composition.

Release 1.7.2 — VAVA Selections active collection state:
- Strengthened the open collection state by inverting the text card to the VAVA olive palette.
- Active collection titles and descriptions switch to white and soft-white tones.
- Active collection buttons switch to a white background with olive text in both Arabic and English.
- Kept the current viewport fixed while collections open and close, including the responsive mobile layout.

Release 1.7.3 — VAVA Selections tangible active colour:
- Keeps the digital-products active card in the approved olive identity.
- Uses #cf7d65 for the tangible-selections active text card.
- Keeps active text white and changes the tangible CTA to a white button with coral text.


Release 1.7.4 — Remove VAVA Selections closing section:
- Removed the closing-section tab and all of its Arabic and English fields from the page editor.
- Removed the closing-section live preview from WordPress administration.
- Removed the closing section from the public VAVA Selections page so the shared footer follows the products area directly.
- Added a one-time cleanup for legacy closing-section values and removed the retired section-specific styles.


Release 1.8.0 — VAVA Journal page:
- Added the bilingual VAVA Journal page with a two-tab advanced editor.
- Added shared category selection, article count, and instant in-page AJAX pagination.
- Added accurate live previews for the Hero and Articles sections.
- Removed the static Why this space section from the WordPress Journal page.

Release 1.8.1 — Editorial Journal layout and pagination:
- Adopted the approved editorial composition with one featured article and a supporting article grid.
- Places the featured article on the right in Arabic and on the left in English.
- Converted article links into coral buttons with white text and subtle shine interactions.
- Added refined article-card backgrounds inspired by the About VAVA cards, with gentle lift and image motion.
- Rebuilt AJAX pagination with coral active, previous, and next controls plus a live page-status label.
- Updated the WordPress live preview to match the new front-end article layout and pagination.


Release 1.8.2 — Fixed featured Journal article:
- Added a shared featured-article selector to the Journal article settings.
- Keeps the chosen featured article fixed across every AJAX pagination page and excludes it by post ID from the paginated query and page count.
- Falls back to the latest article matching the selected categories when no article is chosen manually.
- Separates the featured article from the regular article grid so it no longer stretches to match adjacent rows or creates empty internal space.
- Uses a fixed responsive featured-image frame with background cover cropping, so unusually tall, square, or wide uploads cannot distort the page layout.
- Updated the WordPress live preview to reflect the fixed featured article, exclusion logic, editorial composition, and controlled image frame.

Release 1.8.3 — Journal card alignment and resilient featured selector:
- Replace the native featured-article dropdown with a searchable, width-safe picker that truncates long selected titles, shows full titles on hover, and displays choices on up to two lines.
- Align the regular article grid with the featured article from the same top edge by removing inherited Journal grid spacing.
- Remove article dates from featured and regular cards.
- Move the category and Read Article action into one footer row, with direction-aware placement in Arabic and English.
- Alternate regular-card action colors between coral and olive while keeping the featured action coral.
- Update the live admin preview to match the frontend structure, colors, and metadata placement.

Release 1.8.4 — Journal picker and pagination refinements:
- Fixed live featured-article filtering with Arabic and English normalization, multi-word matching, and native search-field clearing.
- Opens the featured-article results as a fixed wide panel to the visual left, over the preview area, without shifting the editor layout.
- Expands long article choices to three controlled lines and keeps unmatched choices fully hidden.
- Changes the featured badge to the approved coral colour in the frontend and live preview.
- Removes the redundant page-status sentence beneath pagination.
- Uses the VAVA olive colour for Previous, Next, and the current page, with shine-only hover feedback and no vertical movement.


Release 1.9.0 — Journal controls and Contact page:
- Reorganized the first Journal article-copy fields into the approved professional card layout.
- Combined the fixed featured article and articles-per-page controls into one compact settings card.
- Dedicated the full categories area to category selection, article distribution mode, and sortable category priority.
- Added priority-based category sequencing and a stable random category mix that remain consistent across AJAX pagination.
- Added a new bilingual Contact Us page using the shared internal header and homepage-managed footer.
- Added Hero, Contact Form, and Message Guide settings only; the static Open Space section is intentionally omitted.
- Added a secure contact form with validation, nonce, honeypot, rate limiting, configurable recipient email, and no message-body database storage.
- Reused homepage social links automatically in the Contact page and its live preview.

Release 1.9.1 — Flexible protected Contact form builder:
- Reorganized Contact form copy into selector-based editors for interface labels and success/failure messages.
- Added a bilingual drag-and-drop form builder with protected Name, Email, and Message fields that cannot be deleted, hidden, made optional, or changed to unsafe field types.
- Added removable text, phone, select-list, and textarea fields with localized labels, placeholders, options, visibility, required state, order, and half-row/full-row layout controls.
- Added responsive two-fields-per-row composition on desktop while stacking every field safely on mobile.
- Added strict browser and server-side email validation, input length limits, sanitized dynamic fields, and structured HTML email notifications containing every submitted answer, language, date, and source page.
- Added press-and-hold verification with a server-issued challenge and one-time token before revealing the real Send button.
- Strengthened spam resistance with nonce validation, honeypot detection, minimum form age, attempt throttling, cooldowns, duplicate-message blocking, and header-injection protection.
- Matched Contact social icons to the shared footer component in the public page and live preview.
- Updated the Contact live preview to follow dynamic field order, width, visibility, localized copy, and hold-verification state.

Release 1.9.2 — Contact builder accordion and stable field order:
- Converted every Contact form field card into a compact accordion, keeping only one field editor open at a time and opening newly added fields automatically.
- Limited drag-and-drop to additional fields only, with a dedicated sortable area, a clear drag handle, visible placeholder feedback, and persistent bilingual order.
- Locked Name, Email, Subject, and Message into a safe form structure; Subject can no longer be moved or deleted, and every added field is always inserted before Message.
- Replaced text deletion controls with a compact trash icon and a confirmation step for additional fields.
- Fixed press-and-hold verification for mouse, touch, and keyboard by adding pointer capture, reliable press-state tracking, cancellation handling, and server challenge synchronization.
- Allows the hold verification to run before form completion while keeping final browser and server validation enforced at submission.
- Updated the live preview and saved schema normalization so the public form, email output, and both languages always use the same protected field structure and additional-field order.

Release 1.9.3 — Contact builder drag fix and dynamic message guide:
- Fixed additional-field drag and drop by overriding jQuery UI's button cancellation and using a stable cloned helper.
- Redesigned the form field accordion and select controls with a clearer professional admin layout.
- Moved success and failure messages into the unified Form Copy selector and removed the separate messages block.
- Converted Message Guide cards from three fixed cards into addable, removable, sortable accordion cards.
- Added field-to-guide-card linking, shared structure across both languages, and live preview updates.
- Added contextual highlighting of linked guide cards when visitors focus a form field.

Release 1.9.4 — Contact sorting containment and guide-card polish:
- Constrained additional-field and guide-card sorting helpers to their own builders so dragged cards no longer expand across the WordPress screen or overlap the admin sidebar.
- Uses compact accordion-header helpers and matching placeholders during drag operations while preserving the saved order.
- Aligns the Form Copy selector and long success/failure message editor from the same top edge in Arabic and English.
- Refined Message Guide card spacing, headers, badges, linked-field controls, open states, and responsive behaviour.
- Moved each guide card's visibility control into the card header as an accessible eye icon beside Delete.
- Adds immediate visible/hidden feedback in the editor while preserving the card content and shared bilingual structure.

Release 1.9.5 — Inline Journal article reader:
- Opens article details inside the Journal page through AJAX instead of leaving for the WordPress single-post screen.
- Uses the approved centered editorial card with article copy and a fixed-ratio image, without a duplicate top close control.
- Adds one olive bottom toolbar containing Previous article, Close article, and Next article.
- Keeps the originating pagination page and scroll position, so closing an article returns visitors to the same results page and place.
- Supports mouse, keyboard, browser Back/Forward, Arabic and English, and progressive fallback to the normal post URL when JavaScript is unavailable.


V1.9.6 — Legal pages and Journal tag block
- Journal inline reader now shows post tags only inside a soft white block; the category remains above the article title and is not duplicated below the content.
- Added managed bilingual Privacy Policy and Terms & Conditions pages with the shared internal header/footer.
- Added advanced AR/EN settings, live previews, rich legal content editing, and automatic first-run page creation.
- The two pages are available as normal WordPress pages and can be assigned to the footer policy menu from Appearance > Menus.

V1.9.7 — Legal page styling and three shared menu locations
- Fixed the Privacy Policy and Terms & Conditions frontend by binding both managed pages to the approved policies.html / policies-en.html design scope used by the shared internal header, footer, typography, and responsive layout.
- Added robust legal-page asset detection and horizontal-overflow guardrails.
- Replaced the six duplicated Arabic/English menu locations with exactly three shared locations: Internal pages primary menu, Footer menu, and Policy links menu.
- Added a non-destructive migration that preserves existing assignments, prefers the former Arabic assignment, falls back to the former English assignment, and removes only the retired location keys.
- The active site language continues to translate linked managed page titles and append the correct VAVA language state at render time.

V1.9.8 — Internal header menu selection from Homepage Hero
- Added one shared "Internal pages header menu" selector to Homepage Settings > Hero.
- The selected WordPress menu now drives the header on About VAVA, VAVA Paths, VAVA Selections, Journal, Contact, Privacy Policy, Terms & Conditions, and all other internal VAVA pages.
- The homepage header remains independent and continues to use its same-page section navigation.
- The selected menu is one functional setting shared by Arabic and English; managed page labels and URLs still switch with the active VAVA language.
- Existing Appearance > Menus assignment remains a fallback, and saving the Homepage setting synchronizes the unified primary_internal location for compatibility.
- Added a polished shared-setting card with a direct Manage WordPress menus shortcut.



V1.9.9 — Compact internal header menu field placement
- Restyled the internal-pages header menu selector to match the standard footer-menu field instead of using a separate feature card.
- Moved the shared selector to the top of the active Hero language form, directly before the Small text and Main title fields.
- Keeps one canonical shared selector while moving it between the Arabic and English Hero panes, avoiding duplicate settings or conflicting saved values.
- Preserves the existing internal-header menu behaviour, WordPress menu-management shortcut, fallback, and synchronization logic.


V1.9.10 — Internal header inside the Hero live preview
- Added the full shared internal-pages header above the Hero inside both Arabic and English live previews.
- The preview now displays the VAVA logo, selected WordPress menu links, and the active language state using the same soft compact visual direction as the public internal-page header.
- Changing the Internal pages header menu selector updates both language previews immediately without saving or reloading the editor.
- Only top-level menu items are shown, long labels are safely truncated, and an empty-state message appears when no menu is selected.
- The homepage frontend header remains unchanged; this release updates the Homepage Hero admin preview only.


V1.9.11 — Full-height featured Journal card
- The featured Journal card now stretches to the full height of the adjacent regular-article grid on desktop.
- Removed the visual gap below the featured card by assigning the remaining vertical space to the featured image rather than adding empty content spacing.
- The featured image keeps a controlled cover crop and never follows the uploaded image's native dimensions.
- Added the same full-height behaviour to the Journal live preview, while tablet and mobile layouts return to a safe independent image height.

V1.18.0 — VAVA digital products catalogue
- Replaced the four retired digital-product cards with the six client-approved products and prices.
- Added optimized cover artwork from the supplied product files, plus a temporary branded cover for the food reference until its final file is supplied.
- Added one bilingual WordPress detail page for every digital product using the shared internal header, footer, navigation, and AR/EN language switch.
- Added structured product sections for the guide question, contents, ideal audience, format, language, page count, personal-use rights, and educational disclaimer.
- Added a one-time migration that removes the saved legacy product rows, creates or reconnects the six product pages, and keeps existing Selections hero and tangible-product settings intact.
- Removed the retired product artwork and obsolete purchase-page product data from the active JavaScript bundle.
