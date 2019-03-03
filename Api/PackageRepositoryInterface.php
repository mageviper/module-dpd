<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Api
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */

namespace Mageviper\Dpd\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Mageviper\Dpd\Api\Data\PackageInterface;

/**
 * Interface PackageRepositoryInterface
 */
interface PackageRepositoryInterface
{
    /**
     * @param PackageInterface $package
     * @return PackageInterface
     */
    public function save(PackageInterface $package): PackageInterface;

    /**
     * @param int $packageId
     * @return PackageInterface
     */
    public function getById(int $packageId): PackageInterface;

    /**
     * @param int $orderId
     * @return PackageInterface
     */
    public function getByOrderId(int $orderId): PackageInterface;

    /**
     * @param PackageInterface $package
     * @return bool
     */
    public function delete(PackageInterface $package): bool;

    /**
     * @param int $packageId
     * @return bool
     */
    public function deleteById(int $packageId): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;
}
