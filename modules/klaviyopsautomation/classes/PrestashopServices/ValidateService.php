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

class ValidateService
{
    /**
     * @see https://docs.djangoproject.com/fr/4.1/_modules/django/core/validators/#EmailValidator
     */
    const EMAIL_DOMAIN_REGEX = '/((?:[A-Z0-9](?:[A-Z0-9-]{0,61}[A-Z0-9])?\.)+)(?:[A-Z0-9-]{2,63}(?<!-))$/i';

    /**
     * Check if an email is valide for Klaviyo
     *
     * @param string $email
     * @return bool
     */
    public function isEmail($email)
    {
        // Validate::isEmail is not good
        // This method allow unicode characters
        // But Klaviyo not allow it

        // So use filter_var($email, FILTER_VALIDATE_EMAIL) not allow it
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            return false;
        }

        // Use filter_var FILTER_VALIDATE_EMAIL is not always sufficient
        // So we add more check

        // User part can contain "@", so we need to split from the last "@"
        $temp = explode('@', $email);
        $domainPart = array_pop($temp);

        // Regular expression from django package for domain part
        if (preg_match(self::EMAIL_DOMAIN_REGEX, $domainPart) === 0) {
            return false;
        }

        return true;
    }
}
