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

class iaTahseeel extends abstractCore
{
    const STATUS_PAID = 'PAID';
    const STATUS_FAILED = 'FAILED';
    const STATUS_REDIRECTED = 'REDIRECTED';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_UNPAID = 'UNPAID';

    public $baseUrl;
    public $username;
    public $password;
    public $secret;
    public $cc;
    public $knet;


    public function init()
    {
        parent::init();
        $this->baseUrl = 'https://devlounge.tahseeel.com/api/';

        $this->username = $this->iaCore->get('tahseeel_username');
        $this->password = $this->iaCore->get('tahseeel_password');
        $this->secret = $this->iaCore->get('tahseeel_secret');
        $this->cc = $this->iaCore->get('tahseeel_cc');
        $this->knet = $this->iaCore->get('tahseeel_knet');
    }

    /**
     * @param $member array member info
     * @param $transaction array transaction
     *
     * @return array [error, message]
     */
    public function createOrder($member, $transaction)
    {
        $params = [
            'uid' => $this->username,
            'pwd' => $this->password,
            'secret' => $this->secret,
            'order_no' => $transaction['id'],
            'order_amt' => $transaction['amount'],
            'delivery_charges' => 0,
            'total_items' => 1,
            'cust_name' => $member['fullname'],
            'cust_email' => $member['email'],
            'cust_mobile' => $member['phone'],
            'callback_url' => IA_RETURN_URL,
            'knet_allowed' => $this->iaCore->get('tahseeel_knet'),
            'cc_allowed' => $this->iaCore->get('tahseeel_cc'),
        ];

        $response = json_decode($this->_sendRequest('order', $params), true);
        if ($response && isset($response['error']) && !$response['error']) {
            return [$response['error'], $response['msg'], $response['link']];
        }

        return [true, iaLanguage::get('error')];
    }

    public function getOrderInfo($hash, $invoiceId)
    {
        $params = [
            'uid' => $this->username,
            'pwd' => $this->password,
            'secret' => $this->secret,
            'id' => $invoiceId,
            'hash' => $hash,
        ];

        $response = json_decode($this->_sendRequest('order_info', $params), true);

        if ($response['error']) {
            return false;
        }
        return $response;
    }

    protected function _sendRequest($action, $params)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . '?p=' . $action,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            $message = 'Tahseeel: cURL Error #: ' . $error;
            iaDebug::log($message);
            exit($message);
        }

        return $response;
    }
}