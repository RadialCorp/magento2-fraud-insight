<?php

/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Build;

class ChargebackRequest
	implements \Radial\FraudInsight\Model\Build\FeedbackRequestInterface
{
    /** @var \Radial_FraudInsight_Sdk_IPayload */
    protected $_request;
	/** @var \Magento\Sales\Model\Order */
	protected $_order;
	/** @var \Radial\FraudInsight\Helper\Data */
	protected $_dataHelper;
    protected $_data;

	/**
     * Feedback Request constructor.
     * @param \Radial_FraudInsight_Sdk_IPayload $request
     * @param \Magento\Sales\Model\Order $order
     * @param \Radial\FraudInsight\Helper\Data $dataHelper
     * @param array $data
     */
	public function __construct(
        \Radial_FraudInsight_Sdk_IPayload $request,
        \Magento\Sales\Model\Order $order,
        \Radial\FraudInsight\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_request = $request;
        $this->_order = $order;
        $this->_dataHelper = $dataHelper;
        $this->_data = $data;
    }

	/**
	 * @see \Radial\FraudInsight\Model\Build\FeedbackRequestInterface::build()
	 */
	public function build()
	{
		$this->_buildChargeback();
		return $this->_request;
	}

	/**
	 * Populate the fraud insight chargeback request payload.
	 *
	 * @return self
	 */
	protected function _buildChargeback()
	{
        $this->_request->setPrimaryLangId($this->_dataHelper->getLanguageCode())
			->setOrderId($this->_order->getIncrementId())
			->setStoreId($this->_dataHelper->getStoreId())
			->setChargeBackCode($this->_data['code'])
			->setChargeBackFlagDescription($this->_data['description'])
            ->setComments($this->_data['comment']);
		return $this;
	}
}
