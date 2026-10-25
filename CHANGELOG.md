# Changelog

This changelog records **only the two latest published GitHub releases** of `local_rewardshop`. Each entry documents verified differences between its release tag and the immediately preceding **published** release tag. Commits made after `1.0.7` are excluded.

## [1.0.7] - 2026-10-23

Published: 2026-10-23 22:52:00 UTC.

Comparison: [1.0.6...1.0.7](https://github.com/EduardoKrausME/moodle-local_rewardshop/compare/1.0.6...1.0.7)

### Added: Moodle course backup and restore integration

- Added `backup/moodle2/backup_local_rewardshop_plugin.class.php` to include course-scoped reward definitions and XP conversion rules in native Moodle course backups.
- When the backup includes user data, the new integration also exports Reward Shop wallets, transaction ledger entries, purchases and content-unlock entitlements, with annotated user references.
- Added `backup/moodle2/restore_local_rewardshop_plugin.class.php` to restore course configuration and eligible participant data, including remapping course, reward, participant, approver and activity references where applicable.
- The restore logic creates distinct ledger idempotency hashes based on the restore operation and source row, and avoids replacing an already present user wallet for the target course.

### Fixed: Personal XP credit conversion

- Updated `classes/personalxp_bridge.php` to compute new credit milestones from **unconverted XP** (`max(0, total XP - previously converted XP)`) rather than differences between absolute milestone numbers.
- The revised checkpoint adds only the newly converted XP, addressing incorrect credit thresholds when the configured XP-to-credit step changes.

### Tests and privacy metadata

- Extended `tests/purchase_test.php` with regression cases for staff confirmation of manual deliveries, rejection of attempts to deliver purchases from another course, blocking premature delivery of purchases requiring approval, and revocation of content unlocks on refund.
- Added the wallet's `xpconverted` field to the Moodle Privacy API metadata in `classes/privacy/provider.php`.
- Applied code-style, language-key ordering and maintenance adjustments to purchase handling, reward badge code, language packs and the database upgrade file. Most `classes/purchase_service.php` changes in this release are indentation/style updates rather than new purchase-flow behavior.

### Version metadata and scope

- Changed `$plugin->release` in `version.php` from `1.0.6` to `1.0.7`, while **retaining the numeric `$plugin->version = 2026102300`** and the minimum Moodle build `2022112800`.
- The comparison spans seven commits. The backup and restore classes are new; no change to the plugin's published numeric upgrade version can be inferred beyond the unchanged `2026102300` value.

## [1.0.6] - 2026-10-23

Published: 2026-10-23 22:24:42 UTC.

Comparison: [1.0.5...1.0.6](https://github.com/EduardoKrausME/moodle-local_rewardshop/compare/1.0.5...1.0.6)

### Fixed: purchase authorization and wallet consistency

- Introduced `wallet_service::with_wallet_lock()` to hold a user/course wallet lock across operations and their database commits, with nested operations reusing an already-held lock in the same request.
- Updated purchasing, approval/rejection, delivery and refund flows to coordinate wallet changes and prevent concurrent transactions from bypassing consistency checks.
- Separated trusted automatic fulfillment from staff-triggered delivery. Manual delivery now checks the current actor, `local/rewardshop:approve` in the purchase course context, approved purchase state and matching reward/course ownership.
- Updated badge delivery to use the Moodle `core_badges\badge` API and validate that the badge belongs to the reward's course and is active; already-issued badges are not issued again.
- Marked extra quiz attempts as **manual fulfillment** rather than calling an unverified generic cross-version quiz override API.
- Added a content-unlock cancellation handler and tightened course scoping in purchase management. Purchase history is paginated (30 entries per page).

### Added: audited manual credit adjustments

- Added `adjust.php` and `templates/adjust.mustache` for course-scoped credit adjustments, with a navigation entry for authorized managers.
- Required `local/rewardshop:adjustcredits`, a valid Moodle sesskey, a currently enrolled active user, a nonzero adjustment amount, a nonempty reason and a unique submission token.
- Recorded adjustments through the existing wallet ledger, with the acting user identified in the description and an idempotency key to avoid duplicate credit operations.

### Changed: Personal XP conversion and database schema

- Introduced wallet field `xpconverted`, defaulting to `-1`, in `db/install.xml`; added an upgrade step in the new `db/upgrade.php` for previously installed databases.
- Updated `classes/personalxp_bridge.php` to grant credits using a persisted XP conversion checkpoint and the wallet lock, with a one-time calculation from legacy credit ledger references when necessary.
- The checkpoint is persisted with the credit award to avoid replaying previously credited milestones. The subsequent `1.0.7` release changes how new milestones are calculated after changes to the conversion rule.

### Privacy, interface and maintenance

- Expanded the Privacy API provider to account for wallets, ledger entries, purchases, approving staff and content unlocks; added support for course-scoped user lists and corresponding export and erasure operations.
- Added the shop's compiled `styles.css` and updated its SCSS source, alongside English and Brazilian Portuguese language strings and adjustments to the purchase/management screens.
- Added release workflow concurrency settings in `.github/workflows/release.yml`, using a release-scoped group, `cancel-in-progress: false` and `queue: max`.
- Updated `version.php` from `2026101501` / release `1.0.5` to `2026102300` / release `1.0.6`. The minimum Moodle build remained `2022112800`.

### Scope

The comparison includes eleven commits and changes purchase, wallet, XP conversion, reward fulfillment, privacy, styles, templates, translations, database schema/upgrade, release workflow and version metadata. The claims above concern code present in the published release tag, not independently verified runtime behavior.

---

All entries are grounded in the two published-tag comparisons; they do not include later `master` commits or imply that tests or deployments were executed when preparing this changelog.
