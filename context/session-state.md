# ns_comments — Session state

## 2026-10-26 — PHP 8.5 compatibility

- Declared PHP `8.1.0-8.5.99` / `>=8.1 <8.6` in `ext_emconf.php` and `composer.json`.
- Removed `imagedestroy()` from captcha `phptextClass.php` (deprecated in 8.5, no-op since 8.0).
- Lint clean on PHP 8.1 and 8.5; captcha generation emits no 8.5 deprecation. Not tested live in the browser.

## 2026-10-15 — ns_license integration + premium strip + agent context

- Branch `feature/ns-license-premium-strip` from `master` (8a248f5).
- Added `nitsan/ns-license ^14.5.4` / `ns_license 14.5.4-14.9.99` (effective minimum now TYPO3 12.4.18).
  Version 14.0.3 → 14.1.0.
- Premium strip with "Start Free Trial" → ns_license module `?trial=ns_comments#free-section` in the Page module
  preview (event listener, because the extension's Fluid preview is never used) and above the plugin options
  (tab "Plugin" on v14), team-wide wording and design (see `features/premium-cta.md`).
- Verified live on TYPO3 14.3.7 classic with a temporary Comments element on page 1 and a temporary
  system-maintainer admin (both removed; `settings.php` restored byte-identical). PHP 8.4 and 8.1 lint clean.
- Created agent context (`AGENTS.md`, `CLAUDE.md`, `context/`, `tasks/`) and `.gitattributes`; no previous
  context existed. Added "ns_comments 47" to the resolver priority lists of the other NITSAN extensions.
- Open: ns_license 14.5.4 not yet published; v12/v13 not tested; frontend only code-read.
