<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */

namespace Amasty\Geoip\Controller\Adminhtml\Geoip;

class Commit extends \Amasty\Geoip\Controller\Adminhtml\Geoip
{
    public function execute()
    {
        $result = array();

        try {
            $type = $this->getRequest()->getParam('type');
            $isDownload = $this->getRequest()->getParam('is_download');
            $this->importModel->commitProcess($type, $isDownload);
            $result['status'] = 'done';
            $result['full_import_done'] = $this->geoipHelper->isDone() ? "1" : "0";
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($result));
    }

}
