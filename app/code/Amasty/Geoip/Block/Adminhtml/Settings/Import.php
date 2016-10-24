<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */

namespace Amasty\Geoip\Block\Adminhtml\Settings;

class Import extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $block = $this->getLayout()
            ->createBlock('Amasty\Geoip\Block\Adminhtml\Template')
            ->setTemplate('Amasty_Geoip::import.phtml')
        ;
        $this->setImportData($block);

        return $block->toHtml();

        return $button->toHtml();
    }

    public function setImportData($block) {
        $importFilesAvailable = false;

        $fileBlockPath = $block->geoipHelper->getFilePath('block', 'import');
        $fileLocationPath = $block->geoipHelper->getFilePath('location', 'import');

        $blockFileExist = false;
        $locationFileExist = false;
        if ($block->geoipHelper->isFileExist($fileBlockPath)) {
            $blockFileExist = true;
        }
        if ($block->geoipHelper->isFileExist($fileLocationPath)) {
            $locationFileExist = true;
        }

        if ($blockFileExist && $locationFileExist) {
            $importFilesAvailable = true;
        }

        $importDate = '';

        if ($block->geoipHelper->isDone() && $block->geoipHelper->importTableHasData()) {
            $width = 100;
            $importedClass = 'end_imported';
            if ($block->_scopeConfig->getValue('amgeoip/import/date')) {
                $importDate = __('Last Imported: ') . $block->_scopeConfig->getValue('amgeoip/import/date');
            }
        } else {
            $width = 0;
            $importedClass = 'end_not_imported';
        }
        $block
            ->setWidth($width)
            ->setImportFilesAvailable($importFilesAvailable)
            ->setBlockFileExist($blockFileExist)
            ->setLocationFileExist($locationFileExist)
            ->setImportedClass($importedClass)
            ->setImportDate($importDate)
        ;
    }
}
