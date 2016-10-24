<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Helper;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\App\Helper\Context;
use Amasty\Shopby;
use Amasty\Shopby\Model\ResourceModel\FilterSetting\Collection;
use Amasty\Shopby\Model\ResourceModel\FilterSetting\CollectionFactory;

class FilterSetting extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @var  Collection */
    protected $collection;

    /** @var  Shopby\Model\FilterSettingFactory */
    protected $settingFactory;

    public function __construct(Context $context, CollectionFactory $settingCollectionFactory, Shopby\Model\FilterSettingFactory $settingFactory)
    {
        parent::__construct($context);
        $this->collection = $settingCollectionFactory->create();
        $this->settingFactory = $settingFactory;
    }

    /**
     * @param FilterInterface $layerFilter
     * @return Shopby\Api\Data\FilterSettingInterface
     */
    public function getSettingByLayerFilter(FilterInterface $layerFilter)
    {
        $filterCode = $this->getFilterCode($layerFilter);
        $setting = null;
        if (isset($filterCode)) {
            $setting = $this->collection->getItemByColumnValue(Shopby\Model\FilterSetting::FILTER_CODE, $filterCode);
        }
        if (is_null($setting)) {
            $setting = $this->settingFactory->create();
        }
        return $setting;
    }

    private function getFilterCode(FilterInterface $layerFilter)
    {
        try
        {
            // Produces exception when attribute model missing
            $attribute = $layerFilter->getAttributeModel();
            return 'attr_' . $attribute->getAttributeCode();
        } catch (\Exception $exception)
        {
            // Put here cases for special filters like Category, Stock etc.
            ;
        }

        return null;
    }
}
