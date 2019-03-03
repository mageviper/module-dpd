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

use Mageviper\Dpd\Model\Manifest;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface ManifestRegistryInterface
 */
interface ManifestRegistryInterface
{
    /**
     * @param int $manifestId
     * @return Manifest
     * @throws NoSuchEntityException
     */
    public function retrieve(int $manifestId): Manifest;

    /**
     * @param int $manifestId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function remove(int $manifestId): bool;

    /**
     * @param Manifest $manifest
     * @return Manifest
     * @throws NoSuchEntityException
     */
    public function push(Manifest $manifest): Manifest;
}
