<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Setup
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */
declare(strict_types=1);

namespace Mageviper\Dpd\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{
    const MV_DPD_MANIFEST = 'mv_dpd_manifest';
    const MV_DPD_PACKAGES = 'mv_dpd_packages';

    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->dpdManifestTable($setup);
        $this->dpdPackagesTable($setup);
        $this->dpdSalesOrderFlag($setup);
        $setup->endSetup();

    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    protected function dpdManifestTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(self::MV_DPD_MANIFEST)) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable(self::MV_DPD_MANIFEST)
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true
                ],
                'id'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT
                ], 'created at'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT_UPDATE
                ], 'updated at'
            )->addColumn(
                'send_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => true,
                ], 'Time closed manifest'
            )->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                255,
                [
                    'nullable' => true,
                    'default'  => 0
                ], 'Current process status'
            );
            $setup->getConnection()->createTable($table);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    protected function dpdPackagesTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(self::MV_DPD_PACKAGES)) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable(self::MV_DPD_PACKAGES)
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true
                ],
                'id'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ], 'order id'
            )->addColumn(
                'manifest_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ], 'manifest id'
            )->addColumn(
                'package_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true,
                ], 'Package id from DPD service'
            )->addColumn(
                'reference',
                Table::TYPE_TEXT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true,
                ], 'Package id from DPD service'
            )->addColumn(
                'location',
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                [
                    'nullable' => true,
                ], 'Package localisation'
            )->addColumn(
                'tracking_number',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default'  => null
                ], 'Tracking number from DPD service'
            );
        }
        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function dpdSalesOrderFlag(SchemaSetupInterface $setup): void
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'dpd_flag',
            [
                'type'     => Table::TYPE_BOOLEAN,
                'nullable' => true,
                'default'  => '0',
                'comment'  => 'Exported orders',
            ]
        );
    }
}
