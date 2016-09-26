<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveFraudInsightOrderObserver implements ObserverInterface
{
    /**
     * Save order into radial_fraud_insight table to use it in the fraud insight scheduled job
     *
     * @param \Magento\Framework\Event\Observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getData('order');
        $orderId = $order->getIncrementId();

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/fraud_insight_test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($orderId);

        return $this;
    }

}