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

namespace Magestore\Storelocator\Controller\Index;

/**
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class View extends \Magestore\Storelocator\Controller\Index
{
    /**
     * Execute action.
     */
    public function execute()
    {
        if (!$this->_systemConfig->isEnableFrontend()) {
            return $this->_getResultRedirectNoroute();
        }

        $storelocatorId = $this->getRequest()->getParam('storelocator_id');

        /** @var \Magestore\Storelocator\Model\Store $store */
        $store = $this->_objectManager->create('Magestore\Storelocator\Model\Store')->load($storelocatorId);

        if (!$store->getId() || !$store->isEnabled()) {
            return $this->_getResultRedirectNoroute();
        }

        /*
         * load base image of store
         */

        $this->_coreRegistry->register('storelocator_store', $store);

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_initResultPage();
        $resultPage->getConfig()->getTitle()->set($store->getMetaTitle());
        $resultPage->getConfig()->setDescription($store->getMetaDescription());
        $resultPage->getConfig()->setKeywords($store->getMetaKeywords());

        return $resultPage;
    }
}
