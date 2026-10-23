# Home Practice Areas panorama

The Home showcase follows the full-width, image-led panel structure requested from https://rifdaannovarazkaprabowo.com/ (Unmatched Services, Unmatched Excellence). The implementation uses HSNCO copy and a local image; no reference-site imagery, copy or vendor scripts are copied.

One img element (assets/images/office.jpg) spans the entire panel grid. Bootstrap columns divide it into independent links: four desktop columns and two columns below the lg breakpoint. All eight published practices remain available (two desktop rows), instead of limiting content to the four services in the reference.

Black heading area, white titles, subtle panel dividers, gold numbering, and Learn more on hover/focus. Touch layouts show the action without requiring hover. Reduced motion disables transitions. Each panel is a real keyboard-accessible anchor to /practice-areas#slug. The old horizontal rows, per-practice images, summaries and their Home stylesheet import are replaced; the internal Practice Areas page remains unchanged.

Data comes from published PracticeArea records, with sort_order/name ordering. Names/links remain editable via /admin/practice-areas. The shared panorama image is in the Home partial. Existing Home approach visibility controls the replacement in CMS Home; no table or data migration.

QA: exactly one shared image and eight panels, hover/focus, valid destination, no horizontal overflow or JS errors at 1920/1440/1366/768/390/375. Eleven regression tests / 169 assertions passed. The 102 frontend assets pass the Docker manifest validator. No push or deployment performed.