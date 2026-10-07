<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * classes/wallet_service.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop;

use context_course;
use core\lock\lock_config;
use invalid_parameter_exception;
use moodle_exception;

/**
 * Class wallet_service.
 */
class wallet_service {
    private const TYPES = ['earn', 'spend', 'refund', 'adjustment'];

    /**
     * Method get_balance.
     *
     * @param int $userid Parameter userid.
     * @param int $courseid Parameter courseid.
     * @return int Return value.
     */
    public static function get_balance(int $userid, int $courseid): int {
        global $DB;
        return (int)$DB->get_field('local_rewardshop_wallet', 'balance', ['userid' => $userid, 'courseid' => $courseid]);
    }

    /**
     * Method change.
     *
     * @param int $userid Parameter userid.
     * @param int $courseid Parameter courseid.
     * @param int $amount Parameter amount.
     * @param string $type Parameter type.
     * @param string $reference Parameter reference.
     * @param string $description Parameter description.
     * @param ?int $relatedid Parameter relatedid.
     * @param ?string $idempotencykey Parameter idempotencykey.
     * @return int Return value.
     */
    public static function change(int $userid, int $courseid, int $amount, string $type, string $reference, string $description = '', ?int $relatedid = null, ?string $idempotencykey = null): int {
        global $DB;
        if ($userid <= 0 || $courseid <= 0 || $amount === 0 || !in_array($type, self::TYPES, true)) {
            throw new invalid_parameter_exception('Invalid wallet change');
        }
        $ctx = context_course::instance($courseid);
        $key = $idempotencykey ?: implode('|', [$userid, $courseid, $type, $reference, (string)$relatedid, $amount]);
        $hash = hash('sha256', $key);
        $existing = $DB->get_record('local_rewardshop_ledger', ['uniquehash' => $hash]);
        if ($existing) {
            return (int)$existing->balanceafter;
        }
        $factory = lock_config::get_lock_factory('local_rewardshop');
        $lock = $factory->get_lock('wallet:' . $userid . ':' . $courseid, 10);
        if (!$lock) {
            throw new moodle_exception('locktimeout', 'local_rewardshop');
        }
        try {
            $existing = $DB->get_record('local_rewardshop_ledger', ['uniquehash' => $hash]);
            if ($existing) {
                return (int)$existing->balanceafter;
            }
            $tx = $DB->start_delegated_transaction();
            $now = time();
            $wallet = $DB->get_record('local_rewardshop_wallet', ['userid' => $userid, 'courseid' => $courseid]);
            if (!$wallet) {
                $wallet = (object)['userid' => $userid, 'courseid' => $courseid, 'balance' => 0, 'lifetimeearned' => 0, 'lifetimespent' => 0, 'timemodified' => $now];
                $wallet->id = $DB->insert_record('local_rewardshop_wallet', $wallet);
            }
            $new = (int)$wallet->balance + $amount;
            if ($new < 0) {
                throw new moodle_exception('insufficientcredits', 'local_rewardshop');
            }
            $wallet->balance = $new;
            if ($amount > 0 && in_array($type, ['earn', 'refund'], true)) {
                $wallet->lifetimeearned += $amount;
            }
            if ($amount < 0) {
                $wallet->lifetimespent += abs($amount);
            }
            $wallet->timemodified = $now;
            $DB->update_record('local_rewardshop_wallet', $wallet);
            $ledger = (object)[
                'userid' => $userid,
                'courseid' => $courseid,
                'amount' => $amount,
                'type' => $type,
                'reference' => substr($reference, 0, 100),
                'relatedid' => $relatedid,
                'balanceafter' => $new,
                'description' => $description,
                'uniquehash' => $hash,
                'timecreated' => $now,
            ];
            $ledgerid = $DB->insert_record('local_rewardshop_ledger', $ledger);
            $eventclass = $amount < 0 ? '\\local_rewardshop\\event\\credits_spent' : '\\local_rewardshop\\event\\credits_earned';
            $eventclass::create([
                'context' => $ctx,
                'objectid' => $ledgerid,
                'relateduserid' => $userid,
                'other' => ['amount' => $amount, 'balance' => $new, 'type' => $type],
            ])->trigger();
            $tx->allow_commit();
            return $new;
        } finally {
            $lock->release();
        }
    }
}
