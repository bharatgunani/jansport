<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
namespace Amasty\Shopby\Model\Layer\Filter;

use Amasty\Shopby;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{
    protected $_request;

    /** @var  Shopby\Helper\FilterSetting */
    protected $filterSettingHelper;

    /** @var  Shopby\Helper\UrlBuilder */
    protected $urlBuilderHelper;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        Shopby\Helper\FilterSetting $filterSettingHelper,
        Shopby\Helper\UrlBuilder $urlBuilderHelper,
        array $data = []
    ) {
        $this->_request = $request;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->urlBuilderHelper = $urlBuilderHelper;
        parent::__construct($url,$htmlPagerBlock,$data);
    }
    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->urlBuilderHelper->buildUrl($this->getFilter(), $this->getValue());
    }


    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        return $this->urlBuilderHelper->buildUrl($this->getFilter(), $this->getValue());
    }

}
