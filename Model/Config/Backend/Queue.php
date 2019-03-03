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

namespace Mageviper\Dpd\Model\Config\Backend;

use Mageviper\Dpd\Model\Config\Source\Frequency;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Manifest
 */
class Queue extends \Magento\Framework\App\Config\Value
{
    const CRON_SCHEDULE_PATH = 'crontab/default/jobs/mageviper_dpd_queue_cron/schedule/cron_expr';
    const CONFIG             = 'manifest_queue';
    /**
     * @var ValueFactory
     */
    protected $configValueFactory;

    /**
     * Point constructor.
     * @param Context               $context
     * @param Registry              $registry
     * @param ScopeConfigInterface  $config
     * @param TypeListInterface     $cacheTypeList
     * @param ValueFactory          $configValueFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ValueFactory $configValueFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configValueFactory = $configValueFactory;
        parent::__construct($context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function afterSave()
    {
        $frequency = $this->getData('groups/mageviper_dpd/groups/dpd_cron/fields/frequency_queue/value');
        $time      = $this->getData('groups/mageviper_dpd/groups/dpd_cron/fields/time_queue/value');

        if ($frequency === Frequency::CRON_MINUTELY) {
            if (($time[1] === '00' || $time[1] === '01') && $time[0] === '00') {
                $time[1] = '*';
                $time[0] = '*';
            } else {
                $time[1] = '*/' . (int)$time[1];
                $time[0] = $time[0] !== '00' ? (int)$time[0] : '*';
            }
        }
        if ($frequency === Frequency::CRON_HOURLY) {
            if ($time[1] === '00' || $time[1] === '01') {
                $time[1] = '*';
            } else {
                $time[1] = '*/' . (int)$time[1];
            }
        }

        $cronExprArray = [
            $time[1],
            //Minute
            $time[0],
            //Hour
            $frequency == Frequency::CRON_MONTHLY ? '1' : '*',
            //Day of the Month
            '*',
            //Month of the Year
            $frequency == Frequency::CRON_WEEKLY ? '1' : '*',
            //Day of the Week
        ];

        $cronExprString = implode(' ', $cronExprArray);

        try {
            $this->configValueFactory->create()->load(
                self::CRON_SCHEDULE_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_SCHEDULE_PATH
            )->save();
        } catch (\Exception $e) {
            throw new \Exception('We can\'t save the cron expression.');
        }

        return parent::afterSave();
    }

    protected function getField(): string
    {
        return static::CONFIG;
    }
}
