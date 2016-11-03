<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Process;

class FeedbackResponse
	implements \Radial\FraudInsight\Model\Process\ResponseInterface
{
	/** @var \Radial_FraudInsight_Sdk_IPayload */
	protected $_response;
    /** @var \Radial\FraudInsight\Model\FraudInsight */
    protected $_fraudInsight;
    /** @var \Radial\FraudInsight\Helper\Data */
    protected $_dataHelper;

    /**
     * Feedback Response constructor.
     * @param \Radial_FraudInsight_Sdk_IPayload $response
     * @param \Radial\FraudInsight\Model\FraudInsight $fraudInsight
     * @param \Radial\FraudInsight\Helper\Data $dataHelper
     */
    public function __construct(
        \Radial_FraudInsight_Sdk_IPayload $response,
        \Radial\FraudInsight\Model\FraudInsight $fraudInsight,
        \Radial\FraudInsight\Helper\Data $dataHelper
    ) {
        $this->_response = $response;
        $this->_fraudInsight = $fraudInsight;
        $this->_dataHelper = $dataHelper;
    }

	/**
	 * @see \Radial\FraudInsight\Model\Process\ResponseInterface::process()
	 */
	public function process()
	{
		if ($this->_response instanceof \Radial_FraudInsight_Sdk_Feedback_Response) {
			$this->_updateFraudInsight();
		} elseif ($this->_response instanceof \Radial_FraudInsight_Sdk_Error) {
            $this->_incrementFeedbackAttemptCount();
			$this->_logErrorResponse();
		}
		return $this;
	}

    /**
     * Update the fraud insight feedback data with the response payload data.
     *
     * @return self
     */
    protected function _updateFraudInsight()
    {
        $feedbackCount = $this->_fraudInsight->getFeedbackAttemptCount() + 1;
        $actionTakenAcknowledgement = (bool)$this->_response->getActionTakenAcknowledgement();
        $this->_fraudInsight->setIsFeedbackSent(1)
            ->setFeedbackAttemptCount($feedbackCount)
            ->setActionTakenAcknowledgement($actionTakenAcknowledgement)
            ->save();
        return $this;
    }

    /**
     * Increment the feedback sent attempt count.
     *
     * @return self
     */
    protected function _incrementFeedbackAttemptCount()
    {
        $feedbackCount = $this->_fraudInsight->getFeedbackAttemptCount() + 1;
        $this->_fraudInsight->setFeedbackAttemptCount($feedbackCount)->save();
        return $this;
    }

	/**
	 * Log the error code and error code description from the response payload.
	 *
	 * @return self
	 */
	protected function _logErrorResponse()
	{
		$logMessage = sprintf('[%s] Response Error Code: %s', __CLASS__, $this->_response->getErrorCode());
		$this->_dataHelper->getLogger()->critical($logMessage);
		$logMessage = sprintf('[%s] Response Error Description: %s', __CLASS__, $this->_response->getErrorDescription());
        $this->_dataHelper->getLogger()->critical($logMessage);
		return $this;
	}
}
