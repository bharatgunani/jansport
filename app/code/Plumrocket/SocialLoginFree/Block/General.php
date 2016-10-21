<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_SocialLoginFree
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\SocialLoginFree\Block;

class General extends \Magento\Framework\View\Element\Template
{
	protected function _toHtml()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Plumrocket\SocialLoginFree\Helper\Data');
        if(!$helper->moduleEnabled()) {
            return;
        }

        $moduleName = $this->getRequest()->getModuleName();

        // Set current store.
        if($moduleName != 'pslogin') {
            $currentStoreId = $objectManager->get('Magento\Store\Model\StoreManager')->getStore()->getId();
            $helper->refererStore($currentStoreId);
        }

        // Set referer.
        if(!$objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $skipModules = $helper->getRefererLinkSkipModules();
            if($this->getRequest()->getActionName() != 'noRoute' && !in_array($moduleName, $skipModules)) {
                $referer = $objectManager->get('Magento\Framework\Url\Helper\Data')->getCurrentBase64Url();
                $helper->refererLink($referer);
            }
        }
        
        return parent::_toHtml();
    }
}