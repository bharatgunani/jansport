<?php
/**
 * Copyright © 2015 CommerceExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CommerceExtensions\ProductImportExport\Controller\Adminhtml\Data;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class ExportXml extends \CommerceExtensions\ProductImportExport\Controller\Adminhtml\Data
{
    /**
     * Export Data grid to XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $content = $resultLayout->getLayout()->getChildBlock('adminhtml.product.data.grid', 'grid.export');

        return $this->fileFactory->create(
            'export_products.xml',
            $content->getExcelFile(),
            DirectoryList::VAR_DIR
        );
    }
}
