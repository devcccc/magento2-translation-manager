<?php

namespace CCCC\TranslationManager\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Function install
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        //START table setup
        //        $table = $installer->getConnection()->newTable(
        //            $installer->getTable('kavinga')
        //        )->addColumn(
        //            'kid',
        //            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        //            null,
        //            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
        //            'Entity ID'
        //        )->addColumn(
        //            'title',
        //            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        //            255,
        //            ['nullable' => false,],
        //            'Demo Title'
        //        )->addColumn(
        //            'creation_time',
        //            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        //            null,
        //            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,],
        //            'Creation Time'
        //        )->addColumn(
        //            'update_time',
        //            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        //            null,
        //            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,],
        //            'Modification Time'
        //        )->addColumn(
        //            'is_active',
        //            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        //            null,
        //            ['nullable' => false, 'default' => '1',],
        //            'Is Active'
        //        );
        //        $installer->getConnection()->createTable($table);
        //        //END   table setup
    }
}
