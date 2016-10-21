<?php
namespace Aitoc\ReviewBooster\Controller;

use Magento\Review\Model\Review as ProductReview;
use Aitoc\ReviewBooster\Model\Review as ReviewModel;

abstract class Review extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Review\Model\Review
     */
    protected $_review;

    /**
     * @var \Aitoc\ReviewBooster\Model\Review
     */
    protected $_reviewModel;

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
     * Class constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param ProductReview $review
     * @param ReviewModel $reviewModel
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param ReviewModel\Report $reportModel
     * @param ReviewModel\Rate $rateModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ProductReview $review,
        ReviewModel $reviewModel,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Aitoc\ReviewBooster\Model\Review\Report $reportModel,
        \Aitoc\ReviewBooster\Model\Review\Rate $rateModel
    ) {
        $this->_review = $review;
        $this->_reviewModel = $reviewModel;
        $this->_reviewFactory = $reviewFactory;
        $this->_reportModel = $reportModel;
        $this->_rateModel = $rateModel;

        parent::__construct($context);
    }
}
