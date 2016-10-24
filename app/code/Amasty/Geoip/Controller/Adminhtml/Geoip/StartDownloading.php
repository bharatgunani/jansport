<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */

namespace Amasty\Geoip\Controller\Adminhtml\Geoip;

class StartDownloading extends \Amasty\Geoip\Controller\Adminhtml\Geoip
{
    public function execute()
    {
        $result = array();
        try {
            $actionType = 'download_and_import';
            $type = $this->getRequest()->getParam('type');
            $url = $this->_getFileUrl($type);
            $dir = $this->geoipHelper->getDirPath($actionType);
            $newFilePath = $this->geoipHelper->getFilePath($type, $actionType);

            if (file_exists($newFilePath)) {
                unlink($newFilePath);
            }

            if (!file_exists($dir)) {
                mkdir($dir, 0770, true);
            }

            $source = fopen($url, 'r');
            $metaData = stream_get_meta_data($source);
            $this->_fileSize[$type] = $metaData['unread_bytes'];
            $dest = fopen($newFilePath, 'w');
            stream_copy_to_stream($source, $dest);
            $result['status'] = 'finish_downloading';
            $result['file'] = $this->geoipHelper->geoipRequiredFiles[$type];

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($result));

    }

    protected function _getFileUrl($type)
    {
        $url = '';
        if ($type == 'block') {
            $url = $this->geoipHelper->getUrlBlockFile();
        } elseif ($type == 'location') {
            $url = $this->geoipHelper->getUrlLocationFile();
        }

        return $url;
    }
}
