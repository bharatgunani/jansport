<?php
namespace Aitoc\ReviewBooster\Controller\Review;

use Aitoc\ReviewBooster\Controller\Review;

class ReportAjax extends Review
{
    /**
     * Send report
     *
     * @return void
     */
    public function execute()
    {
        $reviewId = $this->_request->getParam('reviewId');
        $report = $this->_request->getParam('report');
        $review = $this->_reportModel->loadReview($reviewId);
        if ($this->_reportModel->saveReport($review, $report)) {
            $this->_reportModel->rememberReport($reviewId, $report);
        }
    }
}
