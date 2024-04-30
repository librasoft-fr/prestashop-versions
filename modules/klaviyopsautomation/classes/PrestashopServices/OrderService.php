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

namespace KlaviyoPs\Classes\PrestashopServices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use ArrayObject;
use Currency;
use Customer;
use KlaviyoV3Sdk\Exception\KlaviyoException;
use KlaviyoPs\Classes\KlaviyoUtils;
use Language;
use Order;
use OrderState;
use ProductDownload;
use Validate;

class OrderService
{
    /**
     * @var ContextService
     */
    protected $contextService;

    /**
     * @var ProductService
     */
    protected $productService;

    /**
     * @var CustomerService
     */
    protected $customerService;

    public function __construct(
        ContextService $contextService,
        ProductService $productService,
        CustomerService $customerService
    ) {
        $this->contextService = $contextService;
        $this->productService = $productService;
        $this->customerService = $customerService;
    }

    /**
     * Normalizing data from PrestaShop allows to use them in a uniform and secure way.
     * To use the methods of this class, start by normalizing data.
     *
     * @param Order|array $order
     * @return ArrayObject
     */
    public function normalize($order)
    {
        $orderObject = null;

        if (is_object($order)) {
            $orderObject = $order;

            $order = (array)$order;
            $order['id_order'] = $orderObject->id;
            $order['cache_object'] = $orderObject;

            unset($order['id']);
        }

        if (!isset($order['id_order'])) {
            $order['id_order'] = 0;
        }

        if (!isset($order['id_customer'])) {
            $order['id_customer'] = 0;
        }

        if (!isset($order['id_lang'])) {
            $order['id_lang'] = 0;
        }

        $order['id_order'] = (int) $order['id_order'];
        $order['id_customer'] = (int) $order['id_customer'];
        $order['id_lang'] = (int) $order['id_lang'];

        return $this->contextService->normalize($order);
    }

    /**
     * Get Order object from normalized data
     *
     * @param ArrayObject $order
     * @return Order
     * @throws KlaviyoException
     */
    public function getObject(ArrayObject $order)
    {
        if (!$order->offsetExists('cache_object')) {
            $order['cache_object'] = new Order($order['id_order']);

            if (!Validate::isLoadedObject($order['cache_object'])) {
                throw new KlaviyoException('Order is not valid');
            }
        }

        return $order['cache_object'];
    }

    /**
     * Get all products of an order and normalize each product
     *
     * @param ArrayObject $order
     * @return ArrayObject[]
     */
    public function getProducts(ArrayObject $order)
    {
        if (!$order->offsetExists('cache_products')) {
            $order['cache_products'] = [];
            $orderObject = $this->getObject($order);

            foreach ($orderObject->getProducts() as $productDetail) {
                $productDetail['id_product'] = (int)$productDetail['product_id'];
                $productDetail['id_product_attribute'] = (isset($productDetail['product_attribute_id']))
                    ? (int)$productDetail['product_attribute_id']
                    : 0
                ;

                $order['cache_products'][] = $this->productService->normalize(
                    $productDetail
                );
            }
        }

        return $order['cache_products'];
    }

    /**
     * @param ArrayObject $order
     * @return OrderState
     * @throws KlaviyoException
     */
    public function getOrderState(ArrayObject $order)
    {
        if (!$order->offsetExists('cache_order_state')) {
            $order['cache_order_state'] = new OrderState(
                $order['current_state'],
                $order['id_lang']
            );

            if (!Validate::isLoadedObject($order['cache_order_state'])) {
                throw new KlaviyoException('Order state is not valid');
            }
        }

        return $order['cache_order_state'];
    }

    /**
     * Get customer of an order and normalize it
     *
     * @param ArrayObject $order
     * @return ArrayObject
     * @throws KlaviyoException
     */
    public function getCustomer(ArrayObject $order)
    {
        if (!$order->offsetExists('cache_customer')) {
            $customer = new Customer($order['id_customer']);

            if (!Validate::isLoadedObject($customer)) {
                throw new KlaviyoException('Customer is not valid');
            }

            $order['cache_customer'] = $this->customerService->normalize(
                $customer
            );
        }

        return $order['cache_customer'];
    }

    /**
     * Get Shipping Data of an order by using Order::getShipping
     * Throw KlaviyoException if there are no shipping data for the order
     *
     * @param ArrayObject $order
     * @return array
     * @throws KlaviyoException
     */
    public function getShippingData(ArrayObject $order)
    {
        if (!$order->offsetExists('cache_shipping_data')) {
            $orderObject = $this->getObject($order);
            $shippingData = $orderObject->getShipping();

            if (
                !is_array($shippingData) ||
                empty($shippingData)
            ) {
                throw new KlaviyoException('Impossible to get shipping data');
            }

            $order['cache_shipping_data'] = $shippingData[0];
        }

        return $order['cache_shipping_data'];
    }

    /**
     * Get download data from virtual product in an order
     *
     * @see OrderHistory::changeIdOrderState
     * @param ArrayObject $order
     * @param ArrayObject $productDetail
     * @return array|null
     */
    public function getDownloadData(
        ArrayObject $order,
        ArrayObject $productDetail
    ) {
        if (!$productDetail->offsetExists('cache_download_data')) {
            try {
                if (!$this->productService->isVirtual($productDetail)) {
                    throw new KlaviyoException();
                }

                $idProductDownload = ProductDownload::getIdFromIdProduct($productDetail['id_product']);

                if (!$idProductDownload) {
                    throw new KlaviyoException();
                }

                $productDownload = new ProductDownload($idProductDownload);

                if (!Validate::isLoadedObject($productDownload)) {
                    throw new KlaviyoException();
                }

                $hash = (isset($productDetail['download_hash']))
                    ? $productDetail['download_hash']
                    : false
                ;

                $downloadLink = $productDownload->getTextLink(false, $hash);
                $downloadLink .= '&id_order=' . $order['id_order'];
                $downloadLink .= '&secure_key=' . $order['secure_key'];

                $productDetail['cache_download_data'] = [
                    'link' => $downloadLink,
                    'filename' => $productDownload->display_filename,
                    'deadline' => KlaviyoUtils::formatDate($productDetail['download_deadline']),
                ];
            } catch (KlaviyoException $e) {
                $productDetail['cache_download_data'] = null;
            }
        }

        return $productDetail['cache_download_data'];
    }

    /**
     * @param ArrayObject $order
     * @return float
     */
    public function getOrderTotal(ArrayObject $order)
    {
        $price = $order['total_paid'];
        $price = KlaviyoUtils::formatPrice($price);

        return $price;
    }

    /**
     * @param ArrayObject $order
     * @return float
     */
    public function getOrderTotalDiscount(ArrayObject $order)
    {
        $price = $order['total_discounts_tax_incl'];
        $price = KlaviyoUtils::formatPrice($price);

        return $price;
    }

    /**
     * @param ArrayObject $order
     * @return string|null
     */
    public function getCurrencyCode(ArrayObject $order)
    {
        if (!$order->offsetExists('cache_currency')) {
            $order['cache_currency'] = new Currency($order['id_currency']);
        }

        if (!Validate::isLoadedObject($order['cache_currency'])) {
            return null;
        }

        return $order['cache_currency']->iso_code;
    }

    /**
     * @param ArrayObject $order
     * @return string|null
     */
    public function getOrderStateTitle(ArrayObject $order)
    {
        try {
            $orderState = $this->getOrderState($order);

            return $orderState->name;
        } catch (KlaviyoException $e) {
            return null;
        }
    }

    /**
     * Get "date_upd" property of an Order and convert it in timestamp for Klaviyo
     *
     * @param ArrayObject $order
     * @return int
     */
    public function getDateUpdate(ArrayObject $order)
    {
        $date = (isset($order['date_upd'])) ? $order['date_upd'] : null;
        $defaultTime = time();

        return KlaviyoUtils::formatDate($date, $defaultTime);
    }

    /**
     * Get the ISO code that corresponds to the language used when the order was placed.
     *
     * @param ArrayObject $order
     * @return string
     */
    public function getIsoLang($order)
    {
        $idLang = $order['id_lang'];
        $res = Language::getIsoById($idLang);

        if ($res === false) {
            $res = (string)$idLang;
        }

        return $res;
    }
}
