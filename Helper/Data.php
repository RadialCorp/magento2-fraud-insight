<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;

/**
 * Fraud Insight data helper
 * @package Radial\FraudInsight\Helper
 */
class Data extends AbstractHelper
{
    const XML_PATH_FRAUD_INSIGHT_ENABLED = 'fraudinsight/api_setting/enabled';

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Data helper constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    /**
     * Check if fraud insight enabled
     * @return bool
     */
    public function isFraudInsightEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FRAUD_INSIGHT_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get all header data
     *
     * @return array
     */
    protected function _getHeaderData()
    {
        $headers = [];
        $server = $this->_getRequest()->getServer();
        foreach ($server as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        $jsonHelper = $this->_objectManager->get('Magento\Framework\Json\Helper\Data');
        return $jsonHelper->jsonEncode($headers);
    }

    /**
     * Save the order data that will be used for fraud insight
     *
     * @param \Magento\Sales\Model\Order $order
     */
    public function saveOrderForFraudInsight(Order $order)
    {
        $fraudInsight = $this->_objectManager->create('Radial\FraudInsight\Model\FraudInsight');
        $httpHeaders = $this->_getHeaderData();
        $orderId = $order->getIncrementId();

        $fraudInsight->setOrderIncrementId($orderId)
            ->setHttpHeaders($httpHeaders)
            ->setIsRequestSent((int)false)
            ->save();
    }
}