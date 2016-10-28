<?php
/**
 * Copyright © 2015 CommerceExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CommerceExtensions\ProductImportExport\Controller\Adminhtml\Data;

use Magento\Framework\Controller\ResultFactory;

class ImportExport extends \CommerceExtensions\ProductImportExport\Controller\Adminhtml\Data
{
    /**
     * Import and export Page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('CommerceExtensions_ProductImportExport::system_convert_product');
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock('CommerceExtensions\ProductImportExport\Block\Adminhtml\Data\ImportExportHeader')
        );
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock('CommerceExtensions\ProductImportExport\Block\Adminhtml\Data\ImportExport')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Products'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import and Export Products'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CommerceExtensions_ProductImportExport::import_export');
    }
}
