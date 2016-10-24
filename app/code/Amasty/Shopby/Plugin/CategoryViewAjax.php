<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Shopby\Plugin;


class CategoryViewAjax
{
    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * CategoryViewAjax constructor.
     *
     * @param \Amasty\Shopby\Helper\Data                      $helper
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Amasty\Shopby\Helper\Data $helper,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->helper = $helper;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * @param \Magento\Catalog\Controller\Category\View $controller
     * @param                                           $page
     *
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\View\Result\Page
     */
    public function afterExecute(\Magento\Catalog\Controller\Category\View $controller,  $page)
    {
        /** @var \Magento\Framework\View\Result\Page $page */
        $isAjax = $controller->getRequest()->isAjax();
        //$isAjax = true;
        if(!$this->helper->isAjaxEnabled() || !$isAjax || !$page instanceof \Magento\Framework\View\Result\Page )
        {
            return $page;
        }

        $products = $page->getLayout()->getBlock('category.products');
        $navigation = $page->getLayout()->getBlock('catalog.leftnav');

        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode(['categoryProducts'=>$products->toHtml(), 'navigation' => $navigation->toHtml()]));
        return $response;

    }
}
