<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
namespace Amasty\Shopby\Model\ResourceModel\Fulltext;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Phrase;
use Magento\Framework\Search\Response\QueryResponse;

/**
 * Fulltext Collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /** @var  QueryResponse */
    protected $queryResponse;

    /**
     * Catalog search data
     *
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory = null;

    /**
     * Catalog search fulltext
     *
     * @var \Magento\CatalogSearch\Model\Fulltext
     */
    protected $_catalogSearchFulltext;

    /**
     * @var \Amasty\Shopby\Model\Request\Builder
     */
    private $requestBuilder;

    /**
     * @var \Magento\Search\Model\SearchEngine
     */
    private $searchEngine;

    /** @var string */
    private $queryText;

    /** @var string|null */
    private $order = null;

    /**
     * @var \Amasty\Shopby\Model\Request\Builder
     */
    public $_memRequestBuilder;

    protected $category_ids = [];

    protected $_minMaxValuesAttributes = [];

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Search\Model\QueryFactory $catalogSearchData
     * @param \Magento\CatalogSearch\Model\Fulltext $catalogSearchFulltext
     * @param \Amasty\Shopby\Model\Request\Builder $requestBuilder
     * @param \Magento\Search\Model\SearchEngine $searchEngine
     * @param \Zend_Db_Adapter_Abstract $connection
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Search\Model\QueryFactory $catalogSearchData,
        \Magento\CatalogSearch\Model\Fulltext $catalogSearchFulltext,
        \Amasty\Shopby\Model\Request\Builder $requestBuilder,
        \Magento\Search\Model\SearchEngine $searchEngine,
        $connection = null
    ) {
        $this->_catalogSearchFulltext = $catalogSearchFulltext;
        $this->queryFactory = $catalogSearchData;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection
        );
        $this->requestBuilder = $requestBuilder;
        $this->searchEngine = $searchEngine;
    }

    /**
     * Apply attribute filter to facet collection
     *
     * @param string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($this->queryResponse !== null) {
            throw new \RuntimeException('Illegal state');
        }
        if (!is_array($condition) || (!in_array(key($condition), ['from', 'to'], true) && $field != 'visibility')) {
            $this->requestBuilder->bind($field, $condition);
        } else {
            if (!empty($condition['from'])) {
                $this->requestBuilder->bind("{$field}.from", $condition['from']);
            }
            if (!empty($condition['to'])) {
                $this->requestBuilder->bind("{$field}.to", $condition['to']);
            }
        }
        return $this;
    }

    /**
     * Add search query filter
     *
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->queryText = trim($this->queryText . ' ' . $query);
        return $this;
    }

    /**
     * @inheritdoc
     */

    public function setRequestData($builder){
        $this->_select->reset();
        $this->requestBuilder = $builder;
        $this->queryResponse = null;
        $this->_isFiltersRendered = false;

    }
    protected function _renderFiltersBefore()
    {
        $this->requestBuilder->bindDimension('scope', $this->getStoreId());
        if ($this->queryText) {
            $this->requestBuilder->bind('search_term', $this->queryText);
        }

        $priceRangeCalculation = $this->_scopeConfig->getValue(
            \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($priceRangeCalculation) {
            $this->requestBuilder->bind('price_dynamic_algorithm', $priceRangeCalculation);
        }

        $this->requestBuilder->autoSetRequestName();
        $this->_memRequestBuilder = clone $this->requestBuilder;
        $queryRequest = $this->requestBuilder->create();

        $this->queryResponse = $this->searchEngine->search($queryRequest);
        $ids = [0];
        /** @var \Magento\Framework\Search\Document $document */
        foreach ($this->queryResponse as $document) {
            $ids[] = $document->getId();
        }
        parent::addFieldToFilter('entity_id', ['in' => $ids]);
        $this->_totalRecords = count($ids) - 1;

        if ($this->order && $this->order['field'] == 'relevance') {
            $this->getSelect()->order(
                new \Zend_Db_Expr(
                    $this->_conn->quoteInto(
                        'FIELD(e.entity_id, ?) ' . $this->order['dir'],
                        $ids
                    )
                )
            );
        }
    }

    /**
     * @return $this
     */
    protected function _renderFilters()
    {
        $this->_filters = [];
        return parent::_renderFilters();
    }

    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = Select::SQL_DESC)
    {
        $this->order = ['field' => $attribute, 'dir' => $dir];
        if ($attribute != 'relevance') {
            parent::setOrder($attribute, $dir);
        }
        return $this;
    }

    /**
     * Stub method for compatibility with other search engines
     *
     * @return $this
     */
    public function setGeneralDefaultQuery()
    {
        return $this;
    }

    /**
     * Return field faceted data from faceted search result
     *
     * @param string $field
     * @return array
     * @throws StateException
     */
    public function getFacetedData($field)
    {
        $this->_renderFilters();
        $aggregations = $this->queryResponse->getAggregations();
        $bucket = $aggregations->getBucket($field . '_bucket');
        if (!$bucket) {
            throw new StateException(new Phrase('Bucket does not exist'));
        }

        $result = [];
        foreach ($bucket->getValues() as $value) {
            $metrics = $value->getMetrics();
            $result[$metrics['value']] = $metrics;
        }
        return $result;
    }

    /**
     * Specify category filter for product collection
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return $this
     */
    public function addCategoryFilter(\Magento\Catalog\Model\Category $category,$key=false)
    {
        // code for multiselect categoru filter
        $this->category_ids[] = $category->getId();
        $this->addFieldToFilter('category_ids', $category->getId());
        return parent::addCategoryFilter($category);
    }

    /**
     * Set product visibility filter for enabled products
     *
     * @param array $visibility
     * @return $this
     */
    public function setVisibility($visibility)
    {
        $this->addFieldToFilter('visibility', $visibility);
        return parent::setVisibility($visibility);
    }

    public function getMinMaxValueByAttribute(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute)
    {
        if(!isset($this->_minMaxValuesAttributes[$attribute->getAttributeCode()])) {
            if(
                $this->_memRequestBuilder->hasPlaceholder($attribute->getAttributeCode().".from") ||
                $this->_memRequestBuilder->hasPlaceholder($attribute->getAttributeCode().".to")
            ){
                /**
                 * @var $requestBuilder \Amasty\Shopby\Model\Request\Builder
                 */
                $requestBuilder = clone $this->_memRequestBuilder;
                $requestBuilder->removePlaceholder($attribute->getAttributeCode().".from");
                $requestBuilder->removePlaceholder($attribute->getAttributeCode().".to");

                $queryRequest = $requestBuilder->create();

                $queryResponse = $this->searchEngine->search($queryRequest);
                $productIds = [0];
                /** @var \Magento\Framework\Search\Document $document */
                foreach ($queryResponse as $document) {
                    $productIds[] = $document->getId();
                }
            } else {
                $productIds = $this->getAllIds();
            }


            $select = clone $this->getSelect();
            $storeId = $this->_storeManager->getStore()->getId();
            /**
             * TODO: When attribute value for store not equals default value (for all stores), then min and max values incorrect
             */

            $select->reset()->from(
                $attribute->getBackend()->getTable(),
                [
                    'min' => 'ROUND(MIN(value))',
                    'max' => 'ROUND(MAX(value))',
                ]
            )->where(
                'attribute_id = ?',
                (int)$attribute->getId()
            )->where(
                'store_id = ? OR store_id = 0',
                $storeId
            )->where(
                'entity_id IN(?)',
                $productIds
            );

            $this->_minMaxValuesAttributes[$attribute->getAttributeCode()] = $this->getConnection()->fetchRow($select);
        }

        return $this->_minMaxValuesAttributes[$attribute->getAttributeCode()];
    }

    protected function _prepareStatisticsData()
    {
        $requestBuilder = clone $this->_memRequestBuilder;
        $requestBuilder->removePlaceholder("price.from");
        $requestBuilder->removePlaceholder("price.to");
        $this->requestBuilder = $requestBuilder;
        $this->queryResponse = null;
        $this->_isFiltersRendered = false;
        $where = $this->getSelect()->getPart(\Zend_Db_Select::WHERE);
        foreach($where as $whereId=>$wherePart) {
            if(strpos($wherePart, 'entity_id')!== false) {
                if($whereId == 0) {
                    $where[$whereId] = '1';
                } else {
                    unset($where[$whereId]);
                }

            }
        }
        $this->getSelect()->setPart(\Zend_Db_Select::WHERE, $where);
        $this->_renderFilters();
        parent::_prepareStatisticsData();
    }

}
