<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin;


use Amasty\Shopby\Model\FilterSetting;
use Amasty\Shopby\Model\FilterSettingFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class AttributePlugin
{
    /** @var  FilterSetting */
    protected $_setting;

    public function __construct(FilterSettingFactory $settingFactory)
    {
        $this->_setting = $settingFactory->create();
    }

    public function aroundSave(Attribute $subject, \Closure $proceed)
    {
        if (!$subject->hasData('filter_code')) {
            return $proceed();
        }

        $filterCode = 'attr_' . $subject->getAttributeCode();
        $this->_setting->load($filterCode, 'filter_code');
        $this->_setting->addData($subject->getData());
        $filterCode = $this->_setting->getFilterCode();
        if(empty($filterCode)) {
            $this->_setting->setFilterCode($filterCode);
        }

        $connection = $this->_setting->getResource()->getConnection();
        try {
            $connection->beginTransaction();
            $this->_setting->save();
            $result = $proceed();
            $connection->commit();
        } catch(\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return $result;
    }
}
