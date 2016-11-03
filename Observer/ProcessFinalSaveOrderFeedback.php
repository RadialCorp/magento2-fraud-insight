<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class ProcessFinalSaveOrderFeedback implements ObserverInterface
{
    /** @var \Radial\FraudInsight\Model\FraudInsight */
    protected $_fraudInsightFactory;
    /** @var \Radial\FraudInsight\Model\FraudInsight\FeedbackFactory */
    protected $_feedbackFactory;
    /** @var \Radial\FraudInsight\Helper\Data */
    protected $_dataHelper;

    /**
     * ProcessFinalSaveOrderFeedback constructor.
     * @param \Radial\FraudInsight\Model\FraudInsightFactory $fraudInsightFactory
     * @param \Radial\FraudInsight\Model\FraudInsight\FeedbackFactory $feedbackFactory
     * @param \Radial\FraudInsight\Helper\Data $dataHelper
     */
    public function __construct(
        \Radial\FraudInsight\Model\FraudInsightFactory $fraudInsightFactory,
        \Radial\FraudInsight\Model\FraudInsight\FeedbackFactory $feedbackFactory,
        \Radial\FraudInsight\Helper\Data $dataHelper
    ) {
        $this->_fraudInsightFactory = $fraudInsightFactory;
        $this->_feedbackFactory = $feedbackFactory;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // check if fraud insight is enabled
        if (!$this->_dataHelper->isFraudInsightEnabled()) {
            return $this;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        if (!$this->_dataHelper->isInAStateToSendFeedback($order)) {
            return $this;
        }
        /** @var \Radial\FraudInsight\Model\FraudInsight $fraudInsight */
        $orderIncrementId = $order->getIncrementId();
        $fraudInsight = $this->_fraudInsightFactory->create()->load($orderIncrementId);
        if ($this->_dataHelper->canSendFraudOrderFeedback($fraudInsight)) {
            $this->_feedbackFactory->create()->sendFraudOrderFeedback($fraudInsight, $order);
        }

        return $this;
    }

}