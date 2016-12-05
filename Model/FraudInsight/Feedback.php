<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Model\FraudInsight;

class Feedback
    extends \Radial\FraudInsight\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Radial\FraudInsight\Helper\Data
     */
    protected $_helper;

    /**
     * Feedback constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Radial\FraudInsight\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Radial\FraudInsight\Helper\Data $helper
    ) {
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
    }

    /**
     * Get a new empty feedback request payload
     *
     * @return \Radial_FraudInsight_Sdk_IPayload
     */
    protected function _getNewEmptyFeedbackRequest()
    {
        return $this->_getNewSdkInstance('Radial_FraudInsight_Sdk_Feedback');
    }

    /**
     * Get a new empty feedback response payload
     *
     * @return \Radial_FraudInsight_Sdk_IPayload
     */
    protected function _getNewEmptyFeedbackResponse()
    {
        return $this->_getNewSdkInstance('Radial_FraudInsight_Sdk_Feedback_Response');
    }

    /**
     * Get new API object.
     *
     * @param \Radial_FraudInsight_Sdk_IConfig $config
     * @return \Radial_FraudInsight_Sdk_IApi
     * @codeCoverageIgnore
     */
    protected function _getApi(\Radial_FraudInsight_Sdk_IConfig $config)
    {
        return $this->_getNewSdkInstance('Radial_FraudInsight_Sdk_Api', $config);
    }

    /**
     * @param \Radial\FraudInsight\Model\ResourceModel\FraudInsight\Collection $collection
     * @return self
     */
    public function massSendFraudOrderFeedback(
        \Radial\FraudInsight\Model\ResourceModel\FraudInsight\Collection $collection
    ) {
        $orderIncrementIds = $collection->getColumnValues('order_increment_id');
        $orderCollection = $this->_objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Collection')
            ->addFieldToFilter('increment_id', ['in' => $orderIncrementIds]);

        foreach ($collection->getItems() as $fraudInsight) {
            $order = $orderCollection->getItemByColumnValue('increment_id', $fraudInsight->getOrderIncrementId());
            if ($order) {
                $this->sendFraudOrderFeedback($fraudInsight, $order);
            }
        }
        return $this;
    }

    /**
     * @param \Radial\FraudInsight\Model\FraudInsight $fraudInsight
     * @param \Magento\Sales\Model\Order $order
     */
    public function sendFraudOrderFeedback(
        \Radial\FraudInsight\Model\FraudInsight $fraudInsight,
        \Magento\Sales\Model\Order $order
    ) {
        /** @var \Radial_FraudInsight_Sdk_IPayload $request */
        $request = $this->_buildFeedbackRequestFromOrder($this->_getNewEmptyFeedbackRequest(), $order);
        if ($this->_helper->isDebugOn() === true) {
            $this->_helper->getLogger()->debug($request->serialize());
        }
        $apiConfig = $this->_setupApiConfig($request, $this->_getNewEmptyFeedbackResponse());
        $response = $this->_sendRequest($this->_getApi($apiConfig));
        if ($response) {
            if ($this->_helper->isDebugOn() === true) {
                $this->_helper->getLogger()->debug($response->serialize());
            }
            $this->_processResponse($response, $fraudInsight, $order);
        } else {
            $this->_incrementFeedbackAttemptCount($fraudInsight);
        }
    }

    /**
     * @param \Radial_FraudInsight_Sdk_IPayload $request
     * @param \Magento\Sales\Model\Order $order
     * @return \Radial_FraudInsight_Sdk_IPayload
     */
    protected function _buildFeedbackRequestFromOrder(
        \Radial_FraudInsight_Sdk_IPayload $request,
        \Magento\Sales\Model\Order $order
    ) {
        return $this->_objectManager->create(
            'Radial\FraudInsight\Model\Build\FeedbackRequest',
            [
                'request'   => $request,
                'order'     => $order,
            ])->build();
    }

    /**
     * Get new API config object.
     *
     * @param \Radial_FraudInsight_Sdk_IPayload $request
     * @param \Radial_FraudInsight_Sdk_IPayload $response
     * @return \Radial_FraudInsight_Sdk_IConfig
     */
    protected function _setupApiConfig(
        \Radial_FraudInsight_Sdk_IPayload $request,
        \Radial_FraudInsight_Sdk_IPayload $response
    ) {
        return $this->_getNewSdkInstance(
            'Radial_FraudInsight_Sdk_Config',
            [
                'api_key'   => $this->_helper->getApiKey(),
                'host'      => $this->_helper->getApiHostname(),
                'store_id'  => $this->_helper->getStoreId(),
                'request'   => $request,
                'response'  => $response,
            ]);
    }

    /**
     * @param \Radial_FraudInsight_Sdk_IApi $api
     * @return \Radial_FraudInsight_Sdk_IPayload
     */
    protected function _sendRequest(\Radial_FraudInsight_Sdk_IApi $api)
    {
        $response = null;
        try {
            $api->send();
            $response = $api->getResponseBody();
        } catch (\Radial_FraudInsight_Sdk_Exception_Network_Error_Exception $e) {
            $logMessage = sprintf('[%s] The following error has occurred while sending request: %s', __CLASS__, $e->getMessage());
            $this->_helper->getLogger()->critical($logMessage);
        } catch (\Exception $e) {
            $logMessage = sprintf('[%s] The following error has occurred while sending request: %s', __CLASS__, $e->getMessage());
            $this->_helper->getLogger()->critical($logMessage);
        }
        return $response;
    }

    /**
     * @param \Radial_FraudInsight_Sdk_IPayload $response
     * @param \Radial\FraudInsight\Model\FraudInsight $fraudInsight
     * @return self
     */
    protected function _processResponse(
        \Radial_FraudInsight_Sdk_IPayload $response,
        \Radial\FraudInsight\Model\FraudInsight $fraudInsight
    ) {
        return $this->_objectManager->create(
            'Radial\FraudInsight\Model\Process\FeedbackResponse',
            [
                'response'      => $response,
                'fraudInsight'  => $fraudInsight,
            ])->process();
    }

    /**
     * Increment the feedback sent attempt count.
     *
     * @param \Radial\FraudInsight\Model\FraudInsight $fraudInsight
     * @return self
     */
    protected function _incrementFeedbackAttemptCount(\Radial\FraudInsight\Model\FraudInsight $fraudInsight)
    {
        $feedbackCount = $fraudInsight->getFeedbackAttemptCount() + 1;
        $fraudInsight->setFeedbackAttemptCount($feedbackCount)->save();
        return $this;
    }
}