<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */

namespace Amasty\Geoip\Controller\Adminhtml\Geoip;

class Process extends \Amasty\Geoip\Controller\Adminhtml\Geoip
{
    public function execute()
    {
        $result = array();
        try {
            $type = $this->getRequest()->getParam('type');
            $action = $this->getRequest()->getParam('action');
            $filePath = $this->geoipHelper->getFilePath($type, $action);
            $ret = $this->importModel->doProcess($type, $filePath);
            $result['type'] = $type;
            $result['status'] = 'processing';
            $result['position'] = ceil($ret['current_row'] / $ret['rows_count'] * 100);
            if ($type == 'block' && $result['position'] == 100 && $ret['current_row'] + 1 != $ret['rows_count']) {
                $result['position'] = 99;
            }
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($result));

    }

}
