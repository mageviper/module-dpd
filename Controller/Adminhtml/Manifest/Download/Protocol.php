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

namespace Mageviper\Dpd\Controller\Adminhtml\Manifest\Download;

use Magento\Framework\Phrase;
use Mageviper\Dpd\Controller\Adminhtml\DownloadPdf;

/**
 * Class DownloadProtocol
 */
class Protocol extends DownloadPdf
{

    /**
     * @param $id
     * @return Phrase
     */
    protected function fileName($id): Phrase
    {
        return __('dpd-protocol-%1.pdf', $id);
    }

    /**
     * @return Phrase
     */
    protected function message(): Phrase
    {
        return __('Label generated successfully');
    }

    /**
     * @param array $packagesIds
     * @return mixed|string
     */
    protected function getContent(array $packagesIds)
    {
        return $this->dpdService->generateProtocolFromParcelsId($packagesIds);
    }
}
