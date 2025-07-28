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

namespace PrestaShop\Module\Assistant\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\Module\Assistant\Api\MakeCurl;
use PrestaShop\Module\Assistant\Security\Verify;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Bundle\SecurityBundle\Security;
use PrestaShopBundle\Entity\Employee\Employee as EmployeeEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Tools;

class AutoLoginController extends PrestaShopAdminController
{
    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        Configuration $configuration
    ) {}

    public function index(Request $request)
    {
        $this->validateRequest($request);
        $employee = EmployeeLib::getEmployee(true);
        \Context::getContext()->employee = $employee;

        $employeeEntity = $this->entityManager
            ->getRepository(EmployeeEntity::class)
            ->findOneBy(['id' => $employee->id]);

        if (!$employeeEntity) {
            throw new \Exception('Employee not found');
        }

        $response = $this->security->login(
            $employeeEntity,
            'security.authenticator.form_login.main'
        );

        return $response instanceof RedirectResponse
            ? $response
            : new RedirectResponse($request->getBaseUrl());
    }

    private function validateRequest(Request $request)
    {
        if (!$this->configuration->get('PSASSISTANT_ISBOACCESSIBLE')) {
            Tools::redirect('page_not_found');
            die;
        }
        $payload = base64_decode($request->query->get('payload'));
        $signature = $request->query->get('signature');
        $decoded_payload = json_decode($payload, true);
        if (!$payload || !$signature || $decoded_payload['action'] !== 'access_bo') {
            Tools::redirect('page_not_found');
            die;
        }
        $public_key = MakeCurl::getPublicKey($decoded_payload['shop_uuid']);
        if (!Verify::verifyPayload($payload, $signature, $public_key)) {
            Tools::redirect('page_not_found');
            die;
        }
    }
}
