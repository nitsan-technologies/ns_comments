# Feature — Premium strip and ns_license free trial

*Status: Done. Verified live on TYPO3 14.3.7 (2026-10-15): strip in the Page module preview and above the
plugin options (tab "Plugin"); the button opens the ns_license module with "Page Comment (ns_comments)"
preselected in the trial modal. Requires ns_license 14.5.4 (deep-link support) to be released. v12/v13 not
tested.*

## Licensing status

Freemium. Premium product (T3Planet catalog): "Page Comment", €129,
https://t3planet.de/en/typo3-page-comment-extension (not linked from the UI; the trial stays inside the TYPO3
backend). Catalog features: inline content ratings, custom comment forms, threaded replies, up/down voting,
comment moderation, AJAX load more, author notifications.

## Dependency

`composer.json` `"nitsan/ns-license": "^14.5.4"`, `ext_emconf.php` `'ns_license' => '14.5.4-14.9.99'`. Raises
the effective minimum to TYPO3 12.4.18 (see `core.md`).

## Placements

| Place | Position |
|---|---|
| Page module, preview of a Comments content element | Below the element header, as the preview body |
| Content element edit form, tab "Plugin" (v14) | Above the plugin options (`pi_flexform`) |

## How it works

- `Backend\PremiumStrip::render()`: `actions-rocket` icon, headline and description on two lines,
  `btn btn-primary` "Start Free Trial →" (the arrow is `aria-hidden` markup). Link = route
  `nitsan_nslicensemodule` + `trial=ns_comments` + `#free-section`, same frame. Returns `''` without
  ns_license, so neither placement shows anything.
- Preview: `EventListener\PremiumStripPreview` on `PageContentPreviewRenderingEvent` (tagged in
  `Services.yaml`, ordered after core's `typo3-backend/fluid-preview/content`) prepends the strip to any
  existing preview content and adds the stylesheet through `PageRenderer`. The record type comes from
  `getRecordType()` (v13+) or the row's `CType` (v12). Because the extension's own Fluid preview is never used
  (see `core.md`), the strip replaces core's standard preview body, which is empty for this CType apart from
  "not allowed in this column" warnings.
- Edit form: `ext_localconf.php` registers `Form\Resolver\PremiumStripFlexFormNoTabsContainerResolver` for node
  `flexFormNoTabsContainer`, priority **47** (taken: ns_news_comments 40, ns_event 41, ns_gallery 42,
  ns_helpdesk 43, ns_social_login 44, ns_faq 45, ns_open_streetmap 46, ns_timeline 48; duplicates make core throw). It matches
  `tt_content.pi_flexform` of CType `nscomments_comment` (or legacy `list` + `list_type=nscomments_comment`),
  not inline children, and returns `Form\Container\PremiumStripFlexFormNoTabsContainer`, which prepends the
  strip and adds the stylesheet. If the FlexForm ever gets a second sheet, core switches to
  `flexFormTabsContainer` and the strip disappears from the form.

## Strip

- Labels in `locallang.xlf`: `premium.strip.headline` "You're using the free version of NS Comments",
  `premium.strip.description` "Unlock premium features and get more out of NS Comments.",
  `premium.strip.trial` "Start Free Trial".
- `Resources/Public/css/backend-premium-strip.css`: `.ns-comments-premium-strip` — team-wide design (light-blue
  card `#e7f2fc`, border `#8eb6e8`, 4px `#2b6cb0` left accent, 6px radius, padding 14px 18px, `light-dark()`
  for dark mode, button `#007bff` / hover `#0062cc`); inset 16px inside the FlexForm panel.
- Measured on v14.3.7: 849×67px in the edit form (883px panel); 482×119px in the Page module preview, where the
  button wraps below the text.
- ns_license's module requires system maintainer access; other users get access denied after clicking.
