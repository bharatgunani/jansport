<?php
namespace Excellence\Hello\Block;
  
class Main extends \Magento\Framework\View\Element\Template
{   
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    )
    {
        parent::__construct($context);
    }
    protected function _prepareLayout()
    {
         
    }
    public function getHelloWorldTxt()
    {
        return 'Hello world!';
    }
}