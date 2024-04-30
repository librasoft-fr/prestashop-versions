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

namespace PrestaShop\Module\PsxDesign\Account\Provider;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Exception;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignTokenDecoderException;

class TokenDecoder
{
    private const USER_ID = 'sub';

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Token|null
     */
    private $decodedToken;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param string $token
     *
     * @return self
     */
    public function decode(string $token): TokenDecoder
    {
        try {
            $this->decodedToken = $this->parser->parse($token);
        } catch (Exception $exception) {
            throw new PsxDesignTokenDecoderException('Token is invalid', PsxDesignTokenDecoderException::INVALID_TOKEN);
        }

        return $this;
    }

    /**
     * @return string
     *
     * @throws PsxDesignTokenDecoderException
     */
    public function getUserId(): string
    {
        if ($this->decodedToken && $this->decodedToken->claims()->has(self::USER_ID)) {
            return $this->decodedToken->claims()->get(self::USER_ID);
        }

        throw new PsxDesignTokenDecoderException('Token does not exist or value does not have user id', PsxDesignTokenDecoderException::INVALID_TOKEN);
    }

    /**
     * @return Token|null
     */
    public function getDecodedToken(): ?Token
    {
        return $this->decodedToken;
    }
}
