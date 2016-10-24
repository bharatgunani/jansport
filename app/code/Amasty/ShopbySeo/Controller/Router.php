<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\ShopbySeo\Controller;


use Amasty\ShopbySeo\Helper\Url;
use Amasty\ShopbySeo\Helper\UrlParser;

class Router implements \Magento\Framework\App\RouterInterface
{
    /** @var \Magento\Framework\App\ActionFactory */
    protected $actionFactory;

    /** @var \Magento\Framework\App\ResponseInterface */
    protected $_response;

    /** @var  Url */
    protected $urlHelper;

    /** @var  \Magento\Framework\Registry */
    protected $registry;

    /** @var  UrlParser */
    protected $urlParser;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Registry $registry,
        UrlParser $urlParser,
        Url $urlHelper
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
        $this->registry = $registry;
        $this->urlHelper = $urlHelper;
        $this->urlParser = $urlParser;
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->urlHelper->isSeoUrlEnabled()) {
            return;
        }

        $identifier = trim($request->getPathInfo(), '/');
        if (!preg_match('@^(.*)/([^/]+)@', $identifier, $matches))
            return;

        $seoPart = $this->urlHelper->removeCategorySuffix($matches[2]);
        $category = ($seoPart == $matches[2]) ? $matches[1] : $this->urlHelper->addCategorySuffix($matches[1]);

        $params = $this->urlParser->parseSeoPart($seoPart);
        if ($params === false) {
            return;
        }

        $this->registry->register('amasty_shopby_seo_parsed_params', $params);

        $params = array_merge($params, $request->getParams());
        $request->setParams($params);

        $request->setPathInfo($category);

        /*
         * We have match and now we will forward action
         */
        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }

}
