<?php
namespace Aitoc\ReviewBooster\Model;

class Cron
{
    /**
     * @var \Aitoc\ReviewBooster\Model\ReminderFactory
     */
    protected $_reminder;

    /**
     * @var \Aitoc\ReviewBooster\Model\Resource\Reminder\CollectionFactory
     */
    protected $_reminderCollection;

    /**
     * Class constructor
     *
     * @param ReminderFactory $reminderFactory
     * @param Resource\Reminder\CollectionFactory $reminderCollectionFactory
     */
    public function __construct(
        \Aitoc\ReviewBooster\Model\ReminderFactory $reminderFactory,
        \Aitoc\ReviewBooster\Model\Resource\Reminder\CollectionFactory $reminderCollectionFactory
    ) {
        $this->_reminder = $reminderFactory;
        $this->_reminderCollection = $reminderCollectionFactory;
    }

    /**
     * Generate reminders
     *
     * @return $this
     */
    public function generateReminders()
    {
        $this->_reminder->create()
            ->addReminders();

        return $this;
    }

    /**
     * Process generated reminders
     *
     * @return $this
     */
    public function processReminders()
    {
        $collection = $this->_reminderCollection->create()
            ->setPageSize(20)
            ->setCurPage(1)
            ->addStatusFilter('pending')
            ->setSendOrder()
            ->setDelayPeriod()
            ->load();
        $collection->walk('sendReminders', [20]);

        return $this;
    }
}