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
use Magento\Sales\Api\OrderRepositoryInterface;
use Mageviper\Dpd\Api\Data\ManifestInterface;
use Mageviper\Dpd\Api\Data\ManifestInterfaceFactory;
use Mageviper\Dpd\Api\Data\PackageInterface;
use Mageviper\Dpd\Api\Data\PackageInterfaceFactory;
use Mageviper\Dpd\Helper\Data;
use Mageviper\Dpd\Model\DataManagement;
use Mageviper\Dpd\Model\Manifest;
use Mageviper\Dpd\Model\ResourceModel\ManifestRepository;
use Mageviper\Dpd\Model\ResourceModel\PackageRepository;

/**
 * Class PrepareManifest
 */
class PrepareManifest
{
    /**
     * @var DataManagement
     */
    protected $management;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var ManifestInterfaceFactory
     */
    protected $manifestInterface;
    /**
     * @var PackageInterfaceFactory
     */
    protected $packageInterface;
    /**
     * @var ManifestRepository
     */
    protected $manifestRepository;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var PackageRepository
     */
    protected $packageRepository;
    /**
     * @var Data
     */
    protected $helper;

    public function __construct(
        DataManagement $management,
        DataObjectHelper $dataObjectHelper,
        ManifestInterfaceFactory $manifestInterface,
        PackageInterfaceFactory $packageInterface,
        ManifestRepository $manifestRepository,
        OrderRepositoryInterface $orderRepository,
        PackageRepository $packageRepository,
        Data $helper
    ) {
        $this->management         = $management;
        $this->dataObjectHelper   = $dataObjectHelper;
        $this->manifestInterface  = $manifestInterface;
        $this->packageInterface   = $packageInterface;
        $this->manifestRepository = $manifestRepository;
        $this->orderRepository    = $orderRepository;
        $this->packageRepository  = $packageRepository;
        $this->helper             = $helper;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Mageviper\Dpd\Exception\NoOrdersException
     */
    public function execute()
    {
        if ($this->helper->getCronManifestStatus()) {
            if (!$this->management->checkManifestSchedule()) {

                if (!$this->management->checkManifestSchedule()) {
                    $orders   = $this->management->prepareOrders();
                    $manifest = $this->management->checkManifest();
                    if (!empty($orders)) {
                        if ($manifest === false) {
                            $manifestDO = $this->manifestInterface->create();
                        } else {
                            $manifestDO = $manifest;
                        }
                        $this->dataObjectHelper->populateWithArray(
                            $manifestDO,
                            $manifestDO->getData(),
                            ManifestInterface::class
                        );
                        $manifest = $this->manifestRepository->save($manifestDO);
                        foreach ($orders as $order) {
                            $package = $this->management->checkPackage((int)$order->getId());
                            if ($package === false) {
                                $packageDO = $this->packageInterface->create();
                                $packageDO->setOrderId((int)$order->getId());
                                $packageDO->setManifestId((int)$manifest->getData('id'));
                            } else {
                                $packageDO = $package;
                            }
                            $this->dataObjectHelper->populateWithArray(
                                $packageDO,
                                $packageDO->getData(),
                                PackageInterface::class
                            );
                            $this->packageRepository->save($packageDO);
                            $order->setDpdFlag(1);
                            $this->orderRepository->save($order);
                        }
                    }
                }
            }
        }
    }
}
