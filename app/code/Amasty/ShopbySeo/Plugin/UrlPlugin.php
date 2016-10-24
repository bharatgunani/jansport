<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\ShopbySeo\Plugin;

use Amasty\ShopbySeo\Helper\Url;
use Magento\Framework\UrlInterface;

class UrlPlugin
{
    /** @var  Url */
    protected $helper;

    public function __construct(Url $helper)
    {
        $this->helper = $helper;
    }

    public function afterGetUrl(UrlInterface $subject, $native)
    {
        if ($this->helper->isSeoUrlEnabled()) {
            $result = $this->helper->seofyUrl($native);
            return $result;
        } else {
            return $native;
        }
    }
}
