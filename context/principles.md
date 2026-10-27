# ns_comments — Operating Principles

*Every change to code, context or docs must serve the goals below.*

---

## Goal 1 — Nothing that works may break

The extension declares TYPO3 v12–v14 (Composer and Classic mode), see `core.md`.

- Keep every declared major working. Follow the existing version switches (`version_main <= 12` / `>= 14` in
  `CommentController`, `enum_exists(IconSize::class)` in `PremiumStrip`, the constructor/`setData()` pair in
  the FormEngine resolver, `getRecordType()` vs. the row's `CType` in the preview listener) instead of inventing
  new ones.
- Lowest PHP is 8.1 (TYPO3 12): no PHP 8.2+ syntax such as `readonly` classes or DNF types. Highest declared PHP is 8.5.
- Both configuration paths must keep working: the classic static template (`plugin.tx_nscomments.*` constants)
  and the site set `nitsan/ns-comments` (`ns_comments.*` settings). A change to one usually needs the same
  change in the other.
- Existing content elements must keep rendering: the plugin is a CType (`nscomments_comment`); older
  installations are migrated from `list_type` by the `txNsCommentsPluginMigration` upgrade wizard.
- ns_news_comments can share a page with this plugin; keep JS and CSS scoped to `.tx_nscomments` (see the
  2026 bugfix commits).
- Prefer the smallest change that fixes the root cause. No drive-by refactors.
- Run `tasks/run-quality.md` and re-test the affected flow before claiming done.

## Goal 2 — Token-cost efficiency (agent context)

- **DRY context:** each fact lives in exactly ONE file. Reference, don't copy.
- **Load on demand:** read `context/features/<area>.md` for the task, not everything.
- **Prune as you go:** keep `context/session-state.md` short.

## Goal 3 — Accuracy and trust

- Answer from context + code, never from invented API, table or field names.
- Mark statements as verified live or code-read; conflicting facts across files = fix the source file.

---

## Guardrails (non-negotiable)

- FormEngine resolver priorities for `flexFormNoTabsContainer` must be unique across all installed NITSAN
  extensions (core throws on duplicates); ns_comments uses 47 (see `features/premium-cta.md`).
- Installing the premium version through ns_license replaces this extension's folder: never keep user data in
  files below the extension.
- Do not change ns_license from an ns_comments task; reuse its module and trial modal.
- All UI text goes into the English `Resources/Private/Language/*.xlf` files, no hard-coded labels. The `de.*`
  and `fr.*` files come from Crowdin; don't edit them here.

---

## Apply this whenever you

- Edit `context/` or `AGENTS.md`
- Land a feature or bugfix (update the matching `context/features/*.md` + `session-state.md`)
