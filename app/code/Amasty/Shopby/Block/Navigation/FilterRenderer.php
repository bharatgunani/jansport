<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
namespace Amasty\Shopby\Block\Navigation;

use Amasty\Shopby\Api\Data\FilterSettingInterface;
use Amasty\Shopby\Helper\FilterSetting;
use Amasty\Shopby\Helper\UrlBuilder;
use Amasty\Shopby\Model\Layer\Filter\Item;
use Amasty\Shopby\Model\Source\DisplayMode;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;

class FilterRenderer extends \Magento\LayeredNavigation\Block\Navigation\FilterRenderer
{
    /** @var  FilterSetting */
    protected $settingHelper;

    /** @var  UrlBuilder */
    protected $urlBuilder;

    /** @var  FilterInterface */
    protected $filter;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, FilterSetting $settingHelper, UrlBuilder $urlBuilder, array $data = [])
    {
        parent::__construct($context, $data);
        $this->settingHelper = $settingHelper;
        $this->urlBuilder = $urlBuilder;
    }

    public function render(FilterInterface $filter)
    {
        $this->filter = $filter;
        $setting = $this->settingHelper->getSettingByLayerFilter($filter);
        $template = $this->getTemplateByFilterSetting($setting);
        $this->setTemplate($template);
        $this->assign('filterSetting', $setting);

        return parent::render($filter);
    }

    protected function getTemplateByFilterSetting(FilterSettingInterface $filterSetting)
    {
        switch($filterSetting->getDisplayMode()) {
            case DisplayMode::MODE_SLIDER:
                $template = "layer/filter/slider.phtml";
                break;
            case DisplayMode::MODE_DROPDOWN:
                $template = "layer/filter/dropdown.phtml";
                break;
            default:
                $template = "layer/filter/default.phtml";
                break;
        }
        return $template;
    }

    public function checkedFilter($filterItem)
    {
        $data = $this->getRequest()->getParam($filterItem->getFilter()->getRequestVar());
        if (!empty($data)) {
            $ids = explode(',', $data);
            if (in_array($filterItem->getValue(), $ids)) {
                return 1;
            }
        }
        return 0;
    }

    public function getClearUrl()
    {
        if (!array_key_exists('filterItems', $this->_viewVars) || !is_array($this->_viewVars['filterItems'])) {
            return '';
        }
        $items = $this->_viewVars['filterItems'];

        foreach ($items as $item) {
            /** @var Item $item */

            if ($this->checkedFilter($item)) {
                return $item->getRemoveUrl();
            }
        }

        return '';
    }

    public function getSliderUrlTemplate()
    {
        return $this->urlBuilder->buildUrl($this->filter, 'amshopby_slider_from-amshopby_slider_to');
    }
}
