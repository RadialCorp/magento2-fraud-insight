<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\FraudInsight;

use Magento\Framework\ObjectManagerInterface;
use Radial\FraudInsight\Helper\Data;

class FraudCheck extends \Radial\FraudInsight\Model\AbstractModel
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * FraudCheck constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Radial\FraudInsight\Helper\Data $helper
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Data $helper
    ) {
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
    }

    /**
     * Get new empty request payload
     *
     * @return \Radial_FraudInsight_Sdk_IPayload
     */
    protected function _getNewEmptyRequest()
    {
        return $this->_getNewSdkInstance('Radial_FraudInsight_Sdk_Request');
    }

    /**
     * Get new empty response payload
     *
     * @return \Radial_FraudInsight_Sdk_IPayload
     */
    protected function _getNewEmptyResponse()
    {
        return $this->_getNewSdkInstance('Radial_FraudInsight_Sdk_Response');
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
    public function massProcessFraudOrder(
        \Radial\FraudInsight\Model\ResourceModel\FraudInsight\Collection $collection
    ) {
        $orderIncrementIds = $collection->getColumnValues('order_increment_id');
        $orderCollection = $this->_objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Collection')
            ->addFieldToFilter('increment_id', ['in' => $orderIncrementIds]);

        foreach ($collection->getItems() as $fraudInsight) {
            $order = $orderCollection->getItemByColumnValue('increment_id', $fraudInsight->getOrderIncrementId());
            if ($order) {
                try {
                    $this->processFraudOrder($fraudInsight, $order);
                } catch (\Exception $e) {
                    $logMessage = sprintf('[%s] The following error has occurred while processing response: %s', __CLASS__, $e->getMessage());
                    $this->_helper->getLogger()->warning($logMessage);
                }
            }
        }
        return $this;
    }

    /**
     * @param \Radial\FraudInsight\Model\FraudInsight $fraudInsight
     * @param \Magento\Sales\Model\Order $order
     */
    public function processFraudOrder(
        \Radial\FraudInsight\Model\FraudInsight $fraudInsight,
        \Magento\Sales\Model\Order $order
    ) {
        /** @var \Radial_FraudInsight_Sdk_IPayload $request */
        $request = $this->_buildRequestFromOrder($this->_getNewEmptyRequest(), $fraudInsight, $order);
        if ($this->_helper->isDebugOn() === true) {
            $this->_helper->getLogger()->debug($request->serialize());
        }
        $apiConfig = $this->_setupApiConfig($request, $this->_getNewEmptyResponse());
        $response = $this->_sendRequest($this->_getApi($apiConfig));
        if ($response) {
            if ($this->_helper->isDebugOn() === true) {
                $this->_helper->getLogger()->debug($response->serialize());
            }
            // Add comment to the order
            $message = __('The fraud insight has successfully completed. Response Code: %1 Response description: %2',
                $response->getResponseReasonCode(), $response->getResponseReasonCodeDescription());
            $order->addStatusHistoryComment($message);
            $order->save();
            // process response
            $this->_processResponse($response, $fraudInsight, $order);
        }
    }

    /**
     * Build the passed in request object using the passed in order and insight object.
     *
     * @param \Radial_FraudInsight_Sdk_IPayload $request
     * @param \Radial\FraudInsight\Model\FraudInsight $fraudInsight
     * @param \Magento\Sales\Model\Order $order
     * @return \Radial_FraudInsight_Sdk_IPayload
     */
    protected function _buildRequestFromOrder(
        \Radial_FraudInsight_Sdk_IPayload $request,
        \Radial\FraudInsight\Model\FraudInsight $fraudInsight,
        \Magento\Sales\Model\Order $order
    ) {
        return $this->_objectManager->create(
            'Radial\FraudInsight\Model\Build\FraudRequest',
            [
                'request'       => $request,
                'fraudInsight'  => $fraudInsight,
                'order'         => $order,
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
            $this->_helper->getLogger()->warning($logMessage);
        }
        return $response;
    }

    /**
     * @param \Radial_FraudInsight_Sdk_IPayload $response
     * @param \Radial\FraudInsight\Model\FraudInsight $fraudInsight
     * @param \Magento\Sales\Model\Order $order
     * @return self
     */
    protected function _processResponse(
        \Radial_FraudInsight_Sdk_IPayload $response,
        \Radial\FraudInsight\Model\FraudInsight $fraudInsight,
        \Magento\Sales\Model\Order $order
    ) {
        return $this->_objectManager->create(
            'Radial\FraudInsight\Model\Process\FraudResponse',
            [
                'response'      => $response,
                'fraudInsight'  => $fraudInsight,
                'order'         => $order,
            ])->process();
    }
}