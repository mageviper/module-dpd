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
class Queue extends Action
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

    public function __construct(
        Action\Context $context,
        ManifestRepository $manifestRepository,
        PackageRepository $packageRepository,
        DataManagement $dataManagement,
        DataObjectHelper $dataObjectHelper,
        Dpd $dpdService
    ) {
        parent::__construct($context);
        $this->manifestRepository = $manifestRepository;
        $this->dataObjectHelper   = $dataObjectHelper;
        $this->dpdService         = $dpdService;
        $this->packageRepository  = $packageRepository;
        $this->dataManagement     = $dataManagement;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('manifest_id');
        if ($id !== null) {
            $manifest = $this->manifestRepository->getById($id);
            $manifest->setStatus(Manifest::QUEUE);

            $this->dataObjectHelper->populateWithArray(
                $manifest,
                $manifest->getData(),
                ManifestInterface::class
            );
            $this->manifestRepository->save($manifest);

            $this->messageManager->addSuccessMessage(__('Manifest added to cron schedule'));
            $this->_redirect('*/manifest/index');
        }
    }
}
