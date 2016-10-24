<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
namespace Amasty\Shopby\Model\Layer\Filter;

use Magento\Framework\Escaper;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;
use Magento\Catalog\Model\Layer\Filter\ItemFactory as ItmFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer;

/**
 * Layer category filter
 */
class Category extends \Magento\CatalogSearch\Model\Layer\Filter\Category
{

    /**
     * Redeclare because of private in parent
     * @var Escaper
     */
    protected $escaper;

    /**
     * Redeclare because of private in parent
     * @var CategoryDataProvider
     */
    protected $dataProvider;

    public function __construct(
        ItmFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        Layer\Filter\Item\DataBuilder $itemDataBuilder,
        Escaper $escaper,
        Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $escaper,
            $categoryDataProviderFactory,
            $data
        );
        $this->escaper = $escaper;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $categoryId = $request->getParam($this->_requestVar) ?: $request->getParam('id');
        if (empty($categoryId)) {
            return $this;
        }
        $categories = explode(',',$categoryId);
        $first = true;
        foreach ($categories as $catId) {
            if(empty($catId)) continue;
            $this->dataProvider->setCategoryId($catId);

            $category = $this->dataProvider->getCategory();
            if($first){
                $this->getLayer()->getProductCollection()->addCategoryFilter($category);
                $first = false;
            }else{
                $this->getLayer()->getProductCollection()->addCategoryFilter($category,true);
            }


            if ($request->getParam('id') != $category->getId() && $this->dataProvider->isValid()) {
                $this->getLayer()->getState()->addFilter($this->_createItem($category->getName(), $catId));
            }
        }

        return $this;
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */

    protected function _getItemsData()
    {
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $baseCategory = $this->dataProvider->getCategory();
        $collection = clone $productCollection;
        $requestBuilder = clone $productCollection->_memRequestBuilder;
        $requestBuilder->bind('category_ids',$this->getLayer()->getCurrentCategory()->getId());

        $collection->setRequestData($requestBuilder);
        $collection->clear();
        $collection->loadWithFilter();

        $optionsFacetedData = $collection->getFacetedData('category');
        $this->dataProvider->setCategoryId($this->getLayer()->getCurrentCategory()->getId());
        $category = $this->dataProvider->getCategory();

        $categories = $category->getChildrenCategories();
        $this->dataProvider->setCategoryId($baseCategory->getId());
        $collectionSize = $collection->getSize();

        if ($category->getIsActive()) {
            foreach ($categories as $category) {
                if($category->getIsActive() && isset($optionsFacetedData[$category->getId()])){
                    $this->itemDataBuilder->addItemData(
                        $this->escaper->escapeHtml($category->getName()),
                        $category->getId(),
                        $optionsFacetedData[$category->getId()]['count']
                    );
                }
            }
        }
        return $this->itemDataBuilder->build();
    }
}
