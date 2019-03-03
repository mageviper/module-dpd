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
use Mageviper\Dpd\Api\Data\ManifestInterface;

/**
 * Interface ManifestRepositoryInterface
 */
interface ManifestRepositoryInterface
{
    /**
     * @param ManifestInterface $manifest
     * @return ManifestInterface
     */
    public function save(ManifestInterface $manifest): ManifestInterface;

    /**
     * @param int $manifestId
     * @return ManifestInterface
     */
    public function getById(int $manifestId): ManifestInterface;

    /**
     * @param ManifestInterface $manifest
     * @return bool
     */
    public function delete(ManifestInterface $manifest): bool;

    /**
     * @param int $manifestId
     * @return bool
     */
    public function deleteById(int $manifestId): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;
}
