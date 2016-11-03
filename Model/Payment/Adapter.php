<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Payment;

class Adapter
	implements \Radial\FraudInsight\Model\Payment\AdapterInterface
{
	/** @var \Magento\Sales\Model\Order */
	protected $_order;
	/** @var \Magento\Framework\ObjectManagerInterface */
	protected $_objectManager;
    /** @var \Radial\FraudInsight\Helper\Data */
	protected $_dataHelper;
    /** @var \Radial\FraudInsight\Model\Payment\Adapter\TypeInterface */
    protected $_adapter;
    /** @var \Radial\FraudInsight\Model\Payment\Adapter\TypeInterface */
    protected $_adapterMaps;

	/**
     * Adapter constructor.
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Radial\FraudInsight\Helper\Data $dataHelper
     */
	public function __construct(
	    \Magento\Sales\Model\Order $order,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Radial\FraudInsight\Helper\Data $dataHelper
    ) {
	    $this->_order = $order;
        $this->_objectManager = $objectManager;
        $this->_dataHelper = $dataHelper;
        $this->_adapterMaps = $this->_getAdapterMaps();
        $this->_adapter = $this->_getPaymentAdapter();
    }

	public function getAdapter()
	{
		return $this->_adapter;
	}

	/**
	 * @return array
	 */
	protected function _getAdapterMaps()
	{
		return $this->_dataHelper->getPaymentAdapterMap();
	}

	/**
	 * @return \Radial\FraudInsight\Model\Payment\Adapter\TypeInterface | null
	 */
	protected function _getPaymentAdapter()
	{
		return $this->_objectManager->create($this->_getAdapterModel(), ['order' => $this->_order]);
	}

	/**
	 * @return string
	 */
	protected function _getAdapterModel()
	{
		$method = $this->_getMethod();
		return (isset($this->_adapterMaps[$method]) && $this->_adapterMaps[$method])
			? $this->_adapterMaps[$method] : static::DEFAULT_ADAPTER;
	}

	/**
	 * @return string
	 */
	protected function _getMethod()
	{
	    return $this->_order->getPayment()->getMethod();
        /*$payment = $this->_order->getPayment();
		return $this->_dataHelper->isGiftCardPayment($this->_order, $payment)
			? static::GIFT_CARD_PAYMENT_METHOD : $payment->getMethod();*/
	}
}
