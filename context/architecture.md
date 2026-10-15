# ns_comments — Architecture

One Extbase content-element plugin (`nscomments_comment`, actions `list`, `new`, `create`, all non-cacheable),
one table, no backend module. Backend additions: FormEngine resolver and Page module preview listener for the
premium strip.

## Configuration

| File | What |
|---|---|
| `ext_localconf.php` | Plugin `ns_comments`/`Comment` as `PLUGIN_TYPE_CONTENT_ELEMENT`; FormEngine `nodeResolver` 1792056230 (`flexFormNoTabsContainer`, priority 47) |
| `Configuration/TCA/Overrides/tt_content.php` | `registerPlugin()` (CType `nscomments_comment`, group `plugins`, FlexForm), extra "Configuration" tab, `addPiFlexFormValue()` (see `core.md`) |
| `Configuration/TCA/tx_nscomments_domain_model_comment.php` | Comment record (`username`, `usermail`, `description`, `pageuid`, `comment` = parent, `childcomment`, `paramlink`, …), label `username`, newest first |
| `Configuration/FlexForm/FlexForm.xml` | One sheet: `settings.custom` (reload) → `customdate` / `customtime`, else `dateFormat` / `timeFormat` radios; `settings.captcha` (default 1) |
| `Configuration/page.tsconfig` | Wizard item in group `plugins` + preview key that core never uses (see `core.md`). Loaded globally by core since v12; the site set imports it again |
| `Configuration/TCA/Overrides/sys_template.php` | Static template (`Configuration/TypoScript`) |
| `Configuration/TypoScript/` | Constants `plugin.tx_nscomments.{view,settings}.*`; setup: plugin settings, global `page.includeCSS` / `includeJSFooter`, `commentAjaxCall` page type 991, `lib.tx_nscomments.comments` |
| `Configuration/Sets/NsComments/` | Site set `nitsan/ns-comments`; 14 settings below `ns_comments.{view,configuration.assets,configuration.terms,configuration.main}`; setup imports the classic setup and overrides it; imports `page.tsconfig` |
| `Configuration/Services.yaml` | Autowires `Classes/*`; tags `EventListener\PremiumStripPreview` (`ns-comments/premium-strip-preview`, after `typo3-backend/fluid-preview/content`) |
| `Configuration/Icons.php` | `ext-ns-comment-icon` |

## Classes

| Class | Role |
|---|---|
| `Controller\CommentController` | `initializeAction` sets the storage page; `listAction` loads top-level comments of the current page + captcha URLs; `createAction` stores a comment (or reply) and returns JSON |
| `Domain\Model\Comment`, `Domain\Repository\CommentRepository` | Comment with child comments; `getCommentsByPage()`, `getLastCommentOfPage()` (both ignore the storage page) |
| `ViewHelpers\LastCommentViewHelper` | Latest comment of a page |
| `Updates\PluginMigration` | Upgrade wizard `txNsCommentsPluginMigration`: `list` + `list_type=nscomments_comment` → CType |
| `Backend\PremiumStrip` | Strip HTML + trial URL; `''` when the ns_license route is missing |
| `Form\Resolver\PremiumStripFlexFormNoTabsContainerResolver` | Picks the strip container for `pi_flexform` of Comments elements |
| `Form\Container\PremiumStripFlexFormNoTabsContainer` | Core single-sheet FlexForm container + strip |
| `EventListener\PremiumStripPreview` | Strip in the Page module preview (`PageContentPreviewRenderingEvent`) |

## Views

- Frontend: `Templates/Comment/List.html` → `Layouts/Default.html` → `Partials/Comment/{Comment,FormFields}.html`
  (recursive replies).
- Backend: `Resources/Private/Backend/Nscomment.html` (not used, see `core.md`).

## Assets

- Frontend: `css/{style.css,font-awesome.min.css}`, `fonts/`, `js/{jquery-3.6.0.min.js,custom.js}`,
  `PHP/{captcha.php,verify.php,phptextClass.php}`, `Icons/`.
- Backend: `css/backend-premium-strip.css`.
