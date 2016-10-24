<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */


namespace Amasty\GeoipRedirect\Model\Source;

class Logic implements \Magento\Framework\Option\ArrayInterface
{

    const ALL_URLS = 0;
    const SPECIFIED_URLS = 1;
    const EXCEPT_URLS = 2;
    const HOMEPAGE_ONLY = 3;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::ALL_URLS, 'label' => __('All URLs')),
            array('value' => self::SPECIFIED_URLS, 'label' => __('Specified URLs')),
            array('value' => self::EXCEPT_URLS, 'label' => __('All Except Specified URLs')),
            array('value' => self::HOMEPAGE_ONLY, 'label' => __('Redirect From Home Page Only'))
        );
    }

}
