# ns_comments — Core facts

## Identity

| Item | Value |
|---|---|
| Extension key | `ns_comments` |
| Composer name | `nitsan/ns-comments` |
| Title (ext_emconf) / product name | TYPO3 Page Comments / Page Comment |
| Namespace | `Nitsan\NsComments\` → `Classes/` (note the casing `Nitsan`) |
| Version (ext_emconf) | 14.1.0 (latest tag 14.0.3) |
| Repository | https://github.com/nitsan-technologies/ns_comments (default branch `master`) |
| Licensing status | **Freemium** — this repo is the free version; a paid premium version exists in the T3Planet catalog (the NITSAN Free/Freemium sheet needs a Google login) |
| Premium version (T3Planet catalog) | "Page Comment", €129, https://t3planet.de/en/typo3-page-comment-extension |

## Compatibility

| Source | TYPO3 | PHP |
|---|---|---|
| `ext_emconf.php` | `12.0.0-14.9.99` | not declared |
| `composer.json` | `^12 \|\| ^13 \|\| ^14` | not declared |
| ns_license 14.5.4 (dependency) | `12.4.18-14.9.99` | not declared (TYPO3 12.4 needs 8.1+) |

- Effective minimum is **TYPO3 12.4.18** (from ns_license) and **PHP 8.1**.

## Dependencies

| Package | Constraint | Why |
|---|---|---|
| `typo3/cms-core` | see above | core |
| `nitsan/ns-license` / `ns_license` | `^14.5.4` / `14.5.4-14.9.99` | TYPO3 Shop module + free-trial deep link (see `features/premium-cta.md`) |

Bundled frontend libraries (no package manager): jQuery 3.6.0, Font Awesome. The captcha image is generated
by the bundled PHP scripts in `Resources/Public/PHP/` (GD, font `fonts/monofont.ttf`).

## Tooling

- No PHPUnit, PHPStan, CS fixer or package.json in the repo. `composer.json` has a `.Build/` vendor/web-dir
  config for standalone installs.
- CI: `.github/workflows/ter-release.yml` only (publishes tags to TER with tailor).
- `.gitattributes` export-ignores `.crowdin.yml`, `.github/`, `AGENTS.md`, `CLAUDE.md`, `context/`, `tasks/` and
  itself, so they are not in the GitHub/Composer archive or the TER package (the TER release uploads the GitHub
  tag archive).
- Translations: Crowdin (`.crowdin.yml`), sources `Resources/Private/Language/*.xlf` (`locallang.xlf`
  frontend + premium strip, `locallang_db.xlf` TCA/FlexForm/constants, `locallang_csh_*.xlf`); `de.*` and
  `fr.*` come from Crowdin.

## Conventions

- PHP: 4 spaces; `CommentController` has no `declare(strict_types=1)`, `PluginMigration` and the premium-strip
  classes do.
- TypoScript: 4 spaces; Fluid and XLF: tabs; FlexForm mixes tabs and spaces.
- Frontend assets in lowercase folders `Resources/Public/{css,js,fonts}/`; backend CSS
  `Resources/Public/css/backend-premium-strip.css`.

## Known issues (existing, not fixed)

All code-read unless marked.

- **Fluid preview never used (seen live on 14.3.7):** `Configuration/page.tsconfig` registers
  `mod.web_layout.tt_content.preview.list.nscomments_comment`, the `list_type` key, but the plugin is a CType,
  so core looks up `preview.nscomments_comment` and shows its standard preview. `Nscomment.html` also uses
  `locallang_db.xl` (missing `f`) in its labels and v12/v13 `pi_flexform_transformed`, which v14 does not pass.
- **`approveComment` does not hold comments back:** the setting only switches the frontend message and the AJAX
  `dataType`; `createAction()` never sets `hidden`, so comments are visible at once.
- `tt_content.php` passes the FlexForm to `registerPlugin()` and again via `addPiFlexFormValue()` (deprecated
  in TYPO3 14); `addToAllTCAtypes` adds a "Configuration" tab whose `pi_flexform` already sits in the "Plugin"
  tab on v14 (seen live).
- `commentAjaxCall` (`typeNum = 991`) and `lib.tx_nscomments.comments` still set `switchableControllerActions`,
  which TYPO3 12+ ignores.
- `ext_tables.sql` adds `pages.nscomments` and comment columns (`feuserid`, `accesstoken`, `terms`, …) without
  TCA (premium leftovers).
- Frontend CSS/JS (`style.css`, Font Awesome, jQuery 3.6.0, `custom.js`) are added to every page via
  `page.includeCSS` / `includeJSFooter`, not only pages with the plugin.
- `page.tsconfig` sets a legacy `icon = ../typo3conf/...` path next to `iconIdentifier`.
- README badges show v13.0.1 / TYPO3 13 and GPL-3.0; `composer.json` uses the deprecated SPDX id `GPL-2.0+`;
  composer `description` mentions admin e-mail notifications, which the free version does not have.
