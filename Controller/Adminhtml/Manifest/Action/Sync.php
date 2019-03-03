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
use Magento\Sales\Api\OrderRepositoryInterface;
use Mageviper\Dpd\Api\Data\ManifestInterface;
use Mageviper\Dpd\Api\Data\ManifestInterfaceFactory;
use Mageviper\Dpd\Api\Data\PackageInterface;
use Mageviper\Dpd\Api\Data\PackageInterfaceFactory;
use Mageviper\Dpd\Exception\NoOrdersException;
use Mageviper\Dpd\Model\DataManagement;
use Mageviper\Dpd\Model\ResourceModel\ManifestRepository;
use Mageviper\Dpd\Model\ResourceModel\PackageRepository;

/**
 * Class Sync
 */
class Sync extends Action
{

    /**
     * @var DataManagement
     */
    protected $management;
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
     * @var PackageRepository
     */
    protected $packageRepository;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Prepare constructor.
     * @param Action\Context           $context
     * @param DataManagement           $management
     * @param DataObjectHelper         $dataObjectHelper
     * @param ManifestInterfaceFactory $manifestInterface
     * @param PackageInterfaceFactory  $packageInterface
     * @param ManifestRepository       $manifestRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param PackageRepository        $packageRepository
     */
    public function __construct(
        Action\Context $context,
        DataManagement $management,
        DataObjectHelper $dataObjectHelper,
        ManifestInterfaceFactory $manifestInterface,
        PackageInterfaceFactory $packageInterface,
        ManifestRepository $manifestRepository,
        OrderRepositoryInterface $orderRepository,
        PackageRepository $packageRepository

    ) {
        parent::__construct($context);
        $this->management         = $management;
        $this->dataObjectHelper   = $dataObjectHelper;
        $this->manifestInterface  = $manifestInterface;
        $this->packageInterface   = $packageInterface;
        $this->manifestRepository = $manifestRepository;
        $this->packageRepository  = $packageRepository;
        $this->orderRepository    = $orderRepository;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return void
     * @throws NoOrdersException
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('manifest_id');
        try {
            $orders = $this->management->prepareOrders();
            if (!empty($orders)) {
                $manifestDO = $this->manifestRepository->getById($id);
                $manifestDO->setPackages((int)$manifestDO->getPackages() + count($orders));

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
                $this->messageManager->addSuccessMessage(__('Manifest was created successfully'));
            }
        } catch (NoOrdersException $e) {
            $this->messageManager->addWarningMessage(__($e->getMessage()));
            $this->_redirect('*/manifest/index');
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__("Something goes wrong: %1", $exception->getMessage()));
            $this->_redirect('*/manifest/index');
        }

        $this->_redirect('*/manifest/index');
    }
}
