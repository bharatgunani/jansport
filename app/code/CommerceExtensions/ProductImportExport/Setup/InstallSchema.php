<?php 

namespace CommerceExtensions\ProductImportExport\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('productimportexport_cronjobdata'))
            ->addColumn(
                'post_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Post ID'
            )
			->addColumn('url_key', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('root_catalog_id', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('update_products_only', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('import_images_by_url', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('reimport_images', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('deleteall_andreimport_images', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('append_tier_prices', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('append_categories', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('import_rates_file', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('import_delimiter', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('import_enclose', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('export_delimiter', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('export_enclose', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('Schedule', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('Profile_type', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('product_id_from', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('product_id_to', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('export_grouped_position', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('export_related_position', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('export_crossell_position', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('export_upsell_position', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('export_category_paths', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('export_full_image_paths', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('File_name', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('File_path', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('Export_File_Location', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Blog Title')
            ->addColumn('content', Table::TYPE_TEXT, '2M', [], 'Blog Content')
            ->addColumn('is_active', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Is Post Active?')
            ->addColumn('creation_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Creation Time')
            ->addColumn('update_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Update Time')
            ->addIndex($installer->getIdxName('blog_post', ['url_key']), ['url_key'])
            ->setComment('CommerceExtensions Product ImportExport');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

}