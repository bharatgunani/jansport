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

class Share extends \Magento\Framework\View\Element\Template
{
    protected $_objectManager = null;

	protected $_share = [
							'facebook',
							'twitter',
							'google_plusone_share' => 'Google+',
							'linkedin' => 'LinkedIn',
							'pinterest',
							'amazonwishlist' => 'Amazon',
							'vk' => 'Vkontakte',
							'odnoklassniki_ru' => 'Odnoklassniki',
							'mymailru' => 'Mail',
							'blogger',
							'delicious',
							'wordpress',
						];

    public function _construct()
    {
        parent::_construct();

        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function getHelper()
    {
        return $this->_objectManager->get('Plumrocket\SocialLoginFree\Helper\Data');
    }

    public function showPopup()
    {
        return $this->getHelper()->showPopup() && $this->getHelper()->shareEnabled();
    }

    public function getButtons()
    {
    	$buttons = [];

    	$url = urlencode($this->getPageUrl());
    	$title = urlencode($this->getTitle());

    	foreach ($this->_share as $key1 => $key2) {
    		$key = (!is_numeric($key1)) ? $key1 : $key2;
    		$name = ucfirst($key2);

    		$buttons[] = [
                'href' => "https://api.addthis.com/oexchange/0.8/forward/{$key}/offer?url={$url}&ct=1&pco=tbxnj-1.0",
    			// 'href' => "https://api.addthis.com/oexchange/0.8/forward/{$key}/offer?url={$url}&title={$title}&ct=1&pco=tbxnj-1.0",
    			'image' => "https://cache.addthiscdn.com/icons/v2/thumbs/32x32/{$key}.png",
    			'name' => $name,
    		];
    	}

    	return $buttons;
    }

    public function getPageUrl()
    {
    	$pageUrl = null;
    	$shareData = $this->getHelper()->getShareData();
    	
    	switch($shareData['page']) {

            case '__custom__':
                $pageUrl = $shareData['page_link'];
                if (!$this->getHelper()->isUrlInternal($pageUrl)) {
                    $pageUrl = $this->_objectManager->get('Magento\Store\Model\Store')->getBaseUrl() . $pageUrl;
                }
                break;

            case '__invitations__':
                if($this->getHelper()->moduleInvitationsEnabled()) {
                    $pageUrl = $this->_objectManager->get('Plumrocket\Invitations\Helper\Data')->getRefferalLink();
                }else{
                    $pageUrl = $this->_objectManager->get('Magento\Store\Model\Store')->getBaseUrl();
                }
            	break;

            default:
                if(is_numeric($shareData['page'])) {
                    $pageUrl = $this->_objectManager->get('Magento\Cms\Helper\Page')->getPageUrl($shareData['page']);
                }
        }

        // Disable addsis analytics anchor.
        $pageUrl .= '#';

        return $pageUrl;
    }

    public function getTitle()
    {
    	$shareData = $this->getHelper()->getShareData();
    	return $shareData['title'];
    }

    public function getDescription()
    {
        $process = $this->_objectManager->get('Magento\Cms\Model\Template\FilterProvider')->getPageFilter();
        $shareData = $this->getHelper()->getShareData();
    	return $process->filter($shareData['description']);
    }

}