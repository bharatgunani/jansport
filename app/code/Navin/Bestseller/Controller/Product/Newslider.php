<?php
namespace Navin\Bestseller\Controller\Product;

class Newslider extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        //echo "Test exit 123344545454454545454545454454 ";
        return $this->resultPageFactory->create();
    }
}