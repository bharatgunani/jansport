<?php

namespace MagicToolbox\MagicZoomPlus\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use MagicToolbox\MagicZoomPlus\Model\ConfigFactory;

/**
 * Data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Model factory
     * @var \MagicToolbox\MagicZoomPlus\Model\ConfigFactory
     */
    protected $_modelConfigFactory = null;

    /**
     * MagicZoomPlus module core class
     *
     * @var \MagicToolbox\MagicZoomPlus\Classes\MagicZoomPlusModuleCoreClass
     *
     */
    protected $magiczoomplus = null;

    /**
     * MagicScroll module core class
     *
     * @var \MagicToolbox\MagicZoomPlus\Classes\MagicScrollModuleCoreClass
     *
     */
    protected $magicscroll = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \MagicToolbox\MagicZoomPlus\Model\ConfigFactory $modelConfigFactory
     * @param \stdClass $magicscroll
     * @param \MagicToolbox\MagicZoomPlus\Classes\MagicZoomPlusModuleCoreClass $magiczoomplus
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \MagicToolbox\MagicZoomPlus\Model\ConfigFactory $modelConfigFactory,
        \stdClass $magicscroll,
        \MagicToolbox\MagicZoomPlus\Classes\MagicZoomPlusModuleCoreClass $magiczoomplus
    ) {
        $this->_modelConfigFactory = $modelConfigFactory;
        $this->magicscroll = $magicscroll;
        $this->magiczoomplus = $magiczoomplus;
        parent::__construct($context);
    }

    public function getToolObj()
    {
        static $doInit = true;
        if ($doInit) {
            $model = $this->_modelConfigFactory->create();
            $collection = $model->getCollection();
            $collection->addFieldToFilter('platform', 0);
            $collection->addFieldToFilter('status', ['neq' => 0]);
            $data = $collection->getData();
            foreach ($data as $key => $param) {
                $this->magiczoomplus->params->setValue($param['name'], $param['value'], $param['profile']);
            }
            $doInit = false;
        }
        return $this->magiczoomplus;
    }

    public function getScrollObj()
    {
        static $doInit = true;
        if ($doInit) {
            //NOTE: init main tool
            $this->getToolObj();
            if ($this->magiczoomplus->params->checkValue('magicscroll', 'Yes', 'product')) {
                //NOTE: load params in a separate profile, in order not to overwrite the options of MagicScroll module
                $this->magicscroll->params->appendParams($this->magiczoomplus->params->getParams('product'), 'magiczoomplus-magicscroll-product');
                $this->magicscroll->params->setValue('orientation', ($this->magiczoomplus->params->checkValue('template', ['left', 'right'], 'product') ? 'vertical' : 'horizontal'), 'magiczoomplus-magicscroll-product');
            } else {
                $this->magicscroll = null;
            }
            $doInit = false;
        }
        return $this->magicscroll;
    }

    /**
     * Public method for retrieve config map
     *
     * @return array
     */
    public function getConfigMap()
    {
        return unserialize('a:2:{s:7:"default";a:8:{s:7:"General";a:1:{i:0;s:28:"include-headers-on-all-pages";}s:24:"Positioning and Geometry";a:7:{i:0;s:15:"thumb-max-width";i:1;s:16:"thumb-max-height";i:2;s:9:"zoomWidth";i:3;s:10:"zoomHeight";i:4;s:12:"zoomPosition";i:5;s:13:"square-images";i:6;s:12:"zoomDistance";}s:15:"Multiple images";a:4:{i:0;s:15:"selectorTrigger";i:1;s:18:"selector-max-width";i:2;s:19:"selector-max-height";i:3;s:16:"transitionEffect";}s:13:"Miscellaneous";a:4:{i:0;s:8:"lazyZoom";i:1;s:10:"rightClick";i:2;s:12:"show-message";i:3;s:7:"message";}s:9:"Zoom mode";a:6:{i:0;s:8:"zoomMode";i:1;s:6:"zoomOn";i:2;s:7:"upscale";i:3;s:9:"smoothing";i:4;s:12:"variableZoom";i:5;s:11:"zoomCaption";}s:11:"Expand mode";a:6:{i:0;s:6:"expand";i:1;s:14:"expandZoomMode";i:2;s:12:"expandZoomOn";i:3;s:13:"expandCaption";i:4;s:19:"closeOnClickOutside";i:5;s:8:"cssClass";}s:4:"Hint";a:7:{i:0;s:4:"hint";i:1;s:17:"textHoverZoomHint";i:2;s:17:"textClickZoomHint";i:3;s:14:"textExpandHint";i:4;s:12:"textBtnClose";i:5;s:11:"textBtnNext";i:6;s:11:"textBtnPrev";}s:6:"Mobile";a:4:{i:0;s:17:"zoomModeForMobile";i:1;s:26:"textHoverZoomHintForMobile";i:2;s:26:"textClickZoomHintForMobile";i:3;s:23:"textExpandHintForMobile";}}s:7:"product";a:9:{s:7:"General";a:3:{i:0;s:13:"enable-effect";i:1;s:8:"template";i:2;s:11:"magicscroll";}s:24:"Positioning and Geometry";a:7:{i:0;s:15:"thumb-max-width";i:1;s:16:"thumb-max-height";i:2;s:9:"zoomWidth";i:3;s:10:"zoomHeight";i:4;s:12:"zoomPosition";i:5;s:13:"square-images";i:6;s:12:"zoomDistance";}s:15:"Multiple images";a:4:{i:0;s:15:"selectorTrigger";i:1;s:18:"selector-max-width";i:2;s:19:"selector-max-height";i:3;s:16:"transitionEffect";}s:13:"Miscellaneous";a:4:{i:0;s:8:"lazyZoom";i:1;s:10:"rightClick";i:2;s:12:"show-message";i:3;s:7:"message";}s:9:"Zoom mode";a:6:{i:0;s:8:"zoomMode";i:1;s:6:"zoomOn";i:2;s:7:"upscale";i:3;s:9:"smoothing";i:4;s:12:"variableZoom";i:5;s:11:"zoomCaption";}s:11:"Expand mode";a:6:{i:0;s:6:"expand";i:1;s:14:"expandZoomMode";i:2;s:12:"expandZoomOn";i:3;s:13:"expandCaption";i:4;s:19:"closeOnClickOutside";i:5;s:8:"cssClass";}s:4:"Hint";a:7:{i:0;s:4:"hint";i:1;s:17:"textHoverZoomHint";i:2;s:17:"textClickZoomHint";i:3;s:14:"textExpandHint";i:4;s:12:"textBtnClose";i:5;s:11:"textBtnNext";i:6;s:11:"textBtnPrev";}s:6:"Mobile";a:4:{i:0;s:17:"zoomModeForMobile";i:1;s:26:"textHoverZoomHintForMobile";i:2;s:26:"textClickZoomHintForMobile";i:3;s:23:"textExpandHintForMobile";}s:6:"Scroll";a:14:{i:0;s:5:"width";i:1;s:6:"height";i:2;s:4:"mode";i:3;s:5:"items";i:4;s:5:"speed";i:5;s:8:"autoplay";i:6;s:4:"loop";i:7;s:4:"step";i:8;s:6:"arrows";i:9;s:10:"pagination";i:10;s:6:"easing";i:11;s:13:"scrollOnWheel";i:12;s:9:"lazy-load";i:13;s:19:"scroll-extra-styles";}}}');
    }

    /**
     * Public method for retrieve statuses
     *
     * @return array
     */
    public function getStatuses($profile = false, $force = false)
    {
        static $result = null;
        if (is_null($result) || $force) {
            $result = [];
            $model = $this->_modelConfigFactory->create();
            $collection = $model->getCollection();
            $collection->addFieldToFilter('platform', 0);
            $data = $collection->getData();
            foreach ($data as $key => $param) {
                if (!isset($result[$param['profile']])) {
                    $result[$param['profile']] = [];
                }
                $result[$param['profile']][$param['name']] = $param['status'];
            }
        }
        return isset($result[$profile]) ? $result[$profile] : $result;
    }

    /**
     * Public method to get image sizes
     *
     * @return array
     */
    public function magicToolboxGetSizes($sizeType, $originalSizes = [])
    {
        $w = $this->magiczoomplus->params->getValue($sizeType.'-max-width');
        $h = $this->magiczoomplus->params->getValue($sizeType.'-max-height');
        if (empty($w)) {
            $w = 0;
        }
        if (empty($h)) {
            $h = 0;
        }
        if ($this->magiczoomplus->params->checkValue('square-images', 'No')) {
            //NOTE: fix for bad images
            if (empty($originalSizes[0]) || empty($originalSizes[1])) {
                return [$w, $h];
            }
            list($w, $h) = $this->calculateSize($originalSizes[0], $originalSizes[1], $w, $h);
        } else {
            $h = $w = $h ? ($w ? min($w, $h) : $h) : $w;
        }
        return [$w, $h];
    }

    /**
     * Public method to calculate sizes
     *
     * @return array
     */
    private function calculateSize($originalW, $originalH, $maxW = 0, $maxH = 0)
    {
        if (!$maxW && !$maxH) {
            return [$originalW, $originalH];
        } elseif (!$maxW) {
            $maxW = ($maxH * $originalW) / $originalH;
        } elseif (!$maxH) {
            $maxH = ($maxW * $originalH) / $originalW;
        }
        $sizeDepends = $originalW/$originalH;
        $placeHolderDepends = $maxW/$maxH;
        if ($sizeDepends > $placeHolderDepends) {
            $newW = $maxW;
            $newH = $originalH * ($maxW / $originalW);
        } else {
            $newW = $originalW * ($maxH / $originalH);
            $newH = $maxH;
        }
        return [round($newW), round($newH)];
    }
}
