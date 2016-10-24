<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */

namespace Amasty\Geoip\Controller\Adminhtml\Geoip;

class Start extends \Amasty\Geoip\Controller\Adminhtml\Geoip
{
    public function execute()
    {
        $result = array();
        try {
            $type = $this->getRequest()->getParam('type');
            $action = $this->getRequest()->getParam('action');

            $this->geoipHelper->resetDone();
            $filePath = $this->geoipHelper->getFilePath($type, $action);
            $ret = $this->importModel->startProcess($type, $filePath, $this->geoipHelper->_geoipIgnoredLines[$type]);
            $result['position'] = ceil($ret['current_row'] / $ret['rows_count'] * 100);
            $result['status'] = 'started';
            $result['file'] = $this->geoipHelper->geoipRequiredFiles[$type];

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($result));

    }

}
