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

use Magento\Framework\Data\OptionSourceInterface;
use Mageviper\Dpd\Model\Manifest;

/**
 * Class StatusType
 */
class StatusType implements OptionSourceInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Manifest::APPLIED,
                'label' => __('Applied')

            ],

            [
                'value' => Manifest::QUEUE,
                'label' => __('Added to Queue')

            ],
            [
                'value' => Manifest::PREPARE_TO_APPLY,
                'label' => __('Waiting for apply')

            ],
            [
                'value' => Manifest::PROCESSING,
                'label' => __('Processing')

            ],

        ];
    }
}
