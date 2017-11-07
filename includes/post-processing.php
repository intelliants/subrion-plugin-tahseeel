<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2017 Intelliants, LLC <https://intelliants.com>
 *
 * This file is part of Subrion.
 *
 * Subrion is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Subrion is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Subrion. If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link https://subrion.org/
 *
 ******************************************************************************/

$iaTahseeel = $iaCore->factoryPlugin('tahseeel', 'common');

if (isset($_GET['cancelled'], $_GET['hash'], $_GET['inv_id'])) {
    $result = $iaTahseeel->getOrderInfo($_GET['hash'], $_GET['inv_id']);
    if ($result && !$_GET['cancelled']) {
        switch ($_GET['tx_status']) {
            case iaTahseeel::STATUS_PAID:
                $transaction = $temp_transaction;
                $transaction['status'] = iaTransaction::PASSED;
                $transaction['reference_id'] = $_GET['inv_id'];
                $transaction['notes'] = $_GET['tx_mode'];

                $member = $iaUsers->getInfo($transaction['member_id']);

                $order['payment_gross'] = $transaction['amount'];
                $order['mc_currency'] = $transaction['currency'];
                $order['payment_date'] = strftime($iaCore->get('date_format'), strtotime($_GET['tx_date']));
                $order['payment_status'] = iaLanguage::get($transaction['status']);
                $order['first_name'] = ($member['fullname'] ? $member['fullname'] : $member['username']);
                $order['last_name'] = '';
                $order['payer_email'] = $member['email'];
                $order['txn_id'] = $transaction['reference_id'];
                break;

            case iaTahseeel::STATUS_FAILED:
                $transaction['status'] = iaTransaction::FAILED;
            default:
                $messages[] = 'Tahseeel payment status: ' . $_GET['tx_status'];
        }
    } elseif ($_GET['cancelled']) {
        $messages[] = iaLanguage::get('tahseeel_payment_cancelled');
    }
} else {
    $messages[] = iaLanguage::get('error');
}
