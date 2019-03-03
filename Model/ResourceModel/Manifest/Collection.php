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

namespace Mageviper\Dpd\Model\ResourceModel\Manifest;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageviper\Dpd\Model;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Standard resource collection initialization
     */
    protected function _construct()
    {
        $this->_init(Model\Manifest::class, Model\ResourceModel\Manifest::class);
    }
}
