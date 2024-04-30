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

namespace KlaviyoPs\Classes\KlaviyoServices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use ArrayObject;
use Exception;
use KlaviyoV3Sdk\Exception\KlaviyoException;
use KlaviyoPs\Classes\BusinessLogicServices\OrderPayloadService;
use KlaviyoPs\Classes\KlaviyoApiWrapper;
use KlaviyoPs\Classes\KlaviyoUtils;
use KlaviyoPs\Classes\KlaviyoValue;
use KlaviyoPs\Classes\PrestashopServices\OrderService;
use KlaviyoPs\Classes\PrestashopServices\ProductService;
use Tools;

class OrderEventService
{
    /**
     * @var KlaviyoApiWrapper
     */
    protected $klaviyoApiWrapper;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var ProductService
     */
    protected $productService;

    /**
     * @var CustomerEventService
     */
    protected $customerEventService;

    public function __construct(
        KlaviyoApiWrapper $klaviyoApiWrapper,
        OrderService $orderService,
        ProductService $productService,
        CustomerEventService $customerEventService
    ) {
        $this->klaviyoApiWrapper = $klaviyoApiWrapper;
        $this->orderService = $orderService;
        $this->productService = $productService;
        $this->customerEventService = $customerEventService;
    }

    /**
     * Send Order event into Klaviyo in real time
     *
     * @param ArrayObject $order
     * @return void
     * @throws KlaviyoException
     */
    public function track(ArrayObject $order)
    {
        $orderObject = $this->orderService->getObject($order);

        $mappedOrderStatus = OrderPayloadService::getMappedOrderStatusValue($orderObject);
        if ($mappedOrderStatus === null) {
            return;
        }

        $customer = $this->orderService->getCustomer($order);
        $orderTime = $this->orderService->getDateUpdate($order);

        $this->klaviyoApiWrapper->trackEvent([
            'event' => $this->buildEvent($mappedOrderStatus),
            'customer_properties' => $this->customerEventService->buildPayload($customer),
            'properties' => $this->buildPayload($order),
            'time' => $orderTime - 1, // -1 because we want this event before Ordered Product events
        ]);
    }

    /**
     * Build properties payload for an order to send it into Klaviyo
     *
     * @param ArrayObject $order
     * @return array
     * @throws KlaviyoException
     */
    public function buildPayload(ArrayObject $order)
    {
        $orderObject = $this->orderService->getObject($order);
        $extraData = OrderPayloadService::buildPayload($orderObject);
        $products = $this->orderService->getProducts($order);

        return [
            '$value' => $this->orderService->getOrderTotal($order),
            '$event_id' => $order['id_order'],
            '$currency_code' => $this->orderService->getCurrencyCode($order),
            '$extra' => $extraData,
            '$service' => KlaviyoValue::SERVICE,
            'Categories' => $this->buildCategories($order),
            'ItemCount' => count($products),
            'Items' => $this->buildItems($order),
            'ItemsDetail' => $this->buildItemsDetail($order),
            'ShopId' => $order['id_shop'],
            'LanguageId' => $order['id_lang'],
            'LanguageIso' => $this->orderService->getIsoLang($order),
            'TotalDiscounts' => $this->orderService->getOrderTotalDiscount($order),
            'DiscountCodes' => $extraData['applied_cart_rule_codes'],
            'Tags' => $this->buildTags($order),
            'OrderStatus' => $this->orderService->getOrderStateTitle($order),
            'Shipping' => $this->buildShippingData($order),
        ];
    }

    /**
     * @param ArrayObject $order
     * @return string[]
     */
    protected function buildCategories(ArrayObject $order)
    {
        $res = [];
        $products = $this->orderService->getProducts($order);

        foreach ($products as $productDetail) {
            $res = array_merge(
                $res,
                $this->productService->getCategoryNames($productDetail, $order)
            );
        }

        return Tools::arrayUnique($res);
    }

    /**
     * @param ArrayObject $order
     * @return string[]
     */
    protected function buildTags(ArrayObject $order)
    {
        $res = [];
        $products = $this->orderService->getProducts($order);

        foreach ($products as $productDetail) {
            $res = array_merge(
                $res,
                $this->productService->getTags($productDetail, $order)
            );
        }

        return Tools::arrayUnique($res);
    }

    /**
     * @param ArrayObject $order
     * @return string[]
     */
    protected function buildItems(ArrayObject $order)
    {
        $res = [];
        $products = $this->orderService->getProducts($order);

        foreach ($products as $productDetail) {
            $res[] = $productDetail['product_name'];
        }

        return $res;
    }

    /**
     * @param ArrayObject $order
     * @return array
     */
    protected function buildItemsDetail(ArrayObject $order)
    {
        $res = [];
        $products = $this->orderService->getProducts($order);

        foreach ($products as $productDetail) {
            $data = [
                'ProductID' => $productDetail['id_product'],
                'SKU' => $productDetail['product_reference'],
                'ProductName' => $productDetail['product_name'],
                'ItemPrice' => KlaviyoUtils::formatPrice($productDetail['unit_price_tax_incl']),
                'RowTotal' => KlaviyoUtils::formatPrice($productDetail['total_price_tax_incl']),
                'Quantity' => (int)$productDetail['product_quantity'],
                'IsVirtual' => $this->productService->isVirtual($productDetail),
            ];

            // These data allow to handle transactional email "download_product" of PrestaShop in Klaviyo
            $downloadData = $this->orderService->getDownloadData(
                $order,
                $productDetail
            );
            if ($downloadData !== null) {
                $data['VirtualInfo'] = [
                    'DownloadLink' => $downloadData['link'],
                    'DownloadFilename' => $downloadData['filename'],
                    'DownloadDeadline' => $downloadData['deadline'],
                ];
            }

            $res[] = $data;
        }

        return $res;
    }

    /**
     * @param ArrayObject $order
     * @return array|null
     */
    protected function buildShippingData(ArrayObject $order)
    {
        try {
            $shippingData = $this->orderService->getShippingData($order);
        } catch (Exception $e) {
            return null;
        }

        return [
            'ShippingMethod' => $shippingData['carrier_name'] ?? null,
            'TrackingNumber' => $shippingData['tracking_number'] ?? null,
        ];
    }

    /**
     * @param string $mappedOrderStatus
     * @return string
     * @throws KlaviyoException must never happen
     */
    protected function buildEvent($mappedOrderStatus)
    {
        switch ($mappedOrderStatus) {
            case 'klaviyops-statuses-placed':
                return 'Placed Order Transactional';

            case 'klaviyops-statuses-refunded':
                return 'Refunded Order Transactional';

            case 'klaviyops-statuses-canceled':
                return 'Cancelled Order Transactional';

            case 'klaviyops-statuses-fulfilled':
                return 'Fulfilled Order Transactional';

            default:
                throw new KlaviyoException('Mapped order status is not valid');
        }
    }
}
