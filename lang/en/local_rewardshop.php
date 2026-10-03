<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Language strings for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
$string['amount'] = 'Amount';
$string['approvalrequired'] = 'Approval required';
$string['approve'] = 'Approve';
$string['automatic'] = 'Automatic';
$string['backtoshop'] = 'Back to shop';
$string['balance'] = 'Balance';
$string['buy'] = 'Buy';
$string['cannotpurchase'] = 'This reward cannot be purchased: {$a}';
$string['configjson'] = 'Type configuration (JSON)';
$string['copyof'] = 'Copy of {$a}';
$string['cost'] = 'Cost';
$string['credithistory'] = 'Credit history';
$string['creditrules'] = 'Credit conversion rules';
$string['credits'] = 'credits';
$string['creditsperstep'] = 'Credits granted per step';
$string['defaultcredits'] = 'Credits per XP step';
$string['defaultxpstep'] = 'Default XP step';
$string['deliverynotsupported'] = 'Automatic delivery is not supported on this Moodle version.';
$string['duplicate'] = 'Duplicate';
$string['editreward'] = 'Edit reward';
$string['enabled'] = 'Enabled';
$string['enableddesc'] = 'Enable Reward Shop';
$string['ended'] = 'Reward availability has ended.';
$string['errorbadgeid'] = 'Configure a badge id.';
$string['errorcmid'] = 'Configure a course module id.';
$string['errorextension'] = 'Configure a positive extension duration in seconds.';
$string['errorhint'] = 'Configure the hint text.';
$string['event_credits_earned'] = 'Credits earned';
$string['event_credits_spent'] = 'Credits spent';
$string['event_reward_approved'] = 'Reward approved';
$string['event_reward_delivered'] = 'Reward delivered';
$string['event_reward_purchased'] = 'Reward purchased';
$string['event_reward_refunded'] = 'Reward refunded';
$string['event_reward_rejected'] = 'Reward rejected';
$string['extensionmanual'] = 'This activity does not expose a stable supported deadline-extension API; keep this reward manual.';
$string['image'] = 'Image';
$string['insufficientcredits'] = 'Insufficient credits.';
$string['invalidcost'] = 'Cost must be zero or greater.';
$string['invalidjson'] = 'Invalid JSON.';
$string['invalidstatus'] = 'This action is not valid for the current purchase status.';
$string['invalidtarget'] = 'The configured activity/resource is invalid.';
$string['ledgerpurchase'] = 'Purchase: {$a}';
$string['ledgerrejection'] = 'Credits returned after rejection.';
$string['limitreached'] = 'Purchase limit reached.';
$string['locktimeout'] = 'Could not obtain a concurrency lock. Please try again.';
$string['manage'] = 'Manage rewards';
$string['manualdelivery'] = 'This reward requires manual delivery.';
$string['markdelivered'] = 'Mark delivered';
$string['maxperuser'] = 'Limit per user';
$string['mustbepositive'] = 'Enter a value greater than zero.';
$string['myhistory'] = 'My history';
$string['newreward'] = 'New reward';
$string['norewards'] = 'No rewards are currently available.';
$string['notstarted'] = 'Reward is not available yet.';
$string['onceonly'] = 'One purchase only';
$string['outofstock'] = 'Out of stock.';
$string['pluginname'] = 'Reward Shop';
$string['prereqcompletion'] = 'Complete {$a} first.';
$string['prereqminxp'] = 'Requires at least {$a} XP.';
$string['privacy:amount'] = 'Credit change';
$string['privacy:balance'] = 'Current balance';
$string['privacy:configsnapshot'] = 'Reward configuration snapshot';
$string['privacy:courseid'] = 'Course id';
$string['privacy:description'] = 'Ledger description';
$string['privacy:ledger'] = 'Immutable credit ledger';
$string['privacy:lifetimeearned'] = 'Lifetime credits earned';
$string['privacy:lifetimespent'] = 'Lifetime credits spent';
$string['privacy:purchases'] = 'Reward purchases';
$string['privacy:status'] = 'Purchase status';
$string['privacy:userid'] = 'User id';
$string['privacy:wallet'] = 'Credit wallet';
$string['purchases'] = 'Purchases';
$string['purchasesuccess'] = 'Reward purchased successfully.';
$string['quizattemptmanual'] = 'An additional quiz attempt cannot be delivered safely through a stable API on this Moodle version; keep this reward manual.';
$string['refund'] = 'Refund';
$string['reject'] = 'Reject';
$string['remaining'] = 'Remaining';
$string['requiresapproval'] = 'Requires manual approval';
$string['reward'] = 'Reward';
$string['rewarddisabled'] = 'Reward is disabled.';
$string['rewardtype'] = 'Reward type';
$string['status_approved'] = 'Approved';
$string['status_cancelled'] = 'Cancelled';
$string['status_delivered'] = 'Delivered';
$string['status_pending'] = 'Pending';
$string['status_refunded'] = 'Refunded';
$string['status_rejected'] = 'Rejected';
$string['stock'] = 'Stock';
$string['tasksyncxp'] = 'Synchronise Personal XP milestones with Reward Shop credits';
$string['teacherrefund'] = 'Refund issued by teacher.';
$string['timeend'] = 'Available until';
$string['timestart'] = 'Available from';
$string['toggle'] = 'Enable/disable';
$string['type_badge'] = 'Badge';
$string['type_badge_desc'] = 'Awards an existing Moodle badge when the supported badge API is available.';
$string['type_contentunlock'] = 'Content unlock';
$string['type_contentunlock_desc'] = 'Creates a Reward Shop entitlement for a course module without changing its original availability rules.';
$string['type_custom'] = 'Custom/manual reward';
$string['type_custom_desc'] = 'Creates a request for manual fulfilment by a teacher.';
$string['type_extension'] = 'Deadline extension';
$string['type_extension_desc'] = 'Extends a supported activity deadline only through a stable activity API; otherwise remains manual.';
$string['type_hint'] = 'Hint';
$string['type_hint_desc'] = 'Reveals teacher-authored hint text after purchase.';
$string['type_quizattempt'] = 'Additional quiz attempt';
$string['type_quizattempt_desc'] = 'Uses a supported Moodle quiz override API only when one is available; otherwise remains manual.';
$string['xpcreditaward'] = '{$a->credits} credits for reaching {$a->xp} XP.';
$string['xpstep'] = 'XP required per step';
