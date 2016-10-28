<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Storelocator
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Storelocator\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magestore\Storelocator\Setup\InstallSchema as StorelocatorShema;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Pdfinvoiceplus
 * @module   Pdfinvoiceplus
 * @author   Magestore Developer
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->changeColumnImage($setup);
        }

        $installer->endSetup();
    }

    /**
     *
     * rename column storelocator_id in table magestore_storelocator_image to locator_id
     *
     * @param SchemaSetupInterface $setup
     */
    public function changeColumnImage(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->dropForeignKey(
            $setup->getTable(StorelocatorShema::SCHEMA_IMAGE),
            $setup->getFkName(
                StorelocatorShema::SCHEMA_IMAGE,
                'storelocator_id',
                StorelocatorShema::SCHEMA_STORE,
                'storelocator_id'
            )
        );

        $setup->getConnection()->dropIndex(
            $setup->getTable(StorelocatorShema::SCHEMA_IMAGE),
            $setup->getIdxName(
                $setup->getTable(StorelocatorShema::SCHEMA_IMAGE),
                ['storelocator_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            )
        );

        $setup->getConnection()->changeColumn(
            $setup->getTable(StorelocatorShema::SCHEMA_IMAGE),
            'storelocator_id',
            'locator_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => null,
                'comment' => 'Storelocator Id',
                'unsigned' => true
            ]
        );

        $setup->getConnection()->addIndex(
            $setup->getTable(StorelocatorShema::SCHEMA_IMAGE),
            $setup->getIdxName(
                $setup->getTable(StorelocatorShema::SCHEMA_IMAGE),
                ['locator_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['locator_id'],
            AdapterInterface::INDEX_TYPE_INDEX
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                StorelocatorShema::SCHEMA_IMAGE,
                'locator_id',
                StorelocatorShema::SCHEMA_STORE,
                'storelocator_id'
            ),
            $setup->getTable(StorelocatorShema::SCHEMA_IMAGE),
            'locator_id',
            $setup->getTable(StorelocatorShema::SCHEMA_STORE),
            'storelocator_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

    }
}