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

namespace Mageviper\Dpd\Controller\Adminhtml\Package;

use Magento\Backend\App\Action;

/**
 * Class Index
 */
class Index extends Action
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('manifest_id');
        $this->_view->loadLayout();
        $this->_setActiveMenu('Mageviper_Core::mageviper_menu')
             ->_addBreadcrumb(__('Mageviper'), __('Dpd Courier Packages'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Packages from Manifest %1', $id));
        $this->_view->renderLayout();
    }
}
