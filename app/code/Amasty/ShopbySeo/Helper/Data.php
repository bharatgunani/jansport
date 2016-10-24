<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\ShopbySeo\Helper;


use Amasty\Shopby\Api\Data\FilterSettingInterface;
use Magento\Catalog\Model\Layer;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Amasty\Shopby\Model\ResourceModel\FilterSetting\CollectionFactory;

class Data extends AbstractHelper
{
    /** @var CollectionFactory */
    protected $settingCollectionFactory;
    /** @var Option\CollectionFactory */
    protected $optionCollectionFactory;

    /** @var  \Magento\Catalog\Model\Product\Url */
    protected $productUrl;

    protected $seoSignificantUrlParameters;
    protected $optionsSeoData;

    public function __construct(
        Context $context,
        CollectionFactory $settingCollectionFactory,
        Option\CollectionFactory $optionCollectionFactory,
        \Magento\Catalog\Model\Product\Url $productUrl
    )
    {
        parent::__construct($context);
        $this->settingCollectionFactory = $settingCollectionFactory;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->productUrl = $productUrl;
    }

    public function getSeoSignificantUrlParameters()
    {
        if (is_null($this->seoSignificantUrlParameters)) {
            $this->seoSignificantUrlParameters = $this->getSeoSignificantAttributeCodes();
        }
        return $this->seoSignificantUrlParameters;
    }

    public function getOptionsSeoData()
    {
        if (is_null($this->optionsSeoData)) {
            $seoAttributeCodes = $this->getSeoSignificantAttributeCodes();

            $collection = $this->optionCollectionFactory->create();
            $collection->join(['a' => 'eav_attribute'], 'a.attribute_id = main_table.attribute_id', ['attribute_code']);
            $collection->addFieldToFilter('attribute_code', ['in' => $seoAttributeCodes]);
            $collection->setStoreFilter();
            $select = $collection->getSelect();

            $statement = $select->query();
            $rows = $statement->fetchAll();
            $this->optionsSeoData = [];
            $aliasHash = [];
            foreach ($rows as $row) {
                $alias = $this->buildUniqueAlias($row['value'], $aliasHash);
                $optionId = $row['option_id'];
                $this->optionsSeoData[$optionId] = [
                    'alias' => $alias,
                    'attribute_code' => $row['attribute_code'],
                ];
                $aliasHash[$alias] = $optionId;
            }
        }
        return $this->optionsSeoData;
    }

    protected function getSeoSignificantAttributeCodes()
    {
        $collection = $this->settingCollectionFactory->create();
        $collection->addFieldToFilter(FilterSettingInterface::IS_SEO_SIGNIFICANT, 1);
        $filterCodes = $collection->getColumnValues(FilterSettingInterface::FILTER_CODE);
        array_walk($filterCodes, function (&$code) {
            if (substr($code, 0, 5) == 'attr_') {
                $code = substr($code, 5);
            }
        });
        return $filterCodes;
    }

    protected function buildUniqueAlias($value, $hash)
    {
        $format = $this->productUrl->formatUrlKey($value);
        $unique = $format;
        for ($i=1; array_key_exists($unique, $hash); $i++) {
            $unique = $format . '-' . $i;
        }
        return $unique;
    }
}
