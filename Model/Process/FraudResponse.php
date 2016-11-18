<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Process;

class FraudResponse implements \Radial\FraudInsight\Model\Process\ResponseInterface
{
	/** @var \Radial_FraudInsight_Sdk_IPayload */
	protected $_response;
	/** @var \Radial\FraudInsight\Model\FraudInsight */
	protected $_fraudInsight;
	/** @var \Magento\Sales\Model\Order */
	protected $_order;
	/** @var \Radial\FraudInsight\Helper\Data */
	protected $_dataHelper;

    /**
     * Fraud Response constructor.
     * @param \Radial_FraudInsight_Sdk_IPayload $response
     * @param \Radial\FraudInsight\Model\FraudInsight $fraudInsight
     * @param \Magento\Sales\Model\Order $order
     * @param \Radial\FraudInsight\Helper\Data $dataHelper
     */
	public function __construct(
        \Radial_FraudInsight_Sdk_IPayload $response,
        \Radial\FraudInsight\Model\FraudInsight $fraudInsight,
        \Magento\Sales\Model\Order $order,
        \Radial\FraudInsight\Helper\Data $dataHelper
    ) {
	    $this->_response = $response;
        $this->_fraudInsight = $fraudInsight;
        $this->_order = $order;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * Process payload response
     * @return self
     */
    public function process()
	{
		if ($this->_response instanceof \Radial_FraudInsight_Sdk_Response) {
            // Updating the Fraud Insight record with response data.
            $this->_updateFraudInsight();

            // Update the order status based on response action
            $responseCode = $this->_response->getResponseReasonCode();
            $action = $this->_dataHelper->getResponseAction($responseCode);
			$this->_processAction($action);
		} elseif ($this->_response instanceof \Radial_FraudInsight_Sdk_Error) {
			$this->_logErrorResponse();
		}
		return $this;
	}

    /**
     * Update the fraud insight record with response data and setting
     * the 'is_request_sent' field to 1.
     *
     * @return self
     */
    protected function _updateFraudInsight()
    {
        $this->_fraudInsight->setResponseCode($this->_response->getResponseReasonCode())
            ->setResponseCodeDescription($this->_response->getResponseReasonCodeDescription())
            ->setIsRequestSent(1)
            ->save();
        return $this;
    }

	/**
	 * Update order status based on response action
     *
     * @param  string
	 * @return self
	 */
	protected function _processAction($action)
	{
        switch ($action) {
            case \Radial\FraudInsight\Model\Config\Source\ResponseAction::ACTION_HOLD:
                $this->_processHoldAction();
				break;
			case \Radial\FraudInsight\Model\Config\Source\ResponseAction::ACTION_CANCEL:
				$this->_processCancelAction();
				break;
            case \Radial\FraudInsight\Model\Config\Source\ResponseAction::ACTION_PROCESS:
            default:
                break;
		}
		return $this;
	}

    /**
     * Hold the order and add comment
     */
	protected function _processHoldAction()
    {
        $message = __('Order was put on hold due to %1 response from Fraud Insight', $this->_response->getResponseReasonCode());
        $this->_order->hold();
        $this->_order->addStatusHistoryComment($message);
        $this->_order->save();
    }

    /**
     * Cancel the order and add comment
     */
    protected function _processCancelAction()
    {
        $message = __('The fraud insight has canceled the order');
        $this->_order->cancel();
        $this->_order->addStatusHistoryComment($message);
        $this->_order->save();
    }

	/**
	 * Log error response
     *
     * @return self
	 */
	protected function _logErrorResponse()
	{
		$logMessage = sprintf('[%s] Response Error Code: %s', __CLASS__, $this->_response->getErrorCode());
        $this->_dataHelper->getLogger()->warning($logMessage);
		$logMessage = sprintf('[%s] Response Error Description: %s', __CLASS__, $this->_response->getErrorDescription());
        $this->_dataHelper->getLogger()->warning($logMessage);
		return $this;
	}
}
