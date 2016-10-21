<?php
namespace Aitoc\ReviewBooster\Model;

class Reminder  extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Configuration paths
     */
    const XML_PATH_EMAIL_SENDER = 'review_booster/general_settings/email_sender';

    const XML_PATH_SEND_EMAILS_AUTOMATICALLY = 'review_booster/general_settings/send_emails_automatically';

    const XML_PATH_DELAY_PERIOD = 'review_booster/general_settings/delay_period';

    /**
     * Review reminder statuses
     */
    const STATUS_PENDING = 'pending';

    const STATUS_SENT = 'sent';

    const STATUS_FAILED = 'failed';

    /**
     * @var Resource\Reminder\CollectionFactory
     */
    protected $_reminderCollection;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $_dateFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Resource\Reminder\CollectionFactory $reminderCollection
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aitoc\ReviewBooster\Model\Resource\Reminder\CollectionFactory $reminderCollection,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\App\ConfigInterface $config,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_reminderCollection = $reminderCollection;
        $this->_orderCollection = $orderCollection;
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->_dateFactory = $dateFactory;
        $this->_transportBuilder = $transportBuilder;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Aitoc\ReviewBooster\Model\Resource\Reminder');
    }

    /**
     * Add reminders for unprocessed orders
     *
     * @return $this
     */
    public function addReminders()
    {
        $orders = $this->getUnprocessedOrders();

        foreach ($orders->getItems() as $order) {
            $data = [
                'store_id' => $order->getStoreId(),
                'customer_id' => $order->getStoreId(),
                'order_id' => $order->getId(),
                'customer_name' => $order->getCustomerFirstname(),
                'customer_email' => $order->getCustomerEmail(),
                'products' => serialize($this->getOrderProducts($order)),
            ];
            $this->addData($data)->save();
            $this->unsetData();
        }

        return $this;
    }

    /**
     * Get unprocessed orders
     *
     * @return mixed
     */
    public function getUnprocessedOrders()
    {
        $orders = $this->_orderCollection->create();
        if ($this->getAllowedStatuses()) {
            $orders->addFieldToFilter('main_table.status', ['in' => $this->getAllowedStatuses()]);
        }
        if ($this->getProcessedOrders()) {
            $orders->addFieldToFilter('main_table.entity_id', ['nin' => $this->getProcessedOrders()]);
        }

        return $orders->load();
    }

    /**
     * Get list of processed orders
     *
     * @return array
     */
    public function getProcessedOrders()
    {
        $readAdapter = $this->getResource()->getConnection();
        $processedOrderIds = $readAdapter->select()
            ->from(
                ['reminder' => $this->getResource()->getTable('aitoc_review_booster_reminder')],
                ['order_id']
            )
            ->query()
            ->fetchAll(\Zend_Db::FETCH_COLUMN);

        return $processedOrderIds;
    }

    /**
     * Get list of allowed statuses to send reminders
     *
     * @return array
     */
    public function getAllowedStatuses()
    {
        $statuses = [
            'complete'
        ];

        return $statuses;
    }

    /**
     * Get list of order products
     *
     * @param $order
     * @return array
     */
    public function getOrderProducts($order)
    {
        $products = [];
        $items = $order->getAllItems();
        foreach ($items as $item) {
            $productItem = $item->getProduct();
            if (!$item->getParentItemId()) {
                $product['id'] = $item->getId();
                $product['name'] = $item->getName();
                $product['url'] = $productItem->getProductUrl();
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * Send pending reminders
     *
     * @return $this
     */
    public function sendReminders()
    {
        if (!$this->_config->getValue(self::XML_PATH_SEND_EMAILS_AUTOMATICALLY)) {
            return $this;
        }

        $reminderData = $this->getData();
        $remindSenderContact = $this->_config->getValue(self::XML_PATH_EMAIL_SENDER);
        $products = unserialize($reminderData['products']);
        $orderProducts = $this->_getProductsHtml($products);

        $transport = $this->_transportBuilder
            ->setTemplateIdentifier(
                'aitoc_review_booster_review_reminder_email_template'
            )
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getStoreId()
                ]
            )
            ->setFrom(
                $remindSenderContact
            )
            ->setTemplateVars(
                [
                    'customer_name' => $this->getCustomerName(),
                    'order_products' => $orderProducts
                ]
            )
            ->addTo(
                $this->getCustomerEmail(),
                $this->getCustomerName()
            )
            ->getTransport();

        try {
            $transport->sendMessage();
        } catch(\Magento\Framework\Exception\MailException $e) {
            $this->setStatus(self::STATUS_FAILED)
                ->save();
            $this->_logger->critical($e);
            return $this;
        }

        $timestamp = $this->_dateFactory->create()->gmtDate();
        $this->setStatus(self::STATUS_SENT)
            ->setSentAt($timestamp)
            ->save();

        return $this;
    }

    /**
     * Get html links of products
     *
     * @param array $products
     * @return string
     */
    protected function _getProductsHtml($products)
    {
        $orderProducts = '';
        if (is_array($products)) {
            foreach ($products as $product) {
                $orderProducts .= '<a href="' . htmlspecialchars($product['url']) . '">' . htmlspecialchars($product['name']) . '</a><br/>';
            }
        }

        return $orderProducts;
    }
}
