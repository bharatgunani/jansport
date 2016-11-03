<?php
/**
 * Copyright © 2015 CommerceExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CommerceExtensions\ProductImportExport\Controller\Adminhtml\Data;

use Magento\Framework\Controller\ResultFactory;

class ImportPost extends \CommerceExtensions\ProductImportExport\Controller\Adminhtml\Data
{
    /**
     * import action from import/export data
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->isPost() && $this->_objectManager->create('Magento\MediaStorage\Model\File\Uploader', ['fileId' => 'import_rates_file'])) {
			try {
					$params = $this->getRequest()->getParams();
					$importHandler = $this->_objectManager->create('CommerceExtensions\ProductImportExport\Model\Data\CsvImportHandler');  
					#$readData = $importHandler->UploadCsvOfproduct($_FILES['import_rates_file']);
					$readData = $importHandler->UploadCsvOfproduct($this->getRequest()->getFiles('import_rates_file'));
					$filepath = $readData['path'].'/'.$readData['file'];
					$importHandler->readCsvFile($filepath, $params);
					
					$success = $this->messageManager->addSuccess(__('The Products have been imported Successfully.'));
					if($success){
						$this->reindexdata();
					}
				
				} catch (\Magento\Framework\Exception\LocalizedException $e) {
					$this->messageManager->addError($e->getMessage());
				} catch (\Exception $e) {
					$this->messageManager->addError(__('Invalid file upload attempt' . $e->getMessage()));
			}
        } else {
				$this->messageManager->addError(__('Invalid file upload attempt'));
        }
        // /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }
	public function reindexdata(){
		$Indexer = $this->_objectManager->create('Magento\Indexer\Model\Processor');
		$Indexer->reindexAll();
	}
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'CommerceExtensions_ProductImportExport::import_export'
        );

    }
}
