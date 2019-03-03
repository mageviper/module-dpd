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
use Mageviper\Dpd\Api\Data\ManifestInterface;
use Mageviper\Dpd\Api\ManifestRepositoryInterface;
use Mageviper\Dpd\Model\Manifest;
use Mageviper\Dpd\Model\ManifestFactory;
use Mageviper\Dpd\Model\ResourceModel\Manifest\CollectionFactory;
use Mageviper\Dpd\Model\Spi\ManifestRegistryInterface;
use Mageviper\Dpd\Model\Spi\ManifestResourceInterface;

/**
 * Class ManifestRepository
 */
class ManifestRepository implements ManifestRepositoryInterface
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;
    /**
     * @var ManifestFactory
     */
    protected $manifestFactory;
    /**
     * @var ManifestResourceInterface
     */
    protected $manifestResource;
    /**
     * @var ManifestRegistryInterface
     */
    protected $manifestRegistry;
    /**
     * @var SearchResultFactory
     */
    protected $searchResultsFactory;
    /**
     * @var CollectionFactory
     */
    protected $manifestCollectionFactory;
    protected $manifest = [];
    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * ManifestRepository constructor.
     * @param ManifestFactory               $manifestFactory
     * @param ManifestResourceInterface     $manifestResource
     * @param ManifestRegistryInterface     $manifestRegistry
     * @param SearchResultFactory           $searchResultsFactory
     * @param CollectionFactory             $manifestCollectionFactory
     * @param CollectionProcessorInterface  $collectionProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ManifestFactory $manifestFactory,
        ManifestResourceInterface $manifestResource,
        ManifestRegistryInterface $manifestRegistry,
        SearchResultFactory $searchResultsFactory,
        CollectionFactory $manifestCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->manifestFactory               = $manifestFactory;
        $this->manifestResource              = $manifestResource;
        $this->manifestRegistry              = $manifestRegistry;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->searchResultsFactory          = $searchResultsFactory;
        $this->manifestCollectionFactory     = $manifestCollectionFactory;
        $this->collectionProcessor           = $collectionProcessor;
    }

    /**
     * @param ManifestInterface $manifest
     * @return ManifestInterface
     * @throws NoSuchEntityException
     */
    public function save(ManifestInterface $manifest): ManifestInterface
    {
        $prevStoredData = [];

        $manifestData = $this->extensibleDataObjectConverter->toNestedArray(
            $manifest,
            [],
            ManifestInterface::class
        );

        if ($manifest->getId() === 0) {
            $manifest->setId(null);
        }

        if ($manifest->getId() !== null) {
            $prevStoredData = $this->extensibleDataObjectConverter->toNestedArray(
                $this->getById((int)$manifest->getId()),
                [],
                ManifestInterface::class
            );
        }

        if ($this->modelHasDifferentData($manifestData, $prevStoredData)) {
            /** @var Manifest $manifestModel */
            $manifestModel = $this->manifestFactory->create();
            $manifestModel->addData($manifestData);
            if ($manifestModel->getId() == 0) {
                $manifestModel->setId(null);
            }

            $this->manifestResource->save($manifestModel);
            $this->manifestRegistry->push($manifestModel);

            $manifest->setId($manifestModel->getId());
        }

        return $manifest;
    }

    /**
     * @param int $manifestId
     * @return ManifestInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $manifestId): ManifestInterface
    {
        $manifestModel = $this->manifestRegistry->retrieve($manifestId);

        return $manifestModel->getDataModel();
    }

    /**
     * @param ManifestInterface $manifest
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ManifestInterface $manifest): bool
    {
        $this->deleteById($manifest->getId());
    }

    /**
     * @param int $manifestId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $manifestId): bool
    {
        try {
            $manifestModel = $this->manifestRegistry->retrieve($manifestId);
            $this->manifestResource->delete($manifestModel);
            $this->manifestRegistry->remove($manifestId);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__("Unable to remove manifest '%1'", $manifestId));
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
        $collection = $this->manifestCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setTotalCount($collection->getSize());

        $collection = $collection->getItems();

        array_walk($collection,
            function (Manifest $manifest) {
                $this->manifest[] = $manifest->getDataModel();
            });
        unset($collection);

        $searchResults->setItems($this->manifest);

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
        return !empty(array_diff_assoc($data, $prevData));
    }
}
