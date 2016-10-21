<?php
namespace Aitoc\ReviewBooster\Block\Plugin\Review\Product;

class Review
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_request = $request;
    }

    /**
     * Add rating parameter to reviews URL
     *
     * @param \Magento\Review\Block\Product\Review $review
     * @param $result
     * @return string
     */
    public function afterGetProductReviewUrl(\Magento\Review\Block\Product\Review $review, $result)
    {
        $url = $result;
        $rating = $this->_request->getParam('rating');
        if ($rating && $rating >= 1 && $rating <= 5) {
            $url = $review->getUrl('review/product/listAjax', ['id' => $review->getProductId(), 'rating' => $rating]);
        }

        return $url;
    }
}
