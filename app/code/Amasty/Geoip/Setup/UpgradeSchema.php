<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */


namespace Amasty\Geoip\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addIndexes($setup);
        }

        $setup->endSetup();
    }

    protected function addIndexes(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addIndex(
            $setup->getTable('amasty_geoip_block'),
            $setup->getConnection()->getIndexName(
                $setup->getTable('amasty_geoip_block'),
                'start_ip_num',
                'index'
            ),
            'start_ip_num'
        );

        $setup->getConnection()->addIndex(
            $setup->getTable('amasty_geoip_block'),
            $setup->getConnection()->getIndexName(
                $setup->getTable('amasty_geoip_block'),
                'end_ip_num',
                'index'
            ),
            'end_ip_num'
        );

        $setup->getConnection()->addIndex(
            $setup->getTable('amasty_geoip_location'),
            $setup->getConnection()->getIndexName(
                $setup->getTable('amasty_geoip_location'),
                'geoip_loc_id',
                'index'
            ),
            'geoip_loc_id'
        );
    }
}

