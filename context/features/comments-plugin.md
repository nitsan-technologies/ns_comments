# Feature — Comments plugin

*Status: Done (free version). Code-read (2026-10-15); content element, edit form and Page module preview seen on
v14.3.7, frontend not tested.*

## Editor flow

1. Add the content element "Comment" (wizard group plugins, CType `nscomments_comment`) to a page, or render
   `lib.tx_nscomments.comments` from a page template (for example news detail pages).
2. Tab "Plugin": date and time format (presets or custom when "custom" is checked) and the captcha checkbox
   (`settings.captcha`; the form shows the captcha when it is `0`).
3. Include the static template or the site set `nitsan/ns-comments`.
4. Manage comments as `tx_nscomments_domain_model_comment` records in the List module.

## Rendering

- `listAction()`: page id from `$GLOBALS['TSFE']` (v12) or the `frontend.page.information` request attribute
  (v13+); top-level comments of that page (`pageuid`, `comment = 0`), newest first, replies recursively.
  Error flash message when there is no page id.
- Storage page (`initializeAction`): the posted page `id` when `comments-storage-pid` is set, else TypoScript
  `persistence.storagePid`, else the posted `Storagepid` / page `id`. Listing ignores the storage page and
  filters by `pageuid`.
- Form (`Partials/Comment/FormFields.html`): name, e-mail, text, optional terms checkbox
  (`termsRequired`, `termsText`, `termsTypolinkParameter`), optional image captcha
  (`Resources/Public/PHP/captcha.php`, checked by `verify.php` from `custom.js`).
- Submit: `custom.js` posts the form via AJAX to page type 991 (`commentAjaxCall`); `createAction()` sets
  `crdate`, language and parent, stores the comment, builds a `paramlink` with anchor `#comments-<uid>` and
  returns JSON; the script reloads the list.
- `relatedComments = 1` (set by `lib.tx_nscomments.comments`) takes date/time format and captcha from
  `settings.mainConfiguration.*` instead of the FlexForm.

## Settings

| Classic constant | Site set setting |
|---|---|
| `plugin.tx_nscomments.view.{templateRootPath,partialRootPath,layoutRootPath}` | `ns_comments.view.*` |
| `plugin.tx_nscomments.settings.{StyleCss,FontAwesomeCss,CustomJS,Minjs}` | `ns_comments.configuration.assets.*` |
| `plugin.tx_nscomments.settings.{termsRequired,termsText,termsTypolinkParameter}` | `ns_comments.configuration.terms.*` |
| `plugin.tx_nscomments.settings.approveComment` | `ns_comments.configuration.main.approveComment` |
| `plugin.tx_nscomments.settings.mainConfiguration.{customDateFormat,customTimeFormat,disableCaptcha}` | `ns_comments.configuration.main.*` |

The asset constants are defined but the setup includes the files with fixed paths. `approveComment` only
changes the frontend message (see `core.md`).

## Page module preview

Core's standard preview (header) + premium strip; the extension's `Nscomment.html` is not used (see `core.md`).
