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

declare(strict_types=1);

namespace PrestaShop\Module\Psshipping\Controller\Admin;

use Context as LegacyContext;
use PrestaShop\Module\Psshipping\Domain\Api\Webhook;
use PrestaShop\Module\Psshipping\Exception\BadRequestException;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Psshipping;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit();
}

class PsshippingConfigurationController extends FrameworkBundleAdminController
{
    const MAX_WEIGHT_PER_PACKAGE = 'PS_SHIPPING_MAX_WEIGHT_PER_PACKAGE';
    const MAX_WIDTH_PER_PACKAGE = 'PS_SHIPPING_MAX_WIDTH_PER_PACKAGE';
    const MAX_HEIGHT_PER_PACKAGE = 'PS_SHIPPING_MAX_HEIGHT_PER_PACKAGE';
    const MAX_LENGTH_PER_PACKAGE = 'PS_SHIPPING_MAX_LENGTH_PER_PACKAGE';
    const ORDER_STATUS_MAPPING = 'PS_SHIPPING_ORDER_STATUS_MAPPING';
    const ORDER_MAPPING_IS_ACTIVATE = 'PS_SHIPPING_ORDER_MAPPING_IS_ACTIVATE';
    const ONBOARDING_IS_DONE = 'PS_SHIPPING_ONBOARDING_IS_DONE';
    const ADVANCED_ORDER_SETTING = 'PS_SHIPPING_ADVANCED_ORDER_SETTING';

    /** @var Psshipping */
    private $module;

    public function __construct(Psshipping $module)
    {
        $this->module = $module;
    }

    /**
     * Toggle the status of the get started page and install tabs
     * accordingly to the status.
     *
     * @return Response
     */
    public function toggleOnboardingStatusAction(): Response
    {
        $configuration = new Configuration();
        $context = LegacyContext::getContext();

        try {
            if (!empty($context) && !empty($context->shop)) {
                $configuration->restrictUpdatesTo($context->shop);
            }
            $onboardingIsDone = (bool) $configuration->get(self::ONBOARDING_IS_DONE, false);
            $configuration->set(self::ONBOARDING_IS_DONE, !$onboardingIsDone);
        } catch (\Exception $e) {
            return new Response(
                json_encode(['error' => 'An error occurred. Cannot toggle onboarding status.']),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        return new Response(
            json_encode(!$onboardingIsDone),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function setPackagesDimensions(Request $request): Response
    {
        try {
            $configuration = new Configuration();
            $context = LegacyContext::getContext();
            if (!empty($context) && !empty($context->shop)) {
                $configuration->restrictUpdatesTo($context->shop);
            }
            $requestBodyContent = (array) json_decode((string) $request->getContent(false), true);
            $requiredParams = ['weight', 'height', 'width', 'length'];
            $maxWeightPerPackage = 0;
            $maxHeightPerPackage = 0;
            $maxWidthPerPackage = 0;
            $maxLengthPerPackage = 0;

            foreach ($requiredParams as $params) {
                if (!in_array($params, array_keys($requestBodyContent))) {
                    return new Response(
                        json_encode([
                            'status' => false,
                            'error' => 'Missing ' . $params . ' parameter in request body',
                        ]),
                        400,
                        ['Content-Type' => 'application/json']
                    );
                }
            }

            if (is_numeric($requestBodyContent['weight'])) {
                $maxWeightPerPackage = floatval($requestBodyContent['weight']);
            }

            if (is_numeric($requestBodyContent['height'])) {
                $maxHeightPerPackage = floatval($requestBodyContent['height']);
            }

            if (is_numeric($requestBodyContent['width'])) {
                $maxWidthPerPackage = floatval($requestBodyContent['width']);
            }

            if (is_numeric($requestBodyContent['length'])) {
                $maxLengthPerPackage = floatval($requestBodyContent['length']);
            }

            if ($maxWeightPerPackage > 30) {
                return new Response(
                    json_encode([
                        'status' => false,
                        'error' => 'Weight must not exceed 30 kg',
                    ]),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }

            $configuration->set(self::MAX_WEIGHT_PER_PACKAGE, $maxWeightPerPackage);
            $configuration->set(self::MAX_WIDTH_PER_PACKAGE, $maxWidthPerPackage);
            $configuration->set(self::MAX_HEIGHT_PER_PACKAGE, $maxHeightPerPackage);
            $configuration->set(self::MAX_LENGTH_PER_PACKAGE, $maxLengthPerPackage);
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }

        return new Response(
            json_encode([
                'status' => true,
            ]),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function getPackagesDimensions(): Response
    {
        $configuration = new Configuration();
        $context = LegacyContext::getContext();
        if (!empty($context) && !empty($context->shop)) {
            $configuration->restrictUpdatesTo($context->shop);
        }

        $maxWeightPerPackage = 0;
        $maxHeightPerPackage = 0;
        $maxWidthPerPackage = 0;
        $maxLengthPerPackage = 0;

        if (is_numeric($configuration->get(self::MAX_WEIGHT_PER_PACKAGE, 0))) {
            $maxWeightPerPackage = floatval($configuration->get(self::MAX_WEIGHT_PER_PACKAGE, 0));
        }

        if (is_numeric($configuration->get(self::MAX_WIDTH_PER_PACKAGE, 0))) {
            $maxWidthPerPackage = floatval($configuration->get(self::MAX_WIDTH_PER_PACKAGE, 0));
        }

        if (is_numeric($configuration->get(self::MAX_HEIGHT_PER_PACKAGE, 0))) {
            $maxHeightPerPackage = floatval($configuration->get(self::MAX_HEIGHT_PER_PACKAGE, 0));
        }

        if (is_numeric($configuration->get(self::MAX_LENGTH_PER_PACKAGE, 0))) {
            $maxLengthPerPackage = floatval($configuration->get(self::MAX_LENGTH_PER_PACKAGE, 0));
        }

        return new Response(
            json_encode([
                'status' => true,
                'configuration' => [
                    'height' => $maxHeightPerPackage,
                    'width' => $maxWidthPerPackage,
                    'weight' => $maxWeightPerPackage,
                    'length' => $maxLengthPerPackage,
                ],
            ]),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function getAdvancedSetting(): Response
    {
        $configuration = new Configuration();
        $context = LegacyContext::getContext();
        if (!empty($context) && !empty($context->shop)) {
            $configuration->restrictUpdatesTo($context->shop);
        }

        return new Response(
            json_encode([
                'status' => true,
                'advancedSetting' => (bool) $configuration->get(self::ADVANCED_ORDER_SETTING, false),
            ]),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function setAdvancedSetting(Request $request): Response
    {
        try {
            $configuration = new Configuration();
            $context = LegacyContext::getContext();
            if (!empty($context) && !empty($context->shop)) {
                $configuration->restrictUpdatesTo($context->shop);
            }
            $requestBodyContent = (array) json_decode((string) $request->getContent(false), true);
            if (!isset($requestBodyContent['advancedValue'])) {
                return new Response(
                    json_encode([
                        'status' => false,
                        'error' => 'Missing advancedValue parameter in request body',
                    ]),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }

            $configuration->set(self::ADVANCED_ORDER_SETTING, $requestBodyContent['advancedValue']);
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }

        return new Response(
            json_encode([
                'status' => true,
            ]),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function setOrderStatusMapping(Request $request): Response
    {
        try {
            $configuration = new Configuration();
            $context = LegacyContext::getContext();
            if (!empty($context) && !empty($context->shop)) {
                $configuration->restrictUpdatesTo($context->shop);
            }
            $requestBodyContent = (array) json_decode((string) $request->getContent(false), true);
            if (!isset($requestBodyContent['mapping'])) {
                return new Response(
                    json_encode([
                        'status' => false,
                        'error' => 'Missing mapping parameter in request body',
                    ]),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }

            if ($configuration->get(self::ORDER_STATUS_MAPPING) === null || empty($configuration->get(self::ORDER_STATUS_MAPPING))) {
                (new Webhook($this->module))->saveSvixSecret();
            }

            $configuration->set(self::ORDER_STATUS_MAPPING, $requestBodyContent['mapping']);
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }

        return new Response(
            json_encode([
                'status' => true,
            ]),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function setStatusForOrderStatusMapping(Request $request): Response
    {
        try {
            $configuration = new Configuration();
            $context = LegacyContext::getContext();
            if (!empty($context) && !empty($context->shop)) {
                $configuration->restrictUpdatesTo($context->shop);
            }
            $requestBodyContent = (array) json_decode((string) $request->getContent(false), true);
            if (!isset($requestBodyContent['enabled'])) {
                return new Response(
                    json_encode([
                        'status' => false,
                        'error' => 'Missing enabled parameter in request body',
                    ]),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }

            $configuration->set(self::ORDER_MAPPING_IS_ACTIVATE, $requestBodyContent['enabled']);
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }

        return new Response(
            json_encode([
                'status' => true,
            ]),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function getStatusOrderStatusMapping(): Response
    {
        $configuration = new Configuration();
        $context = LegacyContext::getContext();

        if (!empty($context) && !empty($context->shop)) {
            $configuration->restrictUpdatesTo($context->shop);
        }

        $mapping = $configuration->get(self::ORDER_STATUS_MAPPING);

        return new Response(
            json_encode([
                'status' => true,
                'enabled' => (bool) $configuration->get(self::ORDER_MAPPING_IS_ACTIVATE, false),
                'mapping' => empty($mapping),
            ]),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function getStateForItaly(): Response
    {
        $state = [];

        // Type is wrong before PS 8
        // @phpstan-ignore notIdentical.alwaysTrue
        if (\Country::getByIso('IT') !== false) {
            $italyStates = array_filter(\State::getStates(), function ($value) {
                return (int) $value['id_country'] === \Country::getByIso('IT');
            });

            $state = array_map(function ($value) {
                return [
                    'isoCode' => $value['iso_code'],
                    'name' => $value['name'],
                ];
            }, $italyStates);
        }

        return new Response(
            json_encode([
                'status' => true,
                'state' => $state,
            ]),
            200,
            ['Content-Type' => 'application/json']
        );
    }
}
