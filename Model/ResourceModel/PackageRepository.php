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

namespace Mageviper\Dpd\Model\ResourceModel;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageviper\Dpd\Api\Data\PackageInterface;
use Mageviper\Dpd\Api\PackageRepositoryInterface;
use Mageviper\Dpd\Model\Package;
use Mageviper\Dpd\Model\PackageFactory;
use Mageviper\Dpd\Model\ResourceModel\Package\CollectionFactory;
use Mageviper\Dpd\Model\Spi\PackageRegistryInterface;
use Mageviper\Dpd\Model\Spi\PackageResourceInterface;

/**
 * Class PackageRepository
 */
class PackageRepository implements PackageRepositoryInterface
{

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;
    /**
     * @var PackageFactory
     */
    protected $packageFactory;
    /**
     * @var PackageResourceInterface
     */
    protected $packageResource;
    /**
     * @var PackageRegistryInterface
     */
    protected $packageRegistry;
    /**
     * @var SearchResultFactory
     */
    protected $searchResultsFactory;
    /**
     * @var CollectionFactory
     */
    protected $packageCollectionFactory;
    protected $package = [];
    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * PackageRepository constructor.
     * @param PackageFactory                $packageFactory
     * @param PackageResourceInterface      $packageResource
     * @param PackageRegistryInterface      $packageRegistry
     * @param SearchResultFactory           $searchResultsFactory
     * @param CollectionFactory             $packageCollectionFactory
     * @param CollectionProcessorInterface  $collectionProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        PackageFactory $packageFactory,
        PackageResourceInterface $packageResource,
        PackageRegistryInterface $packageRegistry,
        SearchResultFactory $searchResultsFactory,
        CollectionFactory $packageCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->packageFactory                = $packageFactory;
        $this->packageResource               = $packageResource;
        $this->packageRegistry               = $packageRegistry;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->searchResultsFactory          = $searchResultsFactory;
        $this->packageCollectionFactory      = $packageCollectionFactory;
        $this->collectionProcessor           = $collectionProcessor;
    }

    /**
     * @param PackageInterface $package
     * @return PackageInterface
     * @throws NoSuchEntityException
     */
    public function save(PackageInterface $package): PackageInterface
    {
        $prevStoredData = [];

        $packageData = $this->extensibleDataObjectConverter->toNestedArray(
            $package,
            [],
            PackageInterface::class
        );

        if ($package->getId() == 0) {
            $package->setId(null);
        } elseif ($package->getId() !== null) {
            $prevStoredData = $this->extensibleDataObjectConverter->toNestedArray(
                $this->getById($package->getId()),
                [],
                PackageInterface::class
            );
        }

        if ($this->modelHasDifferentData($packageData, $prevStoredData)) {
            /** @var Package $packageModel */
            $packageModel = $this->packageFactory->create();
            $packageModel->addData($packageData);
            if ($packageModel->getId() == 0) {
                $packageModel->setId(null);
            }

            $this->packageResource->save($packageModel);
            $this->packageRegistry->push($packageModel);

            $package->setId((int)$packageModel->getId());
        }

        return $package;
    }

    /**
     * @param int $packageId
     * @return PackageInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $packageId): PackageInterface
    {
        $packageModel = $this->packageRegistry->retrieve($packageId);

        return $packageModel->getDataModel();
    }

    public function getByOrderId(int $orderId): PackageInterface
    {
        $packageModel = $this->packageFactory->create();
        $this->packageResource->load($packageModel, $orderId, 'order_id');

        return $packageModel->getDataModel();
    }

    /**
     * @param PackageInterface $package
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(PackageInterface $package): bool
    {
        $this->deleteById($package->getId());
    }

    /**
     * @param int $packageId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $packageId): bool
    {
        try {
            $packageModel = $this->packageRegistry->retrieve($packageId);
            $this->packageResource->delete($packageModel);
            $this->packageRegistry->remove($packageId);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__("Unable to remove package '%1'", $packageId));
        }

        return true;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->packageCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setTotalCount($collection->getSize());

        $collection = $collection->getItems();

        array_walk($collection,
            function (Package $package) {
                $this->package[] = $package->getDataModel();
            });
        unset($collection);

        $searchResults->setItems($this->package);

        return $searchResults;
    }

    /**
     * Return information about different data
     *
     * @param array $data
     * @param array $prevData
     * @return bool - true on success if prev data is different
     */
    protected function modelHasDifferentData(array $data, array $prevData): bool
    {
        return !empty(array_diff($data, $prevData));
    }
}
