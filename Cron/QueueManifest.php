<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Cron
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */
declare(strict_types=1);

namespace Mageviper\Dpd\Cron;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\Data\ShipmentInterface;
use Mageviper\Dpd\Api\Data\ManifestInterface;
use Mageviper\Dpd\Api\Data\PackageInterface;
use Mageviper\Dpd\Helper\Data;
use Mageviper\Dpd\Model\DataManagement;
use Mageviper\Dpd\Model\Manifest;
use Mageviper\Dpd\Model\ResourceModel\ManifestRepository;
use Mageviper\Dpd\Model\ResourceModel\PackageRepository;
use Mageviper\Dpd\Service\Dpd;

/**
 * Class Dpd
 */
class QueueManifest
{
    /**
     * @var ManifestRepository
     */
    protected $manifestRepository;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var Dpd
     */
    protected $dpdService;
    /**
     * @var PackageRepository
     */
    protected $packageRepository;
    /**
     * @var DataManagement
     */
    protected $dataManagement;
    /**
     * @var DateTime
     */
    protected $dateTime;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * QueueManifest constructor.
     * @param ManifestRepository $manifestRepository
     * @param PackageRepository  $packageRepository
     * @param DataManagement     $dataManagement
     * @param DataObjectHelper   $dataObjectHelper
     * @param DateTime           $dateTime
     * @param Dpd                $dpdService
     * @param Data               $helper
     */
    public function __construct(
        ManifestRepository $manifestRepository,
        PackageRepository $packageRepository,
        DataManagement $dataManagement,
        DataObjectHelper $dataObjectHelper,
        DateTime $dateTime,
        Dpd $dpdService,
        Data $helper
    ) {
        $this->manifestRepository = $manifestRepository;
        $this->dataObjectHelper   = $dataObjectHelper;
        $this->dpdService         = $dpdService;
        $this->packageRepository  = $packageRepository;
        $this->dataManagement     = $dataManagement;
        $this->dateTime           = $dateTime;
        $this->helper             = $helper;
    }

    /**
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function execute(): void
    {
        if ($this->helper->getCronQueueStatus()) {
            /** @var ManifestInterface $manifest */
            $manifest = $this->dataManagement->getQueuedManifest();
            if ($manifest !== null) {
                $this->manifestChangeStatus($manifest, Manifest::PROCESSING);

                $packages = $this->dataManagement->prepareAllPackagesFromManifest($manifest->getId());
                /** @var PackageInterface $packageDO */
                foreach ($packages as $packageDO) {
                    if (!empty($packageDO->getTrackingNumber()) && $packageDO->getPackageId() !== null) {
                        continue;
                    }
                    $package = $this->dpdService->preparePackage($packageDO);
                    $packageDO->setPackageId($package->getId());
                    $packageDO->setTrackingNumber($package->getWaybill());
                    $this->dataObjectHelper->populateWithArray(
                        $packageDO,
                        $packageDO->getData(),
                        PackageInterface::class
                    );
                    $updatedPackage = $this->packageRepository->save($packageDO);
                    /** @var ShipmentInterface $shipment */
                    $shipment = $this->dataManagement->prepareShipment((int)$updatedPackage->getOrderId());
                    $this->dataManagement->addTrack($shipment, $updatedPackage->getTrackingNumber());
                }
                $manifest->setSendAt($this->dateTime->gmtTimestamp());
                $this->manifestChangeStatus($manifest, Manifest::APPLIED);
            }
        }
    }

    /**
     * @param ManifestInterface $manifest
     * @param int               $status
     * @throws NoSuchEntityException
     */
    protected function manifestChangeStatus(ManifestInterface $manifest, int $status): void
    {
        $manifest->setStatus($status);
        $this->dataObjectHelper->populateWithArray(
            $manifest,
            $manifest->getData(),
            ManifestInterface::class
        );
        $this->manifestRepository->save($manifest);
    }
}
