<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
namespace PrestaShop\Module\Assistant\Api;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MakeCurl
{
    const API_HOST = 'https://api-assistant.prestashop.com/';
    /**
     * Build curl call with php
     * @param string $url
     * @param array $data
     * @return false|mixed
     */
    public static function makeCurl($url, $data)
    {
        $ch = curl_init(self::API_HOST.$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            SendError::sendError('Cannot retrieve API datas');
        }
        curl_close($ch);
        $response = json_decode($response, true);
        if(!isset($response['status']) || $response['status'] === false) {
            return false;
        }
        return $response;
    }

    /**
     * Build payload for curl call
     * @param string $uuid
     * @return string
     */
    public static function getPublicKey($uuid)
    {
        if($key = self::makeCurl('get-pub-key', ['shop_uuid' => $uuid])) {
            return $key['public_key'];
        }
        SendError::sendError('Cannot retrieve public key');
        return false;
    }
}