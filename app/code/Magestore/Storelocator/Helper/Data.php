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

namespace Magestore\Storelocator\Helper;

/**
 * Helper Data.
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter
     */
    protected $_converter;

    /**
     * @var \Magestore\Storelocator\Model\Factory
     */
    protected $_factory;

    /**
     * @var \Magestore\Storelocator\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var array
     */
    protected $_sessionData = null;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $_backendHelperJs;

    /**
     * Block constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magestore\Storelocator\Model\Factory $factory,
        \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter,
        \Magento\Backend\Helper\Js $backendHelperJs,
        \Magento\Backend\Model\Session $backendSession,
        \Magestore\Storelocator\Model\StoreFactory $storeFactory
    ) {
        parent::__construct($context);
        $this->_factory = $factory;
        $this->_converter = $converter;
        $this->_storeFactory = $storeFactory;
        $this->_backendHelperJs = $backendHelperJs;
        $this->_backendSession = $backendSession;
    }

    /**
     * get selected stores in serilaze grid store.
     *
     * @return array
     */
    public function getTreeSelectedStores()
    {
        $sessionData = $this->_getSessionData();

        if ($sessionData) {
            return $this->_converter->toTreeArray(
                $this->_backendHelperJs->decodeGridSerializedInput($sessionData)
            );
        }

        $entityType = $this->_getRequest()->getParam('entity_type');
        $id = $this->_getRequest()->getParam('enitity_id');

        /** @var \Magestore\Storelocator\Model\AbstractModelManageStores $model */
        $model = $this->_factory->create($entityType)->load($id);

        return $model->getId() ? $this->_converter->toTreeArray($model->getStorelocatorIds()) : [];
    }

    /**
     * get selected rows in serilaze grid of tag, holiday, specialday.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTreeSelectedValues()
    {
        $sessionData = $this->_getSessionData();

        if ($sessionData) {
            return $this->_converter->toTreeArray(
                $this->_backendHelperJs->decodeGridSerializedInput($sessionData)
            );
        }

        $storelocatorId = $this->_getRequest()->getParam('storelocator_id');
        $methodGetterId = $this->_getRequest()->getParam('method_getter_id');

        /** @var \Magestore\Storelocator\Model\Store $store */
        $store = $this->_storeFactory->create()->load($storelocatorId);
        $ids = $store->runGetterMethod($methodGetterId);

        return $store->getId() ? $this->_converter->toTreeArray($ids) : [];
    }

    /**
     * Get session data.
     *
     * @return array
     */
    protected function _getSessionData()
    {
        $serializedName = $this->_getRequest()->getParam('serialized_name');
        if ($this->_sessionData === null) {
            $this->_sessionData = $this->_backendSession->getData($serializedName, true);
        }

        return $this->_sessionData;
    }
}
