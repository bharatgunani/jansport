<?php
namespace Aitoc\ReviewBooster\Controller\Review;

use Aitoc\ReviewBooster\Controller\Review;

class RateAjax extends Review
{
    /**
     * Send review rate choice
     *
     * @return void
     */
    public function execute()
    {
        $reviewId = $this->_request->getParam('reviewId');
        $choice = $this->_request->getParam('choice');
        $review = $this->_reviewModel->loadReview($reviewId);
        if ($this->_rateModel->saveChoice($review, $choice)) {
            $this->_rateModel->rememberChoice($reviewId, $choice);
        }
    }
}
