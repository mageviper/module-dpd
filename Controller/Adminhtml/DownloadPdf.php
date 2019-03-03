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

namespace Mageviper\Dpd\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageviper\Dpd\Api\Data\PackageInterface;
use Mageviper\Dpd\Model\DataManagement;
use Mageviper\Dpd\Service\Dpd;

/**
 * Class DownloadPdf
 */
abstract class DownloadPdf extends Action
{
    /**
     * Base archive path
     */
    const BASE_PATH = 'shipment/DPD/';

    /**
     * @var FileFactory
     */
    protected $fileFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var string
     */
    protected $mediaUrl;
    /**
     * @var DataManagement
     */
    protected $management;
    /**
     * @var Dpd
     */
    protected $dpdService;

    /**
     * DownloadLabels constructor.
     * @param Context               $context
     * @param FileFactory           $fileFactory
     * @param StoreManagerInterface $storeManager
     * @param DataManagement        $management
     * @param Dpd                   $dpdService
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        StoreManagerInterface $storeManager,
        DataManagement $management,
        Dpd $dpdService
    ) {
        parent::__construct($context);
        $this->fileFactory  = $fileFactory;
        $this->storeManager = $storeManager;
        $this->mediaUrl     = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $this->management   = $management;
        $this->dpdService   = $dpdService;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        try {
            $content = $this->fileContent();
            if ($content !== null) {
                $this->fileFactory->create(
                    $this->pdfName(),
                    $content,
                    DirectoryList::MEDIA,
                    'application/pdf'
                );
            }
            $this->messageManager->addSuccessMessage($this->message());
            $this->_redirect('*/manifest/index');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

    }

    /**
     * @return string
     */
    protected function pdfName()
    {
        $manifestId = $this->getRequest()->getParam('manifest_id', null);

        return self::BASE_PATH . $manifestId . DIRECTORY_SEPARATOR . $this->fileName($manifestId);
    }

    /**
     * @param string $flag
     * @return mixed
     */
    public function fileContent()
    {
        $id          = (int)$this->getRequest()->getParam('manifest_id');
        $parcelId    = (int)$this->getRequest()->getParam('package_id');
        $packagesIds = [];
        try {
            if ($id !== 0) {
                $packages = $this->management->prepareAllPackagesFromManifest($id);
                /** @var PackageInterface $package */
                foreach ($packages as $package) {
                    $packagesIds[] = $package->getPackageId();
                }
            } else {
                $packagesIds = [$parcelId];
            }

            return $this->getContent($packagesIds);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

    }

    /**
     * @param $id
     * @return Phrase
     */
    abstract protected function fileName($id): Phrase;

    /**
     * @param array $packagesIds
     * @return mixed
     */
    abstract protected function getContent(array $packagesIds);

    /**
     * @return Phrase
     */
    abstract protected function message(): Phrase;
}