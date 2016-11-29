<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;

/**
 * Fraud Insight data helper
 * @package Radial\FraudInsight\Helper
 */
class Data extends AbstractHelper
{
    const DEFAULT_LANGUAGE_CODE = 'en';

    const XML_PATH_FRAUD_INSIGHT_ENABLED    = 'fraudinsight/api_setting/enabled';
    const XML_PATH_FRAUD_INSIGHT_STORE_ID   = 'fraudinsight/api_setting/store_id';
    const XML_PATH_FRAUD_INSIGHT_API_URL    = 'fraudinsight/api_setting/api_url';
    const XML_PATH_FRAUD_INSIGHT_API_KEY    = 'fraudinsight/api_setting/api_key';
    const XML_PATH_FRAUD_INSIGHT_TIMEOUT    = 'fraudinsight/api_setting/timeout';
    const XML_PATH_FRAUD_INSIGHT_ORDER_SOURCE = 'fraudinsight/api_setting/order_source';
    const XML_PATH_FRAUD_INSIGHT_DEBUG    = 'fraudinsight/api_setting/debug';
    const XML_PATH_FRAUD_INSIGHT_FEEDBACK_RESEND_THRESHOLD = 'fraudinsight/api_setting/debug';

    const CARD_TYPE_MAP = 'fraudinsight/map/card_type';
    const SHIPPING_METHOD_MAP = 'fraudinsight/map/shipping_method';
    const PAYMENT_ADAPTER_MAP = 'fraudinsight/map/payment_adapter';

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;
    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager;

    /**
     * Data helper constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    /**
     * Check if fraud insight enabled
     *
     * @param null|integer $storeId
     * @return bool
     */
    public function isFraudInsightEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_FRAUD_INSIGHT_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * retrieve the API encrypted key from store config and decrypt it.
     *
     * @param  null|integer $storeId
     * @return string
     */
    public function getApiKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_FRAUD_INSIGHT_API_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve the API Host Name from store config
     *
     * @param  null|integer $storeId
     * @return string
     */
    public function getApiHostname($storeId = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_FRAUD_INSIGHT_API_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve the FraudNet Store id from store config
     *
     * @param  null|integer $storeId
     * @return string
     */
    public function getStoreId($storeId = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_FRAUD_INSIGHT_STORE_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Whether debug is enabled in configuration
     *
     * @param null|integer $storeId
     * @return bool
     */
    public function isDebugOn($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            static::XML_PATH_FRAUD_INSIGHT_DEBUG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getOrderSource()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_FRAUD_INSIGHT_ORDER_SOURCE);
    }

    public function getOrderSourceByArea(Order $order)
    {
        return ($order->getRemoteIp()) ? 'WEBSTORE' : 'DASHBOARD';
    }

    public function getLanguageCode()
    {
        $localCode = $this->scopeConfig->getValue('general/locale/code');
        return $localCode ? substr($localCode, 0, 2) : static::DEFAULT_LANGUAGE_CODE;
    }

    public function getFeedbackResendThreshold()
    {
        return (int)$this->scopeConfig->getValue(static::XML_PATH_FRAUD_INSIGHT_FEEDBACK_RESEND_THRESHOLD);
    }

    public function getNewDateTime($dateTime)
    {
        return new \DateTime($dateTime);
    }

    /**
     * Get the payment adapter map settings from store config
     *
     * @param  mixed
     * @return string
     */
    public function getPaymentAdapterMap()
    {
        return $this->scopeConfig->getValue(static::PAYMENT_ADAPTER_MAP);
    }

    /**
     * Get the payment method card type map settings from store config
     *
     * @param  mixed
     * @return string
     */
    public function getPaymentMethodCardTypeMap()
    {
        return $this->scopeConfig->getValue(static::CARD_TYPE_MAP);
    }

    /**
     * Get the response action value from store config
     *
     * @param $responseCode
     * @return string
     */
    public function getResponseAction($responseCode)
    {
        $configPath = sprintf('fraudinsight/api_setting/response_action/%s', strtolower($responseCode));
        return $this->scopeConfig->getValue($configPath);
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

    /**
     * Check if fraud request sent
     *
     * @param $orderIncrementId
     * @return bool
     */
    public function getIsOrderFraudChecked($orderIncrementId)
    {
        $fraudInsight = $this->_objectManager->create('Radial\FraudInsight\Model\FraudInsight')
            ->load($orderIncrementId);
        return ($fraudInsight->getId()) ? $fraudInsight->getIsRequestSent() : true;
    }

    /**
     * Check if the order is in state to send the feedback
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function isInAStateToSendFeedback(\Magento\Sales\Model\Order $order)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/fraud_insight_test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $debugData = [
            'Order Increment ID' => $order->getIncrementId(),
            'State' => $order->getState(),
            'Status' => $order->getStatus(),
        ];
        $logger->info($debugData);
        // check if order in state to send feedback
        return (bool)($order->getState() === \Magento\Sales\Model\Order::STATE_COMPLETE
            || $order->getState() === \Magento\Sales\Model\Order::STATE_CANCELED);
    }

    /**
     * Check if the feedback is already send or
     * has reached the maximum number of retries value from configuration
     *
     * @param \Radial\FraudInsight\Model\FraudInsight $fraudInsight
     * @return bool
     */
    public function canSendFraudOrderFeedback(\Radial\FraudInsight\Model\FraudInsight $fraudInsight)
    {
        // check if fraud insight exist and fraud request is sent
        if (!$fraudInsight->getId() && !$fraudInsight->getIsRequestSent()) {
            return false;
        }

        // check if feedback send or reached the maximum retries
        /*if (!$fraudInsight->getIsFeedbackSent() && $fraudInsight->getFeedbackAttemptCount() <= 3) {
            return false;
        }*/

        return true;
    }

    /**
     * Return a hash and base64 encoded string of the passed in credit card number.
     * @param  string $cc
     * @return string
     */
    public function hashAndEncodeCc($cc)
    {
        return base64_encode(hash('sha1', $cc, true));
    }

    /**
     * Get the first 6 characters from a passed in string
     *
     * @param  string
     * @return string
     */
    public function getFirstSixChars($string)
    {
        return $string ? substr($string, 0, 6) : $string;
    }

    /**
     * Get the instance of logger
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }
}