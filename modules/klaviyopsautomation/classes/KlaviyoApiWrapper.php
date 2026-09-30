<?php

/**
 * Klaviyo
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact extensions@klaviyo.com
 *
 * @author    Klaviyo
 * @copyright Klaviyo
 * @license   commercial
 */

namespace KlaviyoPs\Classes;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Configuration;
use KlaviyoV3Sdk\Exception\KlaviyoApiException;
use KlaviyoV3Sdk\Exception\KlaviyoAuthenticationException;
use KlaviyoV3Sdk\Exception\KlaviyoRateLimitException;
use KlaviyoV3Sdk\Klaviyo;
use KlaviyoV3Sdk\KlaviyoV3Api;

class KlaviyoApiWrapper
{
    /** @var Klaviyo Client for Klaviyo's Api. */
    protected $client;

    public function __construct()
    {
        $this->client = new KlaviyoV3Api(Configuration::get('KLAVIYO_PUBLIC_API'), Configuration::get('KLAVIYO_PRIVATE_API'));
    }

    /**
     * Get all lists for specific Klaviyo account.
     *
     * @return mixed
     */
    public function getLists()
    {
        return $this->client->getLists();
    }

    /**
     * Subscribe email to the Subscriber List selected on configuration page (if selected).
     *
     * @param string $email
     * @throws KlaviyoApiException
     */
    public function subscribeCustomer($email)
    {
        $profile = [
            'type' => 'profile',
            'attributes' => [
                'email' => $email,
                'subscriptions' => [
                    'email' => [
                        'marketing' => [
                            'consent' => 'SUBSCRIBED'
                        ]
                    ]
                ]
            ]
        ];

        $listId = Configuration::get('KLAVIYO_SUBSCRIBER_LIST');

        if ($listId) {
            $this->client->subscribeMembersToList($listId, array($profile));
        }
    }


    /**
     * Subscribe email to the Subscriber List selected on configuration page (if selected).
     *
     * @param array $email
     * @throws KlaviyoApiException
     */
    public function subscribeSMSCustomer($address)
    {
        $attributes = [
            'subscriptions' => [
                'sms' => [
                    'marketing' => [
                        'consent' => 'SUBSCRIBED'
                    ]
                ]
            ]
        ];

        if (isset($address['mobile'])) {
            $attributes['phone_number'] = $address['mobile'];
        }
        if (isset($address['email'])) {
            $attributes['email'] = $address['email'];
        }

        $profile = array(
            'type' => 'profile',
            'attributes' => $attributes,
        );

        $listId = Configuration::get('KLAVIYO_SMS_SUBSCRIBER_LIST');
        if ($listId) {
            $this->client->subscribeMembersToList($listId, array($profile));
        }
    }

    /**
     * @param string $email
     * @param array $properties
     * @return array
     * @throws KlaviyoApiException
     * @throws KlaviyoAuthenticationException
     * @throws KlaviyoRateLimitException
     */
    public function updateProfileCustomProperties($email, $properties)
    {
        return $this->client->updateProfileCustomProperties($email, $properties);
    }

    /**
     * @param $email
     * @param $variantId
     * @param $shopId
     * @param $langId
     * @return array|string|null
     */
    public function createBackInStockSubscription($email, $variantId, $shopId, $langId)
    {
        $catalogId = KlaviyoUtils::formatKlaviyoCatalogIdentifier($shopId, $langId);
        // Double url encode catalog ID because Klaviyo doesn't like the colon delimiter and will attempt to decode.
        $encodedCatalogId = urlencode(urlencode($catalogId));
        // Format variant ID for Back in Stock subscription.
        $catalogVariantId = "\$prestashop:::$encodedCatalogId:::$variantId";
        return $this->client->createBackInStockSubscription($email, $catalogVariantId);
    }

    /**
     * Send event to Klaviyo using the Track endpoint.
     *
     * @param array $event
     * @return bool
     * @throws KlaviyoApiException
     */
    public function trackEvent(array $eventConfig)
    {
        $responseData = $this->client->createEvent($eventConfig);
        if (isset($responseData['errors']) && is_array($responseData['errors']) && !empty($responseData['errors'])) {
            return false;
        }

        return true;
    }
}
