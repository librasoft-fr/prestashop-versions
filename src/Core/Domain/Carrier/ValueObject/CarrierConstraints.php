<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject;

class CarrierConstraints
{
    public function __construct(
        public readonly float $maxWeight,
        public readonly float $maxWidth,
        public readonly float $maxHeight,
        public readonly float $maxDepth,
    ) {
    }
}
