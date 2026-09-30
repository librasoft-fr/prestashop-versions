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

use KlaviyoV3Sdk\Exception\KlaviyoException;
use CartRule;
use Db;
use Exception;
use PrestaShopDatabaseException;
use PrestaShopException;
use Validate;

class CartRuleService
{
    /**
     * Duplicate a CartRule and change properties with $params
     *
     * @param CartRule $cartRuleSource
     * @param array $params
     * @return CartRule
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function duplicate(
        CartRule $cartRuleSource,
        $params
    ) {
        $cartRuleDuplicated = new CartRule((int)$cartRuleSource->id);

        foreach ($params as $key => $value) {
            $cartRuleDuplicated->{$key} = $value;
        }

        $cartRuleDuplicated->add();

        // Condition to use if this bug is fixed in a future version by PrestaShop
        // if (version_compare(_PS_VERSION_, '8.1.1', '<')) {
            $this->copyConditions(
                (int)$cartRuleSource->id,
                (int)$cartRuleDuplicated->id
            );
        // } else {
        //     CartRule::copyConditions(
        //         (int)$cartRuleSource->id,
        //         (int)$cartRuleDuplicated->id
        //     );
        // }

        return $cartRuleDuplicated;
    }

    /**
     * get cart rule object by ID
     *
     * @param  int $cartRuleId
     * @return CartRule
     * @throws KlaviyoException
     */
    public function getObjectById($cartRuleId)
    {
        if (empty($cartRuleId) || !Validate::isUnsignedInt($cartRuleId)) {
            throw new KlaviyoException(sprintf("Error with cart rule ID '%s'.", $cartRuleId));
        }

        $cartRule = new CartRule($cartRuleId);

        if (!Validate::isLoadedObject($cartRule)) {
            throw new KlaviyoException(sprintf("The cart rule ID '%s' does not exist.", $cartRuleId));
        }

        return $cartRule;
    }

    /**
     * get cart rule by code
     *
     * @param  string $code
     * @return CartRule
     * @throws KlaviyoException
     */
    public function getByCode($code)
    {
        $cartRuleId = CartRule::getIdByCode($code);

        if (!$cartRuleId) {
            throw new KlaviyoException(sprintf("The cart rule '%s' does not exist.", $code));
        }

        return $this->getObjectById($cartRuleId);
    }

    /**
     * Delete a list of cart rule code
     *
     * @param  string[] $codes
     * @return string[]
     */
    public function deleteByCodes($codes)
    {
        $cartRuleNotExist = [];
        foreach ($codes as $code) {
            try {
                $cartRule = $this->getByCode($code);
                if (!$cartRule->delete()) {
                    throw new KlaviyoException();
                }
            } catch (Exception $e) {
                $cartRuleNotExist[] = $code;
            }
        }

        return $cartRuleNotExist;
    }

    /**
     * Fix the function CartRule::copyConditions of PrestaShop Core
     *
     * @param  mixed $id_cart_rule_source
     * @param  mixed $id_cart_rule_destination
     * @return void
     */
    public function copyConditions($id_cart_rule_source, $id_cart_rule_destination)
    {
        Db::getInstance()->execute('
		INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_shop` (`id_cart_rule`, `id_shop`)
		(SELECT ' . (int) $id_cart_rule_destination . ', id_shop FROM `' . _DB_PREFIX_ . 'cart_rule_shop` WHERE `id_cart_rule` = ' . (int) $id_cart_rule_source . ')');
        Db::getInstance()->execute('
		INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_carrier` (`id_cart_rule`, `id_carrier`)
		(SELECT ' . (int) $id_cart_rule_destination . ', id_carrier FROM `' . _DB_PREFIX_ . 'cart_rule_carrier` WHERE `id_cart_rule` = ' . (int) $id_cart_rule_source . ')');
        Db::getInstance()->execute('
		INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_group` (`id_cart_rule`, `id_group`)
		(SELECT ' . (int) $id_cart_rule_destination . ', id_group FROM `' . _DB_PREFIX_ . 'cart_rule_group` WHERE `id_cart_rule` = ' . (int) $id_cart_rule_source . ')');
        Db::getInstance()->execute('
		INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_country` (`id_cart_rule`, `id_country`)
		(SELECT ' . (int) $id_cart_rule_destination . ', id_country FROM `' . _DB_PREFIX_ . 'cart_rule_country` WHERE `id_cart_rule` = ' . (int) $id_cart_rule_source . ')');
        // KLAVIYO: Fix this query by adding distinct on the select to prevent a mysql duplicate entry error
        Db::getInstance()->execute('
		INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_combination` (`id_cart_rule_1`, `id_cart_rule_2`)
		(SELECT DISTINCT ' . (int) $id_cart_rule_destination . ', IF(id_cart_rule_1 != ' . (int) $id_cart_rule_source . ', id_cart_rule_1, id_cart_rule_2) FROM `' . _DB_PREFIX_ . 'cart_rule_combination`
		WHERE `id_cart_rule_1` = ' . (int) $id_cart_rule_source . ' OR `id_cart_rule_2` = ' . (int) $id_cart_rule_source . ')');

        // Todo : should be changed soon, be must be copied too
        // Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'cart_rule_product_rule` WHERE `id_cart_rule` = '.(int)$this->id);
        // Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'cart_rule_product_rule_value` WHERE `id_product_rule` NOT IN (SELECT `id_product_rule` FROM `'._DB_PREFIX_.'cart_rule_product_rule`)');
        // Copy products/category filters
        $products_rules_group_source = Db::getInstance()->executeS('
		SELECT id_product_rule_group,quantity FROM `' . _DB_PREFIX_ . 'cart_rule_product_rule_group`
		WHERE `id_cart_rule` = ' . (int) $id_cart_rule_source . ' ');

        foreach ($products_rules_group_source as $product_rule_group_source) {
            Db::getInstance()->execute('
			INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule_group` (`id_cart_rule`, `quantity`)
			VALUES (' . (int) $id_cart_rule_destination . ',' . (int) $product_rule_group_source['quantity'] . ')');
            $id_product_rule_group_destination = Db::getInstance()->Insert_ID();

            $products_rules_source = Db::getInstance()->executeS('
			SELECT id_product_rule,type FROM `' . _DB_PREFIX_ . 'cart_rule_product_rule`
			WHERE `id_product_rule_group` = ' . (int) $product_rule_group_source['id_product_rule_group'] . ' ');

            foreach ($products_rules_source as $product_rule_source) {
                Db::getInstance()->execute('
				INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule` (`id_product_rule_group`, `type`)
				VALUES (' . (int) $id_product_rule_group_destination . ',"' . pSQL($product_rule_source['type']) . '")');
                $id_product_rule_destination = Db::getInstance()->Insert_ID();

                $products_rules_values_source = Db::getInstance()->executeS('
				SELECT id_item FROM `' . _DB_PREFIX_ . 'cart_rule_product_rule_value`
				WHERE `id_product_rule` = ' . (int) $product_rule_source['id_product_rule'] . ' ');

                foreach ($products_rules_values_source as $product_rule_value_source) {
                    Db::getInstance()->execute('
					INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule_value` (`id_product_rule`, `id_item`)
					VALUES (' . (int) $id_product_rule_destination . ',' . (int) $product_rule_value_source['id_item'] . ')');
                }
            }
        }
    }

    public function getCartRuleByPrefix($cart, $prefix)
    {
        if (!Validate::isLoadedObject($cart)) {
            return null;
        }

        foreach ($cart->getCartRules(CartRule::FILTER_ACTION_ALL, false) as $e) {
            if (
                !isset($e['obj']) ||
                !Validate::isLoadedObject($e['obj']) ||
                !($e['obj'] instanceof CartRule)
            ) {
                continue;
            }

            /** @var CartRule $cartRule */
            $cartRule = $e['obj'];
            $cartRulePrefix = $this->getCartRuleCodePrefix($cartRule->code);

            if ($cartRulePrefix === null) {
                continue;
            }

            if ($cartRulePrefix === $prefix) {
                return $cartRule;
            }
        }

        return null;
    }

    public function getCartRuleCodePrefix($code)
    {
        $exploded = explode('_', $code, 2);

        if (count($exploded) !== 2) {
            return null;
        }

        return $exploded[0];
    }
}
