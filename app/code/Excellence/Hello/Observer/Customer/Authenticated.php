<?php
 
 
namespace Excellence\Hello\Observer\Customer;
 
use \Psr\Log\LoggerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
 
 
class Authenticated implements ObserverInterface
{
    
    protected $logger;
 
   
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
 
    public function execute(Observer $observer)
    {
        $customer = $observer->getModel();
        //print_r($customer->getData());exit;
        $this->logger->warn('Customer Logged IN:' . $customer->getFirstname());
    }
}