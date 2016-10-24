<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
namespace Amasty\Shopby\Model\Layer\Filter;

use Amasty\Shopby\Model\Source\DisplayMode;

class Decimal extends \Magento\CatalogSearch\Model\Layer\Filter\Decimal
{
    protected $_fromTo;

    protected $settingHelper;

    protected $currencySymbol;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory $filterDecimalFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Amasty\Shopby\Helper\FilterSetting $settingHelper,
        array $data
    ) {
        $this->settingHelper = $settingHelper;
        $this->currencySymbol = $priceCurrency->getCurrencySymbol();
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $filterDecimalFactory, $priceCurrency, $data);
    }


    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $apply = parent::apply($request);
        $filter = $request->getParam($this->getRequestVar());
        if(!empty($filter) && !is_array($filter)) {
            list($from, $to) = explode('-', current(explode(',', $filter)));
            $this->_fromTo['from'] = $from;
            $this->_fromTo['to'] = $to;
        }

        return $apply;
    }


    public function getCurrentFrom()
    {
        return empty($this->_fromTo['from']) ? $this->getMinValue() : $this->_fromTo['from'];
    }

    public function getCurrentTo()
    {
        return empty($this->_fromTo['to']) ? $this->getMaxValue() : $this->_fromTo['to'];
    }

    public function setFromTo($from = null, $to = null)
    {
        $this->_fromTo['from'] = $from;
        $this->_fromTo['to'] = $to;
    }

    public function getMinValue()
    {
        $minMax = $this->getLayer()->getProductCollection()->getMinMaxValueByAttribute($this->getAttributeModel());
        return $minMax['min'];
    }

    public function getMaxValue()
    {
        $minMax = $this->getLayer()->getProductCollection()->getMinMaxValueByAttribute($this->getAttributeModel());
        return $minMax['max'];
    }


    protected function _initItems()
    {
        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        if($filterSetting->getDisplayMode() != DisplayMode::MODE_SLIDER) {
            return parent::_initItems();
        }
        if(!$this->getMinValue()) {
            return [];
        }

        $this->_items = [
            [
                'from'          => $this->getCurrentFrom(),
                'to'            => $this->getCurrentTo(),
                'min'           => $this->getMinValue(),
                'max'           => $this->getMaxValue(),
                'requestVar'    => $this->getRequestVar(),
                'step'          => round($filterSetting->getSliderStep(), 4),
                'template'      => !$filterSetting->getUnitsLabelUseCurrencySymbol() ? '{amount} '.$filterSetting->getUnitsLabel() : $this->currencySymbol . '{amount}'
            ]
        ];
        return $this;
    }

    protected function renderRangeLabel($fromPrice, $toPrice)
    {
        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        if($filterSetting->getUnitsLabelUseCurrencySymbol()) {
            return parent::renderRangeLabel($fromPrice, $toPrice);
        }
        $formattedFromPrice = round($fromPrice, 4).' '.$filterSetting->getUnitsLabel();
        if ($toPrice === '') {
            return __('%1 and above', $formattedFromPrice);
        } else {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }
            return __('%1 - %2', $formattedFromPrice, round($toPrice, 4).' '.$filterSetting->getUnitsLabel());
        }
    }
}