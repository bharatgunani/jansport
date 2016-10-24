<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

namespace Amasty\Shopby\Helper;


use Amasty\Shopby\Model\Layer\Filter\Price;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;

class UrlBuilder extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Amasty\Shopby\Helper\FilterSetting
     */
    protected $filterSettingHelper;

    /** @var  Registry */
    protected $registry;

    protected $queryParamsResolver;

    public function __construct(
        Context $context,
        Registry $registry,
        \Amasty\Shopby\Helper\FilterSetting $filterSettingHelper,
        \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
    )
    {
        parent::__construct($context);
        $this->registry = $registry;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->queryParamsResolver = $queryParamsResolver;
    }


    public function buildUrl(\Magento\Catalog\Model\Layer\Filter\FilterInterface $filter, $value)
    {
        $result = [];
        $data = $this->_request->getParam($filter->getRequestVar());
        if ($filter instanceof Price && is_array($value)) {
            $value = implode('-', $value);
        }
        if(!empty($data)){
            $values = explode(',',$data);
            foreach($values as $key=>$val){
                if(empty($val)){
                    unset($values[$key]);
                }
            }
            $key = array_search($value, $values);

            if ($this->_isMultiselectAllowed($filter)) {
                if($key !== false) {
                    unset($values[$key]);
                    $result = $values;
                }else{
                    $result = $values;
                    $result[] = $value;
                }
            } else {
                if($key !== false) {
                    $result = [];
                } else {
                    $result[] = $value;
                }
            }
        } else {
            $result = [$value];
        }
        if(!empty($result)){
            $result = implode(',',$result);
        }else{
            $result = null;
        }

        $query = $this->registry->registry('amasty_shopby_seo_parsed_params');
        if (!is_array($query)) {
            $query = [];
        }
        $query[$filter->getRequestVar()] = $result;
        $query['isAjax'] = null;
        $query['_'] = null;

        return $this->_urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }

    private function _isMultiselectAllowed(\Magento\Catalog\Model\Layer\Filter\FilterInterface $filter)
    {
        $setting = $this->filterSettingHelper->getSettingByLayerFilter($filter);
        return $setting->isMultiselect();
    }
}
