<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsxDesign\Tracker;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Context;
use Exception;
use Language;
use PrestaShop\Module\PsxDesign\Account\Provider\PsAccountDataProvider;
use PrestaShop\Module\PsxDesign\Account\Provider\TokenDecoder;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignAccountsException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignApiException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignTokenDecoderException;
use Ramsey\Uuid\Uuid;
use Segment;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ServerBag;

class SegmentTracker implements TrackerInterface
{
    private const COOKIE_ANONYMOUS_ID = 'psxdesign_anonymous_id';

    /**
     * @var PsAccountDataProvider
     */
    private $accountDataProvider;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Language
     */
    private $language;

    /**
     * @var TokenDecoder
     */
    private $decoder;

    /**
     * @var string
     */
    private $segmentKey;

    /**
     * @var string
     */
    private $moduleVersion;

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var string
     */
    private $currentTheme;

    /**
     * @var string
     */
    private $event = '';

    /**
     * @var string|null
     */
    private static $anonymousId;

    /**
     * @var array<string, mixed>
     */
    private $properties = [];

    public function __construct(
        PsAccountDataProvider $accountDataProvider,
        Context $context,
        TokenDecoder $decoder,
        string $segmentKey,
        string $moduleVersion,
        string $moduleName,
        string $currentTheme
    ) {
        $this->accountDataProvider = $accountDataProvider;
        $this->context = $context;
        $this->language = $this->context->language;
        $this->segmentKey = $segmentKey;
        $this->decoder = $decoder;
        $this->moduleVersion = $moduleVersion;
        $this->moduleName = $moduleName;
        $this->currentTheme = $currentTheme;
        $this->initSegment();
        $this->initAnonymousId();
    }

    /**
     * Track event on segment
     *
     * @param string $event
     * @param array $properties
     * @param ServerBag|null $serverBag
     *
     * @return void
     */
    public function track(string $event, array $properties = [], ServerBag $serverBag = null): void
    {
        $this->setProperties($properties);
        $this->setEvent($event);

        if (!$serverBag) {
            $serverBag = $this->getServerBag();
        }

        $message = $this->buildMessage($this->getUserId(), $serverBag);

        $this->segmentTrack($message);
    }

    /**
     * @param array $message
     *
     * @return void
     */
    public function segmentTrack(array $message): void
    {
        try {
            Segment::track($message);
            Segment::flush();
        } catch (Exception $e) {
            throw new PsxDesignApiException('Failed to send data to segment', PsxDesignApiException::FAILED_TO_SEND_DATA_TO_SEGMENT);
        }
    }

    /**
     * Init segment client with the api key
     */
    private function initSegment(): void
    {
        Segment::init($this->segmentKey);
    }

    /**
     * @param string|null $userId
     * @param ServerBag $serverBag
     *
     * @return array
     */
    public function buildMessage(?string $userId, ServerBag $serverBag): array
    {
        $userAgent = $serverBag->get('HTTP_USER_AGENT');
        $referer = $serverBag->get('HTTP_REFERER');
        $httpHost = $serverBag->get('HTTP_HOST');
        $requestUri = $serverBag->get('REQUEST_URI');
        $url = ($serverBag->get('HTTPS') !== null && $serverBag->get('HTTPS') === 'on' ? 'https' : 'http') . "://$httpHost$requestUri";

        return [
            'userId' => $userId,
            'anonymousId' => $this->getAnonymousId(),
            'event' => $this->getEvent(),
            'channel' => 'browser',
            'context' => [
                'userAgent' => $userAgent,
                'locale' => $this->language->iso_code,
                'page' => [
                    'referrer' => $referer,
                    'url' => $url,
                ],
            ],
            'properties' => array_merge([
                'module' => $this->moduleName,
                'module_version' => $this->moduleVersion,
            ], $this->getProperties()),
        ];
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     */
    public function setEvent(string $event): void
    {
        $this->event = $event;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array<string, mixed> $properties
     */
    public function setProperties(array $properties): void
    {
        if (!isset($properties['theme_name'])) {
            $properties['theme_name'] = $this->currentTheme;
        }

        $this->properties = $properties;
    }

    /**
     * @return string
     */
    public function getAnonymousId(): string
    {
        $this->initAnonymousId();

        return self::$anonymousId;
    }

    /**
     * Returns server bag if not provided
     *
     * @return ServerBag
     */
    private function getServerBag(): ServerBag
    {
        return Request::createFromGlobals()->server;
    }

    /**
     * Returns Cookies
     *
     * @return ParameterBag
     */
    private function getCookies(): ParameterBag
    {
        return Request::createFromGlobals()->cookies;
    }

    /**
     * Initialize anonymous id which is generated in front end.
     *
     * @return void
     */
    private function initAnonymousId(): void
    {
        try {
            $allCookies = $this->getCookies();
            $cookie = $allCookies->get(self::COOKIE_ANONYMOUS_ID);
        } catch (Exception $e) {
            $cookie = $this->context->cookie->{self::COOKIE_ANONYMOUS_ID} ?? null;
        }

        if (!isset(self::$anonymousId)) {
            if ($cookie) {
                self::$anonymousId = $cookie;
            } else {
                $this->generateAnonymousId();
            }
        }
    }

    /**
     * Cookie generation in front end is asynchronously, so could be that cookie still does not exist
     * we need to wait and check again in case cookie still do not exist we generate new one.
     *
     * @return void
     */
    private function generateAnonymousId(): void
    {
        sleep(3);
        $cookie = $this->context->cookie->{self::COOKIE_ANONYMOUS_ID} ?? null;

        if (!$cookie) {
            self::$anonymousId = Uuid::uuid4()->toString();
            /* we ignore next line cause it's not an error, we just follow the PS dev doc => https://devdocs.prestashop-project.org/8/development/components/cookie/ */
            /* @phpstan-ignore-next-line */
            $this->context->cookie->{self::COOKIE_ANONYMOUS_ID} = self::$anonymousId;
        } else {
            self::$anonymousId = $cookie;
        }
    }

    /**
     * @return string|null
     */
    private function getUserId(): ?string
    {
        $userId = null;

        try {
            $accessToken = $this->accountDataProvider->getOrRefreshAccessToken();
            if ($accessToken) {
                $userId = $this->decoder->decode($accessToken)->getUserId();
            }
        } catch (PsxDesignTokenDecoderException|PsxDesignAccountsException $e) {
            return null;
        }

        return $userId;
    }
}
