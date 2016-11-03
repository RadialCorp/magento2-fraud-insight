<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Build;

class FeedbackRequest
	implements \Radial\FraudInsight\Model\Build\FeedbackRequestInterface
{
    /** @var \Radial_FraudInsight_Sdk_IPayload */
    protected $_request;
	/** @var \Magento\Sales\Model\Order */
	protected $_order;
	/** @var \Radial\FraudInsight\Helper\Data */
	protected $_dataHelper;

	/**
     * Feedback Request constructor.
     * @param \Radial_FraudInsight_Sdk_IPayload $request
     * @param \Magento\Sales\Model\Order $order
     * @param \Radial\FraudInsight\Helper\Data $dataHelper
     */
	public function __construct(
        \Radial_FraudInsight_Sdk_IPayload $request,
        \Magento\Sales\Model\Order $order,
        \Radial\FraudInsight\Helper\Data $dataHelper
    ) {
        $this->_request = $request;
        $this->_order = $order;
        $this->_dataHelper = $dataHelper;
    }

	/**
	 * @see \Radial\FraudInsight\Model\Build\FeedbackRequestInterface::build()
	 */
	public function build()
	{
		$this->_buildFeedback();
		return $this->_request;
	}

	/**
	 * Populate the fraud insight feedback request payload.
	 *
	 * @return self
	 */
	protected function _buildFeedback()
	{
		$this->_request->setPrimaryLangId($this->_dataHelper->getLanguageCode())
			->setOrderId($this->_order->getIncrementId())
			->setStoreId($this->_dataHelper->getStoreId())
			->setActionTaken($this->_order->getState())
			->setActionTakenDescription($this->_order->getStatus());
		return $this;
	}
}
