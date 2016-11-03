<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Cron;

class ResendFraudOrderFeedback
{
    /**
     * @var \Radial\FraudInsight\Model\ResourceModel\FraudInsight\CollectionFactory
     */
    protected $_collectionFactory;
    /**
     * @var \Radial\FraudInsight\Model\FraudInsight\FeedbackFactory
     */
    protected $_feedbackFactory;
    /**
     * @var \Radial\FraudInsight\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Resend Fraud Order Feedback constructor.
     * @param \Radial\FraudInsight\Model\ResourceModel\FraudInsight\CollectionFactory $collectionFactory
     * @param \Radial\FraudInsight\Model\FraudInsight\FeedbackFactory $feedbackFactory
     * @param \Radial\FraudInsight\Helper\Data $dataHelper
     */
    public function __construct(
        \Radial\FraudInsight\Model\ResourceModel\FraudInsight\CollectionFactory $collectionFactory,
        \Radial\FraudInsight\Model\FraudInsight\FeedbackFactory $feedbackFactory,
        \Radial\FraudInsight\Helper\Data $dataHelper
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_feedbackFactory = $feedbackFactory;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * Resend feedback to fraud insight
     */
    public function execute()
    {
        if (!$this->_dataHelper->isFraudInsightEnabled()) {
            return $this;
        }

        try {
            /** @var $collection \Radial\FraudInsight\Model\ResourceModel\FraudInsight\Collection */
            $collection = $this->_collectionFactory->create();
            $collection->addFieldToFilter('is_request_sent', 1)
                ->addFieldToFilter('is_feedback_sent', 0)
                ->addFieldToFilter('feedback_attempt_count', ['lt' => 3]);
            $collection->getSelect()
                ->joinInner(
                    ['order_table' => $collection->getTable('sales_order')],
                    'main_table.order_increment_id=order_table.increment_id',
                    null)
                ->where('order_table.state=\'complete\' OR order_table.state=\'canceled\'');

            if (count($collection) > 0) {
                $this->_feedbackFactory->create()->massSendFraudOrderFeedback($collection);
            }
        } catch (\Exception $e) {
            $this->_dataHelper->getLogger()->critical(__CLASS__ . $e->getMessage());
        }

        return $this;
    }
}