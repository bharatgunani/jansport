<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
namespace Amasty\Shopby\Model\Layer\Filter;

use Amasty\Shopby\Helper\FilterSetting;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\Exception\LocalizedException;

/**
 * Layer attribute filter
 */
class Attribute extends AbstractFilter
{
    /**
     * @var \Magento\Framework\Filter\StripTags
     */
    private $tagFilter;

    /** @var  FilterSetting */
    protected $settingHelper;
    /**
     * @var \Amasty\Shopby\Api\Data\FilterSettingInterface
     */
    private $filterSetting;

    public $attributeValue = null;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        array $data = [],
        FilterSetting $settingHelper
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->tagFilter = $tagFilter;
        $this->settingHelper = $settingHelper;
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);
        if (empty($attributeValue)) {
            return $this;
        }
        $this->attributeValue = $attributeValue;
        $values = explode(',',$attributeValue);
        if (!$this->isMultiselectAllowed() && count($values) > 1) {
            throw new LocalizedException(__('Layer Filter applied with multiple parameters, but multiselect restricted for the filter.'));
        }
        $attribute = $this->getAttributeModel();
        /** @var \Amasty\Shopby\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();
        $collectionValue = count($values) > 1 ? $values : $values[0];
        $productCollection->addFieldToFilter($attribute->getAttributeCode(), $collectionValue);

        foreach($values as $value){
            $label = $this->getOptionText($value);
            $this->getLayer()
                ->getState()
                ->addFilter($this->_createItem($label, $value));
        }

        if (!$this->isVisibleWhenSelected()) {
            $this->_items = [];
        }
        return $this;
    }

    private function isMultiselectAllowed()
    {
        return $this->getFilterSetting()->isMultiselect();
    }

    private function isVisibleWhenSelected()
    {
        return $this->isMultiselectAllowed() || $this->isDropdown();
    }

    private function isDropdown()
    {
        return $this->getFilterSetting()->getDisplayMode() == \Amasty\Shopby\Model\Source\DisplayMode::MODE_DROPDOWN;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollectionOrigin = $this->getLayer()
            ->getProductCollection();

        if($this->attributeValue){
            $productCollection = clone $productCollectionOrigin;
            $requestBuilder = clone $productCollection->_memRequestBuilder;
            $requestBuilder->removePlaceholder($attribute->getAttributeCode());
            $productCollection->setRequestData($requestBuilder);
            $productCollection->clear();
            $productCollection->loadWithFilter();
            $collection = $productCollection;
        }else{
            $collection = $productCollectionOrigin;
        }
        $optionsFacetedData = $collection->getFacetedData($attribute->getAttributeCode());

        $options = $attribute->getFrontend()
            ->getSelectOptions();
        foreach ($options as $option) {
            if (empty($option['value'])) {
                continue;
            }
            if(isset($optionsFacetedData[$option['value']])){
                $this->itemDataBuilder->addItemData(
                    $this->tagFilter->filter($option['label']),
                    $option['value'],
                    $optionsFacetedData[$option['value']]['count']
                );
            }

        }

        $itemsData = $this->itemDataBuilder->build();

        $setting = $this->settingHelper->getSettingByLayerFilter($this);
        if ($setting->getHideOneOption()) {
            if (count($itemsData) == 1) {
                $itemsData = [];
            }
        }

        return $itemsData;
    }

    /**
     * @return \Amasty\Shopby\Api\Data\FilterSettingInterface
     */
    protected function getFilterSetting()
    {
        if(is_null($this->filterSetting)) {
            $this->filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        }
        return $this->filterSetting;
    }

}
