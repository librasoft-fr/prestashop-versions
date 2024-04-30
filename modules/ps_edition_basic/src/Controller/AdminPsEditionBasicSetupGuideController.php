<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsEditionBasic\Controller;

use PrestaShop\Module\PsEditionBasic\Presenter\SetupGuideDataPresenter;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminPsEditionBasicSetupGuideController extends FrameworkBundleAdminController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param string $serviceName
     *
     * @return object
     */
    public function get($serviceName)
    {
        if (null === $this->container) {
            $this->container = SymfonyContainer::getInstance();
        }

        return $this->container->get($serviceName);
    }

    /**
     * Get the steps for the setup guide
     *
     * @return JsonResponse
     */
    public function fetchInitialDatas(): JsonResponse
    {
        return new JsonResponse($this->getInitialDatas());
    }

    /**
     * Edit the steps for the setup guide
     *
     * @param $stepName string
     *
     * @return JsonResponse
     */
    public function editStep(Request $request): JsonResponse
    {
        $requestBodyContent = (array) json_decode((string) $request->getContent(false), true);
        $step = $requestBodyContent['step'];

        $stepName = isset($step['name']) ? $step['name'] : '';
        $isUserCompleted = isset($step['isUserCompleted']) && $step['isUserCompleted'] ? 1 : 0;

        $uc_stepName = \Tools::strtoupper($stepName);
        $type = in_array($uc_stepName, ['ACCOUNT']) ? 'AUTO' : 'USER';

        $this->getConfiguration()->set("PS_SETUP_GUIDE_STEP_{$uc_stepName}_{$type}_COMPLETED", $isUserCompleted);

        return new JsonResponse([
            'stepName' => $stepName,
        ]);
    }

    /**
     * Store modal hidden status for the setup guide
     *
     * @return JsonResponse
     */
    public function storeModalIsHidden(Request $request): JsonResponse
    {
        $requestBodyContent = (array) json_decode((string) $request->getContent(false), true);
        $modalIsHidden = $requestBodyContent['modalIsHidden'];

        $this->getConfiguration()->set('PS_SETUP_GUIDE_MODAL_IS_HIDDEN', $modalIsHidden);

        return new JsonResponse([
            'modalIsHidden' => $modalIsHidden,
        ]);
    }

    /**
     * Delete the steps for the setup guide
     *
     * @param $stepName string
     *
     * @return JsonResponse
     */
    public function deleteStep(Request $request, string $stepName): JsonResponse
    {
        $uc_stepName = \Tools::strtoupper($stepName);
        $type = in_array($uc_stepName, ['ACCOUNT']) ? 'AUTO' : 'USER';

        $this->getConfiguration()->set("PS_SETUP_GUIDE_STEP_{$uc_stepName}_{$type}_COMPLETED", false);

        return new JsonResponse([
            'stepName' => $stepName,
        ]);
    }

    private function getInitialDatas(): array
    {
        $setupGuideData = $this->setupGuideDataPresenterService();

        return [
            'isHidden' => (bool) $this->getConfiguration()->get('PS_SETUP_GUIDE_MODAL_IS_HIDDEN', false),
            'steps' => $setupGuideData->getSetupGuideData(),
        ];
    }

    private function setupGuideDataPresenterService(): SetupGuideDataPresenter
    {
        return $this->get('ps_edition_basic.presenter.setupGuideData');
    }
}
