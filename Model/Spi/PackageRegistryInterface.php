<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Model
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */

namespace Mageviper\Dpd\Model\Spi;

use Mageviper\Dpd\Model\Package;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface PackageRegistryInterface
 */
interface PackageRegistryInterface
{
    /**
     * @param int $packageId
     * @return Package
     * @throws NoSuchEntityException
     */
    public function retrieve(int $packageId): Package;

    /**
     * @param int $packageId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function remove(int $packageId): bool;

    /**
     * @param Package $package
     * @return Package
     * @throws NoSuchEntityException
     */
    public function push(Package $package): Package;
}
