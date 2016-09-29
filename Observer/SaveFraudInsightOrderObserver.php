<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Radial\FraudInsight\Helper\Data;

class SaveFraudInsightOrderObserver implements ObserverInterface
{
    /**
     * @var \Radial\FraudInsight\Helper\Data
     */
    protected $fraudInsightData;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * SaveFraudInsightOrderObserver constructor.
     * @param \Radial\FraudInsight\Helper\Data $fraudInsightData
     */
    public function __construct(
        Data $fraudInsightData
    )
    {
        $this->fraudInsightData = $fraudInsightData;
    }

    /**
     * Save order into radial_fraud_insight table to use it in the fraud insight scheduled job
     *
     * @param \Magento\Framework\Event\Observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // check if fraud insight is enabled
        if (!$this->fraudInsightData->isFraudInsightEnabled()) {
            return $this;
        }

        /* process single order */
        /* @var $order Order */
        $order = $observer->getEvent()->getData('order');
        if ($order !== null) {
            $this->fraudInsightData->saveOrderForFraudInsight($order);
            return $this;
        }

        /* process multi-shipping order */
        /* @var $orders Orders */
        $orders = (array)$observer->getEvent()->getData('orders');
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $this->fraudInsightData->saveOrderForFraudInsight($order);
            }
        }

        return $this;
    }
}