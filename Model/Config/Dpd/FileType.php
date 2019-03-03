<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Model
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */
declare(strict_types=1);

namespace Mageviper\Dpd\Model\Config\Dpd;

use Magento\Framework\Option\ArrayInterface;

class FileType implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => '',
                'label' => __('-- Please Select --')
            ],
            [
                'value' => 'PDF',
                'label' => __('PDF')
            ],
            [
                'value' => 'ZPL',
                'label' => __('ZPL')
            ],
            [
                'value' => 'EPL',
                'label' => __('EPL')
            ],
        ];

        return $options;
    }
}