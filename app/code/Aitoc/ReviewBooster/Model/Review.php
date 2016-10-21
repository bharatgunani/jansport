<?php
namespace Aitoc\ReviewBooster\Model;

class Review extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Ten years
     */
    const COOKIE_DURATION = 315360000;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookie;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadata;

    public function __construct(
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookie,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->_reviewFactory = $reviewFactory;
        $this->_storeManager = $storeManager;
        $this->_cookie = $cookie;
        $this->_cookieMetadata = $cookieMetadataFactory;
    }

    /**
     * Load review
     *
     * @param $reviewId
     * @return \Magento\Review\Model\Review|bool
     */
    public function loadReview($reviewId)
    {
        if(!$reviewId)
        {
            return false;
        }

        $review = $this->_reviewFactory->create()->load($reviewId);
        if (!$review->getId()
            || !$review->isApproved()
            || !$review->isAvailableOnStore($this->_storeManager->getStore())
        ) {
            return false;
        }

        return $review;
    }

    /**
     * Check review status
     *
     * @param $reviewId
     * @return bool
     */
    public function checkReviewStatus($reviewId)
    {
        $cookieName = $this->getCookieName();
        $typeCookie = $this->_cookie->getCookie($cookieName);
        if (isset($typeCookie[$reviewId])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get cookie name
     *
     * @param $reviewId
     * @return string
     */
    public function getCookieNameForSave($reviewId, $cookieName)
    {
        $cookieName = $cookieName . '[' . $reviewId . ']';

        return $cookieName;
    }

    /**
     * Get cookie duration
     *
     * @return int
     */
    public function getCookieDuration()
    {
        return self::COOKIE_DURATION;
    }
}
