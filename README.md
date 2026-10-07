# Reward Shop (`local_rewardshop`)

Reward Shop adds a non-competitive virtual reward store to Moodle and keeps credits separate from Personal XP.

XP is historical progress and is never consumed. Credits are the spendable unit. A learner may therefore have 4,850 XP
and 320 credits; buying a 50-credit reward leaves XP at 4,850 and reduces the credit balance to 270.

## How credits work

Each learner has a wallet per course with the current balance, total credits earned and total credits spent. Every
movement is also written to an immutable ledger. Ledger rows are never edited: corrections are new `refund`
or `adjustment` rows.

The public API provides `add_credits()`, `spend_credits()`, `refund()`, `get_balance()` and `can_purchase_reward()`.
Wallet changes use Moodle locks plus database transactions so concurrent requests cannot spend the same balance twice.

Purchases use a server-side reward price. The browser never supplies a trusted price. Each purchase request also carries
an idempotency token, and the database enforces one purchase per user/token to protect against double clicks, reloads
and repeated requests.

## Personal XP integration

Reward Shop can convert XP milestones into credits, for example 50 credits for every 500 XP. Rules can be set per
course, for example 50 credits every 500 XP. The conversion is idempotent: each user/course/milestone has a unique
ledger key, so running the synchronisation repeatedly never grants the same milestone twice.

The current public `local_personalxp` implementation does not emit a dedicated XP-earned event. Reward Shop therefore
reads the Personal XP service and periodically reconciles enrolled learners in courses that have
rewards. `personalxp_bridge::on_xp_awarded()` is also available as an immediate integration point for a future Personal
XP event or API callback.

## Reward types

- Badge: awards an existing Moodle badge when the supported badge API is available.
- Content unlock: stores a Reward Shop entitlement for a course module without rewriting the module's original
  availability configuration. Integrations can query `api::has_content_unlock()`; the plugin intentionally does not
  bypass Moodle availability checks.
- Hint: reveals teacher-authored hint text in the learner purchase history.
- Additional quiz attempt: automatic delivery is enabled only when the Moodle version exposes a supported override API.
  It never writes directly to quiz tables.
- Deadline extension: remains manual unless a stable supported API exists for the target activity; it never writes
  directly to activity-plugin tables.
- Custom: creates a manual request for the teacher.

Reward types implement `local_rewardshop\\reward_type_interface`, allowing additional types without changing the
purchase engine.

## Learner flow

The course store is available at `/local/rewardshop/index.php`. It shows the learner's own balance and available
rewards, including image, description, cost and whether delivery is automatic, needs approval or is limited to one
purchase.

`/local/rewardshop/my.php` shows the learner's own credit movements, purchases, refunds and request status. There is no
ranking, leaderboard, podium or learner-to-learner comparison.

## Teacher flow

Teachers with the management capability can create, edit, duplicate, enable or disable rewards and control their order
with manual up/down controls, cost, stock, per-user limit, availability period, approval requirement and type-specific
JSON configuration.

The purchase panel allows approval, rejection, manual delivery and refunds. Rejection and refund create new ledger
entries rather than rewriting credit history.

## Pedagogical safeguards

Reward Shop does not sell grades and contains no reward type for directly changing grades. Extra attempts, deadline
extensions and content access are explicit teacher-configured reward types and are delivered automatically only when a
supported Moodle API makes the operation safe. Otherwise the request remains manual.

XP and grades remain independent from credits. The store is designed around personal progress rather than competition.

## Prerequisites

Type configuration may include `minxp` and `requiredcmids`. These are evaluated server-side before
purchase. `requiredcmids` uses Moodle completion state for the current learner.
