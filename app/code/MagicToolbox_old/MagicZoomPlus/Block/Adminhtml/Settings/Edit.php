<?php

namespace MagicToolbox\MagicZoomPlus\Block\Adminhtml\Settings;

use Magento\Backend\Block\Widget\Form\Container;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'object_id';
        $this->_controller = 'adminhtml_settings';
        $this->_blockGroup = 'MagicToolbox_MagicZoomPlus';
        $this->_headerText = 'Magic Zoom Plus Config';

        parent::_construct();

        $this->_formScripts[] = '
            require([\'magiczoomplus\'], function(magiczoomplus){
                magiczoomplus.initSwitcher();
                magiczoomplus.initDefaults();
            });
        ';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->updateButton('save', 'label', __('Save Settings'));
    }
}
