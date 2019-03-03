<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Ui
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */
declare(strict_types=1);

namespace Mageviper\Dpd\Ui\Component\Listing\Column\Manifest;

use Magento\Ui\Component\Listing\Columns\Column;
use Mageviper\Dpd\Model\Manifest;

/**
 * Class Status
 */
class Status extends Column
{
    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                switch ($item[$this->getData('name')]) {
                    case Manifest::PREPARE_TO_APPLY:
                        $class = 'grid-severity-notice';
                        $text  = __('Waiting for Apply');
                        break;
                    case Manifest::APPLIED:
                        $class = 'grid-severity-notice';
                        $text  = __('Applied');
                        break;
                    case Manifest::ERROR:
                        $class = 'grid-severity-critical';
                        $text = __('Errors');
                        break;
                    case Manifest::QUEUE;
                        $class = 'grid-severity-minor';
                        $text  = __('Added to queued');
                        break;
                    case Manifest::PROCESSING;
                        $class = 'grid-severity-minor';
                        $text  = __('Processing');
                        break;
                    default:
                        $class = '';
                        $text  = '';
                        break;
                }
                $item['status_int'] = $item[$this->getData('name')];
                $item[$this->getData('name')] = '<span class="' . $class . '"><span>' . $text . '</span></span>';
            }
        }

        return $dataSource;
    }
}
