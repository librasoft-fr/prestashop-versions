<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShop\PrestaShop\Adapter\Order\Repository;

use OrderDetail;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderDetailNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Shipment\ValueObject\OrderDetailId;
use PrestaShop\PrestaShop\Core\Exception\CoreException;
use PrestaShop\PrestaShop\Core\Repository\AbstractObjectModelRepository;

class OrderDetailRepository extends AbstractObjectModelRepository
{
    /**
     * Gets legacy Order detail
     *
     * @param OrderDetailId $orderDetailId
     *
     * @return OrderDetail
     *
     * @throws CoreException
     */
    public function get(OrderDetailId $orderDetailId): OrderDetail
    {
        /** @var OrderDetail $orderDetail */
        $orderDetail = $this->getObjectModel(
            $orderDetailId->getValue(),
            OrderDetail::class,
            OrderDetailNotFoundException::class
        );

        return $orderDetail;
    }
}
