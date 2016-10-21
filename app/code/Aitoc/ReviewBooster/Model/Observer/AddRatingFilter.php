<?php
namespace Aitoc\ReviewBooster\Model\Observer;

use Magento\Framework\Event\ObserverInterface;

class addRatingFilter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Aitoc\ReviewBooster\Helper\Data
     */
    protected $_helper;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Aitoc\ReviewBooster\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Aitoc\ReviewBooster\Helper\Data $helper
    ) {
        $this->_request = $request;
        $this->_helper = $helper;
    }

    /**
     * Add rating filter
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $rating = $this->_request->getParam('rating');
        $ratingInterval = $this->_helper->getRatingIntervals();
        $select = $event->getCollection()->getSelect();
        $select
            ->joinLeft(
                ['rating_vote' => 'rating_option_vote'],
                'rating_vote.review_id = main_table.review_id',
                [
                    'percent'
                ]
            );
        if ($rating) {
            $select
                ->where(
                    'rating_vote.percent <= ' . $ratingInterval[$rating]['max']
                )
                ->where(
                    'rating_vote.percent >= ' . $ratingInterval[$rating]['min']
                );
        }
    }
}
