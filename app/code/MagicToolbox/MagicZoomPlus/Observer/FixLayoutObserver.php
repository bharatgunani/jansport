<?php

namespace MagicToolbox\MagicZoomPlus\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use MagicToolbox\MagicZoomPlus\Helper\Data;

/**
 * MagicToolbox Observer
 *
 */
class FixLayoutObserver implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \MagicToolbox\MagicZoomPlus\Helper\Data
     */
    public $magicToolboxHelper = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Constructor
     *
     * @param \MagicToolbox\MagicZoomPlus\Helper\Data $magicToolboxHelper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \MagicToolbox\MagicZoomPlus\Helper\Data $magicToolboxHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->magicToolboxHelper = $magicToolboxHelper;
        $this->coreRegistry = $registry;
    }

    /**
     * Execute method
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     *
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();

        $block = $layout->getBlock('product.info.media.magiczoomplus');
        if ($block) {
            $data = $this->coreRegistry->registry('magictoolbox');
            if (is_null($data)) {
                $data = [
                    'current' => '',
                    'blocks' => [
                        'product.info.media.magic360' => null,
                        'product.info.media.magicslideshow' => null,
                        'product.info.media.magicscroll' => null,
                        'product.info.media.magiczoomplus' => null,
                        'product.info.media.magiczoom' => null,
                        'product.info.media.magicthumb' => null,
                        'product.info.media.image' => null,
                    ],
                    'cooperative-mode' => false,
                    'additional-block-name' => '',
                    'magicscroll' => '',
                ];
            }

            if (empty($data['current'])) {
                $original = $layout->getBlock('product.info.media.image');
                if ($original) {
                    $data['current'] = 'product.info.media.image';
                    $data['blocks']['product.info.media.image'] = $original;
                }
            }

            $magiczoomplus = $this->magicToolboxHelper->getToolObj();
            $isEnabled = !$magiczoomplus->params->checkValue('enable-effect', 'No', 'product');

            if ($isEnabled) {
                $layout->unsetElement($data['current']);
                $data['current'] = 'product.info.media.magiczoomplus';
                $data['blocks']['product.info.media.magiczoomplus'] = $block;
                //NOTE: to determine which module will display magicscroll headers on the product page
                $data['magicscroll'] = 'magiczoomplus';
            } else {
                $layout->unsetElement('product.info.media.magiczoomplus');
            }
            $this->coreRegistry->unregister('magictoolbox');
            $this->coreRegistry->register('magictoolbox', $data);
        }

        return $this;
    }
}
