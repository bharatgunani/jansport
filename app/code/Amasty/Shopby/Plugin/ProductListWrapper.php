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


class ProductListWrapper
{
    public function afterToHtml(\Magento\Catalog\Block\Product\ListProduct $subject, $result)
    {
        $loader = '<img src="' . $subject->getViewFileUrl('images/loader-1.gif') . '"
                 alt="' . __('Loading...') . '" style="top: 100px;left: 45%;display: block;position: absolute;">';
        $style  = '
        style="
            background-color: #FFFFFF;
            height: 100%;
            left: 0;
            opacity: 0.5;
            filter: alpha(opacity = 50);
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 555;
            display:none;
        "
        ';
        return '<div id="amasty-shopby-product-list">'.$result.'<div id="amasty-shopby-overlay" '.$style.'>' . $loader . '</div></div>';
    }
}
