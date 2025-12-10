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

namespace PrestaShop\Module\Psshipping\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PrestaShop\Module\Psshipping\Domain\PickupPoints\PsshippingAddressOrdersRepository")
 */
class PsshippingAddressOrders
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_order", type="integer", nullable=false)
     */
    private $idOrder;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_shop", type="integer", nullable=false)
     */
    private $idShop;

    /**
     * @var PsshippingAddress
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=PsshippingAddress::class)
     * @ORM\JoinColumn(name="id_address", referencedColumnName="id_address", nullable=false)
     */
    private $address;

    public function getIdOrder(): int
    {
        return $this->idOrder;
    }

    public function setIdOrder(int $idOrder): self
    {
        $this->idOrder = $idOrder;

        return $this;
    }

    public function getIdShop(): int
    {
        return $this->idShop;
    }

    public function setIdShop(int $idShop): self
    {
        $this->idShop = $idShop;

        return $this;
    }

    public function getAddress(): PsshippingAddress
    {
        return $this->address;
    }

    public function setAddress(PsshippingAddress $address): self
    {
        $this->address = $address;

        return $this;
    }
}
