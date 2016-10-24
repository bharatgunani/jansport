<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

/**
 * Created by PhpStorm.
 * User: yuri
 * Date: 23/12/15
 * Time: 15:21
 */

namespace Amasty\Shopby\Model\Source;


class DisplayMode implements \Magento\Framework\Option\ArrayInterface
{
    const MODE_DEFAULT = 0;
    const MODE_DROPDOWN = 1;
    const MODE_SLIDER  = 2;


    const ATTRUBUTE_DEFAULT = 'default';
    const ATTRUBUTE_DECIMAL = 'decimal';

    protected $attributeType = self::ATTRUBUTE_DEFAULT;



    public function setAttributeType($attributeType)
    {
        $this->attributeType = $attributeType;
        return $this;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->_getOptions() as $optionValue=>$optionLabel) {
            $options[] = ['value'=>$optionValue, 'label'=>$optionLabel];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_getOptions();
    }

    protected function _getOptions()
    {
        $options = [
            self::MODE_DEFAULT => __('Default'),
            self::MODE_DROPDOWN => __('Dropdown')
        ];

        switch($this->attributeType) {
            case self::ATTRUBUTE_DECIMAL:
                $options[self::MODE_SLIDER] = __('Slider');
                break;
            default:
                break;
        }

        return $options;
    }
}
