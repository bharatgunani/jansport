<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\ShopbySeo\Observer\Admin;

use Amasty\Shopby\Model\FilterSetting;
use Amasty\ShopbySeo\Model\Source\IndexMode;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\Form;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

class AttributeFormTabBuildAfter implements ObserverInterface
{
    /** @var  Yesno */
    protected $yesno;

    /** @var  IndexMode */
    protected $indexMode;

    /** @var  Attribute */
    protected $attribute;

    public function __construct(Yesno $yesno, IndexMode $indexMode, Registry $registry)
    {
        $this->yesno = $yesno;
        $this->indexMode = $indexMode;
        $this->attribute = $registry->registry('entity_attribute');
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Form $form */
        $form = $observer->getData('form');

        /** @var FilterSetting $setting */
        $setting = $observer->getData('setting');

        if ($this->attribute->getFrontendInput() == 'price') {
            return;
        }

        $fieldset = $form->addFieldset(
            'shopby_fieldset_seo',
            ['legend' => __('SEO')]
        );

        $fieldset->addField(
            'is_seo_significant',
            'select',
            [
                'name'     => 'is_seo_significant',
                'label'    => __('Generate SEO URL'),
                'title'    => __('Generate SEO URL'),
                'values'   => $this->yesno->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'index_mode',
            'select',
            [
                'name'     => 'index_mode',
                'label'    => __('Allow Google to INDEX the Category Page with the Filter Applied'),
                'title'    => __('Allow Google to INDEX the Category Page with the Filter Applied'),
                'values'   => $this->indexMode->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'follow_mode',
            'select',
            [
                'name'     => 'follow_mode',
                'label'    => __('Allow Google to FOLLOW Links on the Category Page with the Filter Applied'),
                'title'    => __('Allow Google to FOLLOW Links on the Category Page with the Filter Applied'),
                'values'   => $this->indexMode->toOptionArray(),
            ]
        );
    }
}
