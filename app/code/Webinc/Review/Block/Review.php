<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webinc\Review\Block;

class Review extends \Magento\Review\Block\Product\Review
{ public function setTabTitle()
    {
        $title = $this->getCollectionSize()
            ? __('Customer Ratings & Review', '<span class="counter">' . $this->getCollectionSize() . '</span>')
            : __('Customer Ratings & Review');
        $this->setTitle($title);
    }
	
	public function getCollectionSize()
    {
        $collection = $this->_reviewsColFactory->create()->addStoreFilter(
            $this->_storeManager->getStore()->getId()
        )->addStatusFilter(
            \Magento\Review\Model\Review::STATUS_APPROVED
        )->addEntityFilter(
            'product',
            $this->getProductId()
        );

        return $collection->getSize();
    }
}
