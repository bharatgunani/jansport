<?php
 
namespace Excellence\Hello\Controller\Adminhtml\World;
 
class Index extends \Magento\Backend\App\Action
{
    
    protected $resultPageFactory = false;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Excellence_Hello::hello_world_test1');
        $resultPage->addBreadcrumb(__('Hello'), __('Hello'));
        $resultPage->addBreadcrumb(__('World'), __('World'));
        $resultPage->getConfig()->getTitle()->prepend(__('Hello World'));
        return $resultPage;
    }
}

