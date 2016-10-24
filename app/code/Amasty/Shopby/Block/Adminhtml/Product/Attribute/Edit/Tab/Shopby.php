<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Amasty\Shopby\Model\FilterSetting;
use Amasty\Shopby\Model\FilterSettingFactory;
use Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\Shopby\Model\Source\MeasureUnit;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class Shopby extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var Yesno
     */
    protected $yesNo;

    /** @var  DisplayMode */
    protected $displayMode;

    /** @var  MeasureUnit */
    protected $measureUnitSource;

    /** @var  FilterSetting */
    protected $setting;

    /** @var Attribute $attributeObject */
    protected $attributeObject;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesNo,
        DisplayMode $displayMode,
        MeasureUnit $measureUnitSource,
        FilterSettingFactory $settingFactory,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->displayMode = $displayMode;
        $this->measureUnitSource = $measureUnitSource;
        $this->setting = $settingFactory->create();
        $this->attributeObject = $registry->registry('entity_attribute');
        $this->displayMode->setAttributeType($this->attributeObject->getBackendType());
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $this->prepareFilterSetting();
        $form->setDataObject($this->setting);

        $yesnoSource = $this->yesNo->toOptionArray();
        /** @var  $dependence \Magento\SalesRule\Block\Widget\Form\Element\Dependence */
        $dependence = $this->getLayout()->createBlock(
            'Magento\SalesRule\Block\Widget\Form\Element\Dependence'
        );

        $fieldset = $form->addFieldset(
            'shopby_fieldset_filtering',
            ['legend' => __('Filtering'), 'collapsable' => $this->getRequest()->has('popup')]
        );

        $fieldset->addField(
            'filter_code',
            'hidden',
            [
                'name'     => 'filter_code',
                'value'   => $this->setting->getFilterCode(),
            ]
        );

        $displayModeField = $fieldset->addField(
            'display_mode',
            'select',
            [
                'name'     => 'display_mode',
                'label'    => __('Display Mode'),
                'title'    => __('Display Mode'),
                'values'   => $this->displayMode->toOptionArray(),
            ]
        );
        $dependence->addFieldMap(
            $displayModeField->getHtmlId(),
            $displayModeField->getName()
        );

        if($this->attributeObject->getBackendType() != 'decimal') {
            $multiselectField = $fieldset->addField(
                'is_multiselect',
                'select',
                [
                    'name'     => 'is_multiselect',
                    'label'    => __('Allow Multiselect'),
                    'title'    => __('Allow Multiselect'),
                    'values'   => $yesnoSource,
                ]
            );
            $dependence->addFieldMap(
                $multiselectField->getHtmlId(),
                $multiselectField->getName()
            )->addFieldDependence(
                $multiselectField->getName(),
                $displayModeField->getName(),
                \Amasty\Shopby\Model\Source\DisplayMode::MODE_DEFAULT
            );

            $fieldset->addField(
                'hide_one_option',
                'select',
                [
                    'name'     => 'hide_one_option',
                    'label'    => __('Hide filter when only one option available'),
                    'title'    => __('Hide filter when only one option available'),
                    'values'   => $yesnoSource,
                ]
            );
        } else {
            $useCurrencySymbolField = $fieldset->addField(
                'units_label_use_currency_symbol',
                'select',
                [
                    'name'     => 'units_label_use_currency_symbol',
                    'label'    => __('Measure Units'),
                    'title'    => __('Measure Units'),
                    'values'   => $this->measureUnitSource->toOptionArray(),
                ]
            );
            $dependence->addFieldMap(
                $useCurrencySymbolField->getHtmlId(),
                $useCurrencySymbolField->getName()
            );

            $unitsLabelField = $fieldset->addField(
                'units_label',
                'text',
                [
                    'name'     => 'units_label',
                    'label'    => __('Unit label'),
                    'title'    => __('Unit label'),
                ]
            );

            $dependence->addFieldMap(
                $unitsLabelField->getHtmlId(),
                $unitsLabelField->getName()
            );

            $dependence->addFieldDependence(
                $unitsLabelField->getName(),
                $useCurrencySymbolField->getName(),
                MeasureUnit::CUSTOM
            );

            $sliderStepField = $fieldset->addField(
                'slider_step',
                'text',
                [
                    'name'     => 'slider_step',
                    'label'    => __('Slider Step'),
                    'title'    => __('Slider Step'),
                ]
            );

            $dependence->addFieldMap(
                $sliderStepField->getHtmlId(),
                $sliderStepField->getName()
            )->addFieldDependence(
                $sliderStepField->getName(),
                $displayModeField->getName(),
                \Amasty\Shopby\Model\Source\DisplayMode::MODE_SLIDER
            );
        }

        $this->setChild(
            'form_after',
            $dependence
        );


        $this->_eventManager->dispatch('amshopby_attribute_form_tab_build_after', ['form' => $form, 'setting' => $this->setting]);

        $this->setForm($form);
        $data = $this->setting->getData();
        if(isset($data['slider_step'])) {
            $data['slider_step'] = round($data['slider_step'], 4);
        }
        $form->setValues($data);
        return parent::_prepareForm();
    }

    protected function prepareFilterSetting()
    {
        if ($this->attributeObject->getId()) {
            $filterCode = 'attr_' . $this->attributeObject->getAttributeCode();
            $this->setting->load($filterCode, 'filter_code');
            $this->setting->setFilterCode($filterCode);
        }
    }
}
