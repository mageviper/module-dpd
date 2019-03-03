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
use Mageviper\Dpd\Model\Spi\ManifestResourceInterface;

/**
 * Class ManifestRegistry
 */
class ManifestRegistry implements Spi\ManifestRegistryInterface
{
    /**
     * @var ManifestFactory
     */
    protected $manifestFactory;
    /**
     * @var ManifestResourceInterface
     */
    protected $manifestResource;
    /**
     * @var array
     */
    protected $manifestRegistryById = [];

    /**
     * ManifestRegistry constructor.
     * @param ManifestFactory           $manifestFactory
     * @param ManifestResourceInterface $manifestResource
     */
    public function __construct(
        ManifestFactory $manifestFactory,
        ManifestResourceInterface $manifestResource
    ) {
        $this->manifestFactory  = $manifestFactory;
        $this->manifestResource = $manifestResource;
    }

    /**
     * @param int $manifestId
     * @return Manifest
     * @throws NoSuchEntityException
     */
    public function retrieve(int $manifestId): Manifest
    {
        if (isset($this->manifestRegistryById[$manifestId])) {
            return $this->manifestRegistryById[$manifestId];
        }

        $manifestModel = $this->manifestFactory->create();
        $this->manifestResource->load($manifestModel, $manifestId);

        if (!$manifestModel->getId()) {
            throw new NoSuchEntityException(__("Manifest with specific ID '%1' not found", $manifestId));
        }

        $this->manifestRegistryById[$manifestId] = $manifestModel;

        return $manifestModel;
    }

    /**
     * @param int $manifestId
     * @return bool
     * @throw NoSuchEntityException
     */
    public function remove(int $manifestId): bool
    {
        if (isset($this->manifestRegistryById[$manifestId])) {
            unset($this->manifestRegistryById[$manifestId]);

            return true;
        }

        return false;
    }

    /**
     * @param Manifest $manifest
     * @return Manifest
     * @throws NoSuchEntityException
     */
    public function push(Manifest $manifest): Manifest
    {
        if (!$manifest->getId()) {
            throw new NoSuchEntityException(__("Manifest hasn't been found"));
        }

        $this->manifestRegistryById[$manifest->getId()] = $manifest;

        return $manifest;
    }
}
