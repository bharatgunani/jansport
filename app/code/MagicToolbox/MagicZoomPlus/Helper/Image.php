<?php

namespace MagicToolbox\MagicZoomPlus\Helper;

/**
 * MagicZoomPlus image helper
 */
class Image extends \Magento\Catalog\Helper\Image
{
    /**
     * Retrieve original image width
     *
     * @return int|null
     */
    public function getOriginalWidth()
    {
        if(!$this->_getModel()->getBaseFile()) {
            return null;
        }
        return $this->_getModel()->getImageProcessor()->getOriginalWidth();
    }

    /**
     * Retrieve original image height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        if(!$this->_getModel()->getBaseFile()) {
            return null;
        }
        return $this->_getModel()->getImageProcessor()->getOriginalHeight();
    }
}
