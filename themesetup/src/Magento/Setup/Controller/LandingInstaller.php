<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\AppInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Controller for Setup Landing page
 */
class LandingInstaller extends AbstractActionController
{
    /**
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $welcomeMsg = "Welcome to Emthemes Magento 2.0 theme Installation.<br>"
            . "Click 'Agree and Set Up Our themes' or read ";
        $docRef = "http://devdocs.magento.com/guides/v1.0/install-gde/install/install-web.html";
        $agreeButtonText = "Agree and Setup Emthemes";
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('/magento/setup/landing.phtml');
//        $view->setVariable('version', AppInterface::VERSION);
        $view->setVariable('welcomeMsg', $welcomeMsg);
        $view->setVariable('docRef', $docRef);
        $view->setVariable('agreeButtonText', $agreeButtonText);
        return $view;
    }
}
