<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Model
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */
declare(strict_types=1);

namespace Mageviper\Dpd\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Mageviper\Dpd\Model\Spi\PackageResourceInterface;

/**
 * Class PackageRegistry
 */
class PackageRegistry implements Spi\PackageRegistryInterface
{

    /**
     * @var PackageFactory
     */
    protected $packageFactory;
    /**
     * @var PackageResourceInterface
     */
    protected $packageResource;
    protected $packageRegistryById = [];

    public function __construct(
        PackageFactory $packageFactory,
        PackageResourceInterface $packageResource
    ) {
        $this->packageFactory  = $packageFactory;
        $this->packageResource = $packageResource;
    }

    /**
     * @param int $packageId
     * @return Package
     * @throws NoSuchEntityException
     */
    public function retrieve(int $packageId): Package
    {
        if (isset($this->packageRegistryById[$packageId])) {
            return $this->packageRegistryById[$packageId];
        }

        /** @var Package $packageModel */
        $packageModel = $this->packageFactory->create();
        $this->packageResource->load($packageModel, $packageId);

        if (!$packageModel->getId()) {
            throw new NoSuchEntityException(__("Package with specified ID '%1' not found", $packageId));
        }
        $this->packageRegistryById[$packageId] = $packageModel;

        return $packageModel;
    }

    /**
     * @param int $packageId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function remove(int $packageId): bool
    {
        if (isset($this->packageRegistryById[$packageId])) {
            unset($this->packageRegistryById[$packageId]);

            return true;
        }

        return false;
    }

    /**
     * @param Package $package
     * @return Package
     * @throws NoSuchEntityException
     */
    public function push(Package $package): Package
    {
        if (!$package->getId()) {
            throw new NoSuchEntityException(__("Package hasn't been found"));
        }

        $this->packageRegistryById[$package->getId()] = $package;

        return $package;
    }

}
