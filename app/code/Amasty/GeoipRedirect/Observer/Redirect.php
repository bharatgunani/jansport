<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */


namespace Amasty\GeoipRedirect\Observer;

use Magento\Framework\Event\ObserverInterface;
use Amasty\GeoipRedirect\Model\Source\Logic;
use Magento\Store\Model\ScopeInterface;

class Redirect implements ObserverInterface
{
    protected $redirectAllowed = false;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Amasty\GeoipRedirect\Helper\Data
     */
    protected $geoipHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Amasty\Geoip\Model\Geolocation
     */
    protected $geolocation;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Api\StoreCookieManagerInterface
     */
    protected $storeCookieManager;


    public function __construct(
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\GeoipRedirect\Helper\Data $geoipHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Geoip\Model\Geolocation $geolocation,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Api\StoreCookieManagerInterface $storeCookieManager
    )
    {
        $this->remoteAddress = $remoteAddress;
        $this->scopeConfig = $scopeConfig;
        $this->geoipHelper = $geoipHelper;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->geolocation = $geolocation;
        $this->customerSession = $customerSession;
        $this->storeCookieManager = $storeCookieManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controller = $observer->getControllerAction();

        $ipRestriction = $this->scopeConfig->getValue('amgeoipredirect/restriction/ip_restriction');
        $currentIp = $this->remoteAddress->getRemoteAddress();
        if (!empty($ipRestriction)) {
            $ipRestriction = array_map("rtrim", explode(PHP_EOL, $ipRestriction));
            foreach ($ipRestriction as $ip) {
                if ($currentIp && $currentIp == $ip) {
                    return;
                }
            }
        }
        $isApi = $controller->getRequest()->getControllerModule() == 'Mage_Api';
        if ($isApi || !$this->geoipHelper->isModuleOutputEnabled('Amasty_Geoip')) {
            return;
        }
        $userAgent = $controller->getRequest()->getHeader('USER_AGENT');
        $userAgentsIgnore = $this->scopeConfig->getValue('amgeoipredirect/restriction/user_agents_ignore');
        if (!empty($userAgentsIgnore)) {
            $userAgentsIgnore = explode(',', $userAgentsIgnore);
            $userAgentsIgnore = array_map("trim", $userAgentsIgnore);
            foreach ($userAgentsIgnore as $agent) {
                if ($userAgent && $agent && stripos($userAgent, $agent) !== false) {
                    return;
                }
            }
        }

        $this->applyLogic($controller);
        $currentStoreId = $this->storeManager->getStore()->getId();
        $scopeStores = ScopeInterface::SCOPE_STORES;
        if ($this->scopeConfig->getValue('amgeoipredirect/general/enable', $scopeStores,  $currentStoreId)
            && $this->redirectAllowed) {
            $location = $this->geolocation->locate($currentIp);
            $country = $location->getCountry();

            $session = $this->customerSession;
            if ($this->scopeConfig->getValue('amgeoipredirect/restriction/first_visit_redirect')) {
                $getAmYetRedirectStore = $session->getAmYetRedirectStore();
                $getAmYetRedirectCurrency = $session->getAmYetRedirectCurrency();
                $getAmYetRedirectUrl = $session->getAmYetRedirectUrl();
            } else {
                $getAmYetRedirectStore = 0;
                $getAmYetRedirectCurrency = 0;
                $getAmYetRedirectUrl = 0;
            }

            if (!$getAmYetRedirectUrl && $this->scopeConfig->getValue('amgeoipredirect/country_url/enable_url')) {
                $urlMapping = unserialize($this->scopeConfig->getValue('amgeoipredirect/country_url/url_mapping', 'default', $currentStoreId));
                $currentUrl = $this->urlBuilder->getCurrentUrl();
                foreach ($urlMapping as $countries => $url) {
                    if (strpos($countries, $country) !== false && $url != $currentUrl) {
                        $session->setAmYetRedirectUrl(1);
                        $controller->getResponse()->setRedirect($url);
                        $controller->getResponse()->sendResponse();
                        exit;
                    }
                }
            }

            if (!$getAmYetRedirectStore && $this->scopeConfig->getValue('amgeoipredirect/country_store/enable_store')) {
                $allStores = $this->storeManager->getStores();
                foreach ($allStores as $store) {
                    $currentStoreUrl = str_replace('&amp;', '&', $store->getCurrentUrl(false));
                    $redirectStoreUrl = trim($currentStoreUrl, '/');
                    $countries = $this->scopeConfig->getValue('amgeoipredirect/country_store/affected_countries', $scopeStores, $store->getId());
                    if (!$this->scopeConfig->getValue('amgeoipredirect/restriction/redirect_between_websites')) {
                        $useMultistores = $store->getWebsiteId() == $this->storeManager->getStore()->getWebsiteId();
                    } else {
                        $useMultistores = true;
                    }

                    if ($country && $countries && strpos($countries, $country) !== false
                        && $store->getId() != $currentStoreId
                        && $useMultistores
                    ) {
                        $session->setAmYetRedirectStore(1);
                        $this->storeCookieManager->setStoreCookie($store);
                        $controller->getResponse()->setRedirect($redirectStoreUrl);
                        $controller->getResponse()->sendResponse();
                        exit;
                    }
                }
            }

            if (!$getAmYetRedirectCurrency && $this->scopeConfig->getValue('amgeoipredirect/country_currency/enable_currency')) {
                $currencyMapping = unserialize($this->scopeConfig->getValue('amgeoipredirect/country_currency/currency_mapping', $scopeStores, $currentStoreId));
                foreach ($currencyMapping as $countries => $currency) {
                    if (strpos($countries, $country) !== false && $this->storeManager->getStore()->getCurrentCurrencyCode() != $currency) {
                        $session->setAmYetRedirectCurrency(1);
                        $this->storeManager->getStore()->setCurrentCurrencyCode($currency);
                    }
                }
            }
        }
    }

    protected function applyLogic($controller)
    {
        $applyLogic = $this->scopeConfig->getValue('amgeoipredirect/restriction/apply_logic');
        $currentUrl = $this->urlBuilder->getCurrentUrl();
        $baseUrl = $this->storeManager->getStore()->getCurrentUrl();
        switch ($applyLogic) {
            case Logic::ALL_URLS :
                $this->redirectAllowed = true;
                $url = substr($currentUrl, strlen($baseUrl)-1);
                return $url;
                break;
            case Logic::SPECIFIED_URLS :
                $acceptedUrls = explode(PHP_EOL, $this->scopeConfig->getValue('amgeoipredirect/restriction/accepted_urls'));
                foreach ($acceptedUrls as $url) {
                    $url = trim($url);
                    if ($url && $currentUrl && strpos($currentUrl, $url) !== false) {
                        $this->redirectAllowed = true;
                        return $url;
                    }
                }
                break;
            case Logic::EXCEPT_URLS :
                $exceptedUrls = explode(PHP_EOL, $this->scopeConfig->getValue('amgeoipredirect/restriction/excepted_urls'));
                foreach ($exceptedUrls as $url) {
                    $url = trim($url);
                    if ($url && $currentUrl && strpos($currentUrl, $url) !== false) {
                        $this->redirectAllowed = false;
                        return $url;
                    } else {
                        $this->redirectAllowed = true;
                    }
                }
                break;
            case Logic::HOMEPAGE_ONLY :
                $routeName = $controller->getRequest()->getRouteName();
                $action = $controller->getRequest()->getActionName();
                if($routeName == 'cms' && $action == 'index') {
                    $this->redirectAllowed = true;
                }
                break;
        }
        return '';
    }
}
