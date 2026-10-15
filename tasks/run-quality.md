# Run quality checks

The repo has no PHPUnit, PHPStan or CS config. Available checks:

```bash
# PHP syntax; also run once with PHP 8.1, the lowest version TYPO3 12 supports
for f in $(find . -name "*.php" -not -path "./.git/*" -not -path "./.Build/*"); do php -l "$f"; done

# composer.json
composer validate --no-check-publish

# XLF well-formedness
for f in Resources/Private/Language/*.xlf; do xmllint --noout "$f"; done
```

## Manual test (per TYPO3 major 12 / 13 / 14)

1. Page module, page with a "Comment" content element: strip below the element header (on every page, with or
   without the site set).
2. Edit the element, tab "Plugin" (v14) / "Configuration": strip above the plugin options. Other plugins'
   FlexForms show no ns_comments strip (other NITSAN strips may appear for their own plugins).
3. "Start Free Trial" (as system maintainer) opens the ns_license module and its trial modal with
   "Page Comment (ns_comments)" preselected. Cancel; don't start a trial.
4. Deactivate ns_license (test system only): both placements show nothing and the form still saves.
5. Frontend: post a comment and a reply (with and without captcha / terms); both appear without reload. Check a
   page that also has an ns_news_comments plugin.
6. Check light and dark backend themes.

Remove test content elements and comments afterwards.
