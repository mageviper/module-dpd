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
use Mageviper\Dpd\Api\Data\PackageInterface;
use Mageviper\Dpd\Api\Data\PackageInterfaceFactory;

/**
 * Class Package
 *
 * Abstract Model Magic methods
 *
 * @method int getId(): ?int
 * @method int getOrderId(): ?int
 * @method $this setOrderId(int $oderId): Package
 * @method int getManifestId(): ?int
 * @method $this setManifestId(int $manifestId): Package
 * @method int getPackageId(): ?int
 * @method $this setPackageId(int $packageId): Package
 * @method string getTrackingNumber(): ?string
 * @method $this setTrackingNumber(string $waybill): Package
 *
 */
class Package extends AbstractModel implements IdentityInterface
{

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var PackageInterfaceFactory
     */
    protected $packageDataFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        DataObjectHelper $dataObjectHelper,
        PackageInterfaceFactory $packageDataFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->dataObjectHelper   = $dataObjectHelper;
        $this->packageDataFactory = $packageDataFactory;
    }

    public function _construct()
    {
        $this->_init(ResourceModel\Package::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [ResourceModel\Package::MAIN_TABLE . '_' . $this->getId()];
    }

    public function getDataModel(): PackageInterface
    {
        $packageData = $this->getData();

        /** @var PackageInterface $packageDataObject */
        $packageDataObject = $this->packageDataFactory->create();

        $this->dataObjectHelper->populateWithArray(
            $packageDataObject,
            $packageData,
            PackageInterface::class
        );

        return $packageDataObject;

    }
}
