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

 use PrestaShop\Module\Psshipping\Domain\GelProximity\Models\PickupPoint;

 class psshippingGelProximityNotificationHandlerModuleFrontController extends \ModuleFrontController
 {
     protected $validActions = ['setPickUpPointForCurrentCart'];

     public function init()
     {
         $this->ajax = true;
         $this->content_only = true;
         $this->controller_type = 'module';
     }

     private function respondAndDie($message, $data, $response_code = 500)
     {
         header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
         header('Cache-Control: post-check=0, pre-check=0', false);
         header('Pragma: no-cache');
         header('Content-Type: application/json');
         http_response_code($response_code);

         $response = [
            'status' => $response_code,
            'message' => $message,
            'data' => $data,
        ];

         echo json_encode($response, JSON_UNESCAPED_SLASHES);
         exit;
     }

     public function setPickUpPointForCurrentCart()
     {
         $payload = Tools::getValue('payload');
         /** @var array{
          *   pickupPointId: int,
          *   networkCode: string,
          *   code: string,
          *   address: string,
          *   description: string|null,
          *   city: string,
          *   zipCode: string,
          *   department: string|null,
          *   country: string
          * } $pickupPoint */
         $pickupPoint = json_decode($payload, true);
         $pickupPoint = PickupPoint::fromArray($pickupPoint);
         $pickupPoint->injectIntoCookies($this->context);

         $template = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'psshipping/views/templates/hook/pickUpPointDetails.tpl');
         $template->assign([
            'btnText' => $this->trans('Change pick-up point', [], 'Modules.Psshipping.Admin'),
            'detailsTitle' => $this->trans('Pick-up point selected:', [], 'Modules.Psshipping.Admin'),
            'pickupPoint' => $pickupPoint->toArray(),
        ]);

         return ['pickupsDetails' => $template->fetch()];
     }

     public function postProcess()
     {
         $action = Tools::getValue('action');

         if (!in_array($action, $this->validActions, true)) {
             $this->respondAndDie(
                ['unknown action'],
                false,
                400
            );
         } elseif ($action === 'setPickUpPointForCurrentCart') {
             $this->respondAndDie(
                false,
                $this->setPickUpPointForCurrentCart(),
                200
            );
         }
     }
 }
