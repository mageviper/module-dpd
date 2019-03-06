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

namespace Mageviper\Dpd\Controller\Adminhtml\Package\Action;

use Composer\Package\PackageInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Model\OrderRepository;
use Mageviper\Dpd\Model\ResourceModel\PackageRepository;
use Mageviper\Dpd\Service\Dpd;
use T3ko\Dpd\Objects\RegisteredParcel;

/**
 * Class Renew
 */
class Renew extends Action
{
    /**
     * @var Dpd
     */
    protected $dpdService;
    /**
     * @var PackageRepository
     */
    protected $packageRepository;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    public function __construct(
        Action\Context $context,
        Dpd $dpdService,
        PackageRepository $packageRepository,
        OrderRepository $orderRepository,
        DataObjectHelper $dataObjectHelper
    ) {
        parent::__construct($context);
        $this->dpdService        = $dpdService;
        $this->packageRepository = $packageRepository;
        $this->dataObjectHelper  = $dataObjectHelper;
        $this->orderRepository   = $orderRepository;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function execute()
    {
        $orderId   = $this->getRequest()->getParam('order_id');
        $packageDO = $this->packageRepository->getByOrderId((int)$orderId);
        /** @var RegisteredParcel $package */
        $package = $this->dpdService->preparePackage($packageDO);
        $packageDO->setPackageId($package->getId());
        $packageDO->setTrackingNumber($package->getWaybill());
        $this->dataObjectHelper->populateWithArray(
            $packageDO,
            $packageDO->getData(),
            PackageInterface::class
        );
        $this->packageRepository->save($packageDO);
        $this->_redirect('*/*/', ['manifest_id' => $packageDO->getManifestId()]);
    }
}
