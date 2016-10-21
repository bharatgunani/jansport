<?php
/**
 * Copyright © 2015 Aitoc. All rights reserved.
 */
namespace Aitoc\ReviewBooster\Model\Plugin\Resource;

class Review
{
    protected $_request;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Aitoc\ReviewBooster\Helper\Data
     */
    protected $_helper;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Aitoc\ReviewBooster\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResourceConnection $resource,
        \Aitoc\ReviewBooster\Helper\Data $helper
    ) {
        $this->_request = $request;
        $this->_resource = $resource;
        $this->_helper = $helper;
    }

    /**
     * Save review extended data
     *
     * @param \Magento\Review\Model\ResourceModel\Review $object
     * @param callable $proceed
     * @param AbstractModel $review
     * @return \Magento\Review\Model\ResourceModel\Review
     */
    public function aroundSave(
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $object,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $review
    ) {
        $detail = [];
        $result = $proceed($review);
        $adapter = $this->getWriteAdapter();
        $reviewId = $review->getReviewId();
        $customerId = $review->getCustomerId();
        $productId = $review->getEntityPkValue();

        if ($reviewId)
        {
            $condition = ['review_id = ?' => (int) $reviewId];
            $reviewData = $review->getData();
            if (
                $this->_request->getModuleName() == 'aitocreviewbooster' &&
                $this->_request->getControllerName() == 'review' &&
                $this->_request->getActionName() == 'rateAjax'
            ) {
                if (isset($reviewData['aitoc_review_helpful'])) {
                    $detail['aitoc_review_helpful'] = $reviewData['aitoc_review_helpful'];
                }
                if (isset($reviewData['aitoc_review_unhelpful'])) {
                    $detail['aitoc_review_unhelpful'] = $reviewData['aitoc_review_unhelpful'];
                }
            }
            if (
                $this->_request->getModuleName() == 'aitocreviewbooster' &&
                $this->_request->getControllerName() == 'review' &&
                $this->_request->getActionName() == 'reportAjax'
            ) {
                if (isset($reviewData['aitoc_review_reported'])) {
                    $detail['aitoc_review_reported'] = $reviewData['aitoc_review_reported'];
                }
            }
            if (
                $this->_request->getModuleName() == 'review' &&
                $this->_request->getControllerName() == 'product' &&
                ($this->_request->getActionName() == 'post' || $this->_request->getActionName() == 'save')
            ) {
                if (isset($reviewData['advantages'])) {
					if(is_array($reviewData['advantages']))
					{
						$detail['aitoc_product_advantages'] = implode(',',$reviewData['advantages']);
					}
					else
					{
						$detail['aitoc_product_advantages'] = $reviewData['advantages'];
					}
                    
                }
				if (isset($reviewData['recommend'])) {
                    $detail['aitoc_product_recommend'] = $reviewData['recommend'];
                }
                if (isset($reviewData['disadvantages'])) {
					if(is_array($reviewData['advantages']))
					{
						$detail['aitoc_product_disadvantages'] = implode(',',$reviewData['disadvantages']);
					}
					else
					{
						$detail['aitoc_product_disadvantages'] = $reviewData['disadvantages'];
					}
                }
                if ($this->isCustomerVerified($customerId, $productId))
                {
                    $detail['aitoc_customer_verified'] = 1;
                }
            }
            if ($detail) {
                if (!$this->areExtendedDataSaved($reviewId)) {
                    $detail['review_id'] = $reviewId;
                    $adapter->insert($this->getTable('aitoc_review_booster_review_detail_extended'), $detail);
                } else {
                    $adapter->update($this->getTable('aitoc_review_booster_review_detail_extended'), $detail, $condition);
                }
            }
        }
        
        return $result;
    }

    /**
     * Check has customer purchased reviewable product
     *
     * @param $customerId
     * @param $productId
     * @return bool
     */
    protected function isCustomerVerified($customerId, $productId)
    {
        if ($customerId && $productId) {
            return $this->_helper->hasCustomerPurchasedProduct($customerId, $productId);
        } else {
            return false;
        }
    }

    /**
     * Check extended data existence for defined review
     *
     * @param $reviewId
     * @return bool
     */
    protected function areExtendedDataSaved($reviewId)
    {
        $adapter = $this->getReadAdapter();

        $extendedReviewData = $adapter->select()
            ->from(
                ['extended' => $this->getTable('aitoc_review_booster_review_detail_extended')],
                'extended_id'
            )
            ->where(
                'review_id = ?', $reviewId
            )
            ->query()
            ->fetch(\Zend_Db::FETCH_ASSOC);

        if ($extendedReviewData) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return validated table name
     *
     * @param string $table
     * @return string
     */
    protected function getTable($table)
    {
        return $this->_resource->getTableName($table);
    }

    /**
     * Retrieve connection for write data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getWriteAdapter()
    {
        return $this->_resource->getConnection('write');
    }

    /**
     * Retrieve connection for read data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getReadAdapter()
    {
        return $this->_resource->getConnection('read');
    }
}
