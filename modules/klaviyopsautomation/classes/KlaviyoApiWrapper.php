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
        $profile = array(
            'type' => 'profile',
            'attributes' => array(
                'email' => $email,
                'subscriptions' => array(
                    'email' => [
                        'MARKETING'
                    ]
                )
            )
        );

        $listId = Configuration::get('KLAVIYO_SUBSCRIBER_LIST');

        if ($listId) {
            $this->client->subscribeMembersToList($listId, array($profile));
        }
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
