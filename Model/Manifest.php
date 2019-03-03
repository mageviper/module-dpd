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

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageviper\Dpd\Api\Data\ManifestInterface;
use Mageviper\Dpd\Api\Data\ManifestInterfaceFactory;

/**
 * Class Manifest
 *
 * Abstract Model Magic methods
 *
 * @method string   getCreatedAt(): string
 * @method $this    setCreatedAt($timestamp): Manifest
 * @method string   getUpdatedAt(): string
 * @method $this    setUpdatedAt($timestamp): Manifest
 * @method string   getSendAt(): string
 * @method $this    setSendAt($timestamp): Manifest
 * @method int      getPackages(): int
 * @method $this    setPackages(int $packages): Manifest
 * @method int      getStatus(): int
 * @method $this    setStatus(int $status): Manifest
 */
class Manifest extends AbstractModel implements IdentityInterface
{

    const PREPARE_TO_APPLY = 0;
    const APPLIED          = 1;
    const QUEUE            = 10;
    const PROCESSING       = 20;
    const ERROR            = 50;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var ManifestInterfaceFactory
     */
    protected $manifestDataFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        DataObjectHelper $dataObjectHelper,
        ManifestInterfaceFactory $manifestDataFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->dataObjectHelper    = $dataObjectHelper;
        $this->manifestDataFactory = $manifestDataFactory;
    }

    public function _construct()
    {
        $this->_init(ResourceModel\Manifest::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [ResourceModel\Manifest::MAIN_TABLE . '_' . $this->getId()];
    }

    public function getDataModel(): ManifestInterface
    {
        $manifestData = $this->getData();

        /** @var ManifestInterface $manifestDataObject */
        $manifestDataObject = $this->manifestDataFactory->create();

        $this->dataObjectHelper->populateWithArray(
            $manifestDataObject,
            $manifestData,
            ManifestInterface::class
        );

        return $manifestDataObject;
    }

}
