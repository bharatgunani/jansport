<?php
namespace Aitoc\ReviewBooster\Helper;

class Data extends \Magento\Framework\Url\Helper\Data
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var \Aitoc\ReviewBooster\Model\Review\Report
     */
    protected $_reportModel;

    /**
     * @var \Aitoc\ReviewBooster\Model\Review\Rate
     */
    protected $_rateModel;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Aitoc\ReviewBooster\Model\Review\Report $reportModel
     * @param \Aitoc\ReviewBooster\Model\Review\Rate $rateModel
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Backend\App\ConfigInterface $config
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Aitoc\ReviewBooster\Model\Review\Report $reportModel,
        \Aitoc\ReviewBooster\Model\Review\Rate $rateModel,
        \Magento\Backend\App\ConfigInterface $config
    ) {
        $this->_resource = $resource;
        $this->_storeManager = $storeManager;
        $this->_reviewFactory = $reviewFactory;
        $this->_reportModel = $reportModel;
        $this->_rateModel = $rateModel;
        $this->_request = $context->getRequest();
        $this->_config = $config;
        parent::__construct($context);
    }

    /**
     * Get rating names from config
     *
     * @return array
     */
    public function getRatingNames()
    {
        $ratingNames = $this->_config->getValue('review_booster/general_settings/rating_names');
        $rating = [];
        $i = 1;
        foreach (explode(',', $ratingNames) as $ratingName) {
            $rating[$i] = $ratingName;
            $i++;
        }

        return $rating;
    }

    /**
     * Get rating param from URL
     *
     * @return int
     */
    public function getRatingParam()
    {
        $rating = (int)$this->_request->getParam('rating');

        return $rating;
    }

    /**
     * Validate rating param from URL
     *
     * @param $value
     * @return bool
     */
    public function validateRatingParam($value)
    {
        if (!$value || $value < 1 || $value > 5) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Has customer purchased product
     *
     * @param $customerId
     * @param $productId
     * @return bool
     */
    public function hasCustomerPurchasedProduct($customerId, $productId)
    {
        $adapter = $this->getReadAdapter();

        $products = $adapter->select()->from(
                ['orders' => $this->getTable('sales_order')],
                'entity_id'
            )
            ->join(
                ['items' => $this->getTable('sales_order_item')],
                'items.order_id = orders.entity_id',
                'item_id'
            )
            ->where(
                'orders.customer_id = ?',
                $customerId
            )
            ->where(
                'items.product_id = ?',
                $productId
            )
            ->query()
            ->fetch(\Zend_Db::FETCH_ASSOC);

        if ($products) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get rating summary
     *
     * @param $product
     * @return mixed
     */
    public function getRatingSummary($product){
        $this->_reviewFactory
            ->create()
            ->getEntitySummary($product, $this->_storeManager->getStore()->getId());

        return $product->getRatingSummary()->getRatingSummary();
    }

    /**
     * Convert rating from percents to points
     *
     * @param $rating
     * @param bool $round
     * @return float|int
     */
    public function convertRatingToPoints($rating, $round = false)
    {
        if ($rating <= 0) {
            return 0;
        }

        $convertedRating = $rating * 0.1 / 2;
        if ($round) {
            $convertedRating = ceil($convertedRating);
        }
        if ($convertedRating < 1) {
            $convertedRating = 1;
        } elseif ($convertedRating > 5) {
            $convertedRating = 5;
        }

        return $convertedRating;
    }

    /**
     * Convert rating from points to percents
     *
     * @param $rating
     * @param bool $round
     * @return float|int
     */
    public function convertRatingToPercents($rating, $round = false)
    {
        if ($rating <= 0) {
            return 0;
        }

        $convertedRating = $rating * 20;

        if ($round) {
            $convertedRating = ceil($convertedRating);
        }

        return $convertedRating;
    }

    /**
     * Get rating equivalents of points and percents
     *
     * @return array
     */
    public function getRatingIntervals()
    {
        $intervals = [
            1 => [
                'min' => 1,
                'max' => 20
            ],
            2 => [
                'min' => 21,
                'max' => 40
            ],
            3 => [
                'min' => 41,
                'max' => 60
            ],
            4 => [
                'min' => 61,
                'max' => 80
            ],
            5 => [
                'min' => 81,
                'max' => 100
            ]
        ];

        return $intervals;
    }

    /**
     * Get detailed product rating
     *
     * @param \Magento\Review\Model\ResourceModel\Review\Collection $collection
     * @return array|bool
     */
    public function getDetailedRating(\Magento\Review\Model\ResourceModel\Review\Collection $collection)
    {
        $adapter = $this->getReadAdapter();
        $select = $collection->getSelect();
        $select->reset(\Zend_Db_Select::LIMIT_COUNT);
        $select->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $where = $select->getPart('where');
        foreach ($where as $id => $item) {
            if (preg_match('/AND \(rating_vote.percent/', $item)) {
                unset($where[$id]);
            }
			if (preg_match('/AND \(aitoc_extended.aitoc_product_recommend/', $item)) {
                unset($where[$id]);
            }
        }
        $select->setPart('where', $where);
        $ratings = $adapter->fetchAll($select);
        $detailedRating = array_fill(1, 5, 0);
        foreach ($ratings as $rating) {
            if (isset($rating['percent'])) {
                $ratingValue = $this->convertRatingToPoints($rating['percent'], true);
                $detailedRating[$ratingValue]++;
            }
        }
        return array_reverse($detailedRating, true);
    }

    /**
     * Calculate single rating
     *
     * @param $totalRatings
     * @param $singleRating
     * @return float|int
     */
    public function calculateSingleRating($totalRatings, $singleRating)
    {
        if ($totalRatings < 1 || $singleRating < 1)
        {
            return 0;
        } else {
            return round($singleRating / $totalRatings * 100);
        }
    }

    /**
     * Retrieve connection for write data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getReadAdapter()
    {
        return $this->_resource->getConnection('read');
    }

    /**
     * Return validated table name
     *
     * @param string|string[] $table
     * @return string
     */
    protected function getTable($table)
    {
        return $this->_resource->getTableName($table);
    }
	/*********Customized by Vipasha************************/
	 public function getRecommendedPercentage(\Magento\Review\Model\ResourceModel\Review\Collection $collection)
    {
		$total = count($collection);
        $adapter = $this->getReadAdapter();
        $select = $collection->getSelect();
        $select->reset(\Zend_Db_Select::LIMIT_COUNT);
        $select->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $select->where('aitoc_extended.aitoc_product_recommend = 1');
        $ratings = $adapter->fetchAll($select);
		$recommended = count($ratings);
		$recommendedpercent = ($recommended*100)/$total;
        return number_format($recommendedpercent,2);
    }
	
	public function getProsCons(\Magento\Review\Model\ResourceModel\Review\Collection $collection)
    {
		$advant = array('Durable' => 0,
		'Roomy' => 0,
		'Comfortable' => 0,
		'Stylish' => 0,
		'Numerous pockets' => 0
		);
		$disadvant = array('Zipper gets stuck' => 0,
		'Bad zippers' => 0,
		'Bulky' => 0,
		'Heavy / bulky' => 0,
		'Uncomfortable straps' => 0
		);
		 $adapter = $this->getReadAdapter();
        $select = $collection->getSelect();
        $select->reset(\Zend_Db_Select::LIMIT_COUNT);
        $select->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $ratings = $adapter->fetchAll($select);
		$pros = array();
		$cons = array();
		// echo "<pre>";
		// print_r($ratings);
		// die;
		foreach($ratings as $item)
		{
			if($item['aitoc_product_advantages'] != '')
			{
				$advantages = explode(',',$item['aitoc_product_advantages']);
				foreach($advantages as $adv)
				{
					$advant[$adv] += 1;
				}
			}
			if($item['aitoc_product_disadvantages'] != '')
			{
				$disadvantages = explode(',',$item['aitoc_product_disadvantages']);
				foreach($disadvantages as $disadv)
				{
					$disadvant[$disadv] += 1;
				}
			}				
			
		}
        return array('pros'=>$advant, 'cons' => $disadvant);
    }
}
