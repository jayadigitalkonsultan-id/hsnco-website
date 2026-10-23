# Home Practice Areas showcase

Our Approach is replaced by one black Practice Areas section in both fallback Home and the CMS Home view. The old light/minimal practice list is removed so published services are not duplicated.

Rows reuse practice-areas.css and the existing directory visual language: gold numbering, service name, short summary, arrow, and CMS image on hover or keyboard focus. Touch devices show a subdued background image without requiring hover. Links open the corresponding /practice-areas#slug entry. Missing/private images fall back to office.jpg.

Content comes from published PracticeArea records, ordered by sort_order/name, with publicImage eager-loaded. Edit service content and images in /admin/practice-areas. No table or data migration. The existing Home approach section visibility controls the replacement when a CMS Home exists; obsolete heading/step content and the old recognitions placement are not rendered.

Browser checks: 8 published services, exactly one black showcase, hover and focus images, valid anchor, no JS errors or overflow at 1920/1440/1366/768/390/375. No deployment or push performed.