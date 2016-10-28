<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Storelocator
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Storelocator\Block\Adminhtml\Store\Edit\Tab;

/**
 * General Tab.
 *
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class GeneralTab extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->getRegistryModel();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('store_');

        /*
         * General Field Set
         */
        $fieldset = $form->addFieldset(
            'general_fieldset',
            [
                'legend' => __('General Information'),
                'collapsable' => true,
            ]
        );

        if ($model->getId()) {
            $fieldset->addField('storelocator_id', 'hidden', ['name' => 'storelocator_id']);
        }

        $fieldset->addField(
            'store_name',
            'text',
            [
                'name' => 'store_name',
                'label' => __('Store Name'),
                'title' => __('Store Name'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'wysiwyg' => true,
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'options' => \Magestore\Storelocator\Model\Status::getAvailableStatuses(),
            ]
        );

        $fieldset->addField(
            'link',
            'text',
            [
                'name' => 'link',
                'label' => __('Store\'s Link'),
                'title' => __('Store\'s Link'),
            ]
        );

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
            ]
        );

        /*
         * Contact Field Set
         */
        $fieldset = $form->addFieldset(
            'contact_fieldset',
            [
                'legend' => __('Contact Information'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'phone',
            'text',
            [
                'name' => 'phone',
                'label' => __('Phone Number'),
                'title' => __('Phone Number'),
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email Address'),
                'title' => __('Email Address'),
            ]
        );

        $fieldset->addField(
            'fax',
            'text',
            [
                'name' => 'fax',
                'label' => __('Fax Number'),
                'title' => __('Fax Number'),
            ]
        );

        /*
         * Meta Information Field Set
         */
        $fieldset = $form->addFieldset(
            'meta_fieldset',
            [
                'legend' => __('Meta Information'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'rewrite_request_path',
            'text',
            [
                'name' => 'rewrite_request_path',
                'label' => __('URL Key'),
                'title' => __('URL Key'),
                'class' => 'validate-identifier'
            ]
        );

        $fieldset->addField(
            'meta_title',
            'text',
            [
                'name' => 'meta_title',
                'label' => __('Meta Title'),
                'title' => __('Meta Title'),
            ]
        );

        $fieldset->addField(
            'meta_keywords',
            'textarea',
            [
                'name' => 'meta_keywords',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords'),
            ]
        );
        $fieldset->addField(
            'meta_description',
            'textarea',
            [
                'name' => 'meta_description',
                'label' => __('Meta Description'),
                'title' => __('Meta Description'),
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * get registry model.
     *
     * @return \Magestore\Storelocator\Model\Store
     */
    public function getRegistryModel()
    {
        return $this->_coreRegistry->registry('storelocator_store');
    }

    /**
     * Return Tab label.
     *
     * @return string
     *
     * @api
     */
    public function getTabLabel()
    {
        return __('General information');
    }

    /**
     * Return Tab title.
     *
     * @return string
     *
     * @api
     */
    public function getTabTitle()
    {
        return __('General information');
    }

    /**
     * Can show tab in tabs.
     *
     * @return bool
     *
     * @api
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden.
     *
     * @return bool
     *
     * @api
     */
    public function isHidden()
    {
        return false;
    }
}
