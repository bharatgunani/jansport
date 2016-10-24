<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model;


use Amasty\Shopby\Api\Data\FilterSettingInterface;
use Amasty\Shopby\Model\Source\DisplayMode;
use Magento\Framework\DataObject\IdentityInterface;

class FilterSetting extends \Magento\Framework\Model\AbstractModel implements FilterSettingInterface, IdentityInterface
{
    const CACHE_TAG = 'amshopby_filter_setting';

    protected $_eventPrefix = 'amshopby_filter_setting';

    protected function _construct()
    {
        $this->_init('Amasty\Shopby\Model\ResourceModel\FilterSetting');
    }

    public function getId()
    {
        return $this->getData(self::FILTER_SETTING_ID);
    }

    public function getDisplayMode()
    {
        return $this->getData(self::DISPLAY_MODE);
    }

    public function getFilterCode()
    {
        return $this->getData(self::FILTER_CODE);
    }

    public function getFollowMode()
    {
        return $this->getData(self::FOLLOW_MODE);
    }

    public function getHideOneOption()
    {
        return $this->getData(self::HIDE_ONE_OPTION);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getIndexMode()
    {
        return $this->getData(self::INDEX_MODE);
    }

    public function getUnitsLabel($currencySymbol = '')
    {
        if($this->getUnitsLabelUseCurrencySymbol()) {
            return $currencySymbol;
        }
        return parent::getUnitsLabel();
    }

    public function isMultiselect()
    {
        return $this->getData(self::IS_MULTISELECT) && $this->isDisplayTypeAllowsMultiselect();
    }

    public function isSeoSignificant()
    {
        return $this->getData(self::IS_SEO_SIGNIFICANT);
    }

    public function setHideOneOption($hideOenOption)
    {
        return $this->setData(self::HIDE_ONE_OPTION, $hideOenOption);
    }

    public function setId($id)
    {
        return $this->setData(self::FILTER_SETTING_ID, $id);
    }

    public function setDisplayMode($displayMode)
    {
        return $this->setData(self::DISPLAY_MODE, $displayMode);
    }

    public function setFilterCode($filterCode)
    {
        return $this->setData(self::FILTER_CODE, $filterCode);
    }

    public function setIndexMode($indexMode)
    {
        return $this->setData(self::INDEX_MODE);
    }

    public function setFollowMode($followMode)
    {
        return $this->setData(self::FOLLOW_MODE);
    }

    public function setIsMultiselect($isMultiselect)
    {
        return $this->setData(self::FILTER_SETTING_ID, $isMultiselect);
    }

    public function setIsSeoSignificant($isSeoSignificant)
    {
        return $this->setData(self::IS_SEO_SIGNIFICANT, $isSeoSignificant);
    }

    protected function isDisplayTypeAllowsMultiselect()
    {
        return $this->getDisplayMode() == DisplayMode::MODE_DEFAULT;
    }
}
