<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
namespace Amasty\Shopby\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addPriceSliderColumnsToFilterSettings($setup);
        }

        if (version_compare($context->getVersion(), '1.2.2.1', '<')) {
            $this->addIndexModeColumnsToFilterSettings($setup);
        }

        if (version_compare($context->getVersion(), '1.3.1', '<')) {
            $this->addHideOneOptionColumnToFilterSettings($setup);
        }

        $setup->endSetup();
    }

    private function addPriceSliderColumnsToFilterSettings(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'slider_step',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '1.00',
                'comment' => 'Slider Step'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'units_label_use_currency_symbol',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => true,
                'comment' => 'is Units label used currency symbol'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'units_label',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => false,
                'default' => '',
                'comment' => 'Units label'
            ]
        );
    }

    private function addIndexModeColumnsToFilterSettings(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'index_mode',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Robots Index Mode'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'follow_mode',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Robots Follow Mode'
            ]
        );
    }

    private function addHideOneOptionColumnToFilterSettings(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'hide_one_option',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Hide filter when only one option available'
            ]
        );
    }
}
