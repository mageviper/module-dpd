<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Controller
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */
declare(strict_types=1);

namespace Mageviper\Dpd\Controller\Adminhtml\Manifest\Action;

use Magento\Backend\App\Action;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageviper\Dpd\Api\Data\ManifestInterface;
use Mageviper\Dpd\Api\Data\PackageInterface;
use Mageviper\Dpd\Model\DataManagement;
use Mageviper\Dpd\Model\Manifest;
use Mageviper\Dpd\Model\ResourceModel\ManifestRepository;
use Mageviper\Dpd\Model\ResourceModel\PackageRepository;
use Mageviper\Dpd\Service\Dpd;

/**
 * Class Apply
 */
class Apply extends Action
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

    public function __construct(
        Action\Context $context,
        ManifestRepository $manifestRepository,
        PackageRepository $packageRepository,
        DataManagement $dataManagement,
        DataObjectHelper $dataObjectHelper,
        DateTime $dateTime,
        Dpd $dpdService
    ) {
        parent::__construct($context);
        $this->manifestRepository = $manifestRepository;
        $this->dataObjectHelper   = $dataObjectHelper;
        $this->dpdService         = $dpdService;
        $this->packageRepository  = $packageRepository;
        $this->dataManagement     = $dataManagement;
        $this->dateTime           = $dateTime;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('manifest_id');
        if ($id !== null) {
            $manifest = $this->manifestRepository->getById($id);
            $packages = $this->dataManagement->prepareAllPackagesFromManifest($id);
            $manifest->setStatus(Manifest::APPLIED);
            /** @var PackageInterface $packageDO */
            foreach ($packages as $packageDO) {
                if (!empty($packageDO->getTrackingNumber()) && $packageDO->getPackageId() !== null) {
                    continue;
                }
                try {
                    $package = $this->dpdService->preparePackage($packageDO);
                    $packageDO->setPackageId($package->getId());
                    $packageDO->setTrackingNumber($package->getWaybill());
                    $this->dataObjectHelper->populateWithArray(
                        $packageDO,
                        $packageDO->getData(),
                        PackageInterface::class
                    );
                    $updatedPackage = $this->packageRepository->save($packageDO);
                    $shipment       = $this->dataManagement->prepareShipment((int)$updatedPackage->getOrderId());
                    $this->dataManagement->addTrack($shipment, $updatedPackage->getTrackingNumber());
                } catch (\Exception $exception) {
                    $this->messageManager->addErrorMessage($exception->getMessage() . ' ' . __('Check orders postcode'));
                    $manifest->setStatus(Manifest::ERROR);
                    continue;
                }

            }

            $manifest->setSendAt($this->dateTime->gmtTimestamp());
            $this->dataObjectHelper->populateWithArray(
                $manifest,
                $manifest->getData(),
                ManifestInterface::class
            );
            $this->manifestRepository->save($manifest);
            $this->messageManager->addSuccessMessage(__('Manifest added to cron schedule'));
        }
        $this->_redirect('*/manifest/index');
    }
}
