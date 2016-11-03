<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Payment\Adapter;

class Pbridge
	extends \Radial\FraudInsight\Model\Payment\Adapter\Type
{
	const TYPE_AVS_ZIP = 'avsZip';
	const TYPE_AVS_ADDRESS = 'avsAddr';

	/** @var array */
	protected $_avsMap;
	/** @var array */
	protected $_avsToMethodMap;

	/**
     * Pbridge constructor.
     * @param \Magento\Sales\Model\Order $order
     * @param \Radial\FraudInsight\Helper\Data $dataHelper
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
	public function __construct(
	    \Magento\Sales\Model\Order $order,
        \Radial\FraudInsight\Helper\Data $dataHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
	    $this->_avsMap = $this->_getAvsMap();
        $this->_avsToMethodMap = $this->_getAvsCodeToMethodMap();
        parent::__construct($order, $dataHelper, $encryptor);
    }

	protected function _initialize()
	{
		$payment = $this->_order->getPayment();
		$this->setExtractCardHolderName(null)
			->setExtractPaymentAccountUniqueId(null)
			->setExtractIsToken(static::IS_NOT_TOKEN)
			->setExtractPaymentAccountBin(null)
			->setExtractExpireDate(null)
			->setExtractCardType($this->_helper->getMapRiskInsightPaymentMethod($payment))
			->setExtractTransactionResponses($this->_getTransactions($payment));
		return $this;
	}

	/**
	 * @see https://support.authorize.net/authkb/index?page=content&id=A378
	 * @param array
	 */
	protected function _getAvsMap()
	{
		return array(
			// Street Address: Match -- First 5 Digits of ZIP: No Match
			'A' => 'M',
			// Address not provided for AVS check or street address match,
			// postal code could not be verified
			'B' => 'N',
			// AVS Error
			'E' => 'X',
			// Non U.S. Card Issuing Bank
			'G' => 'X',
			// Street Address: No Match -- First 5 Digits of ZIP: No Match
			'N' => 'N',
			// AVS not applicable for this transaction
			'P' => 'N',
			// Retry, System Is Unavailable
			'R' => 'N',
			// AVS Not Supported by Card Issuing Bank
			'S' => 'N',
			// Address Information For This Cardholder Is Unavailable
			'U' => 'N',
			// Street Address: No Match -- All 9 Digits of ZIP: Match
			'W' => 'N',
			// Street Address: Match -- All 9 Digits of ZIP: Match
			'X' => 'X',
			// Street Address: Match - First 5 Digits of ZIP: Match
			'Y' => 'N',
			// Street Address: No Match - First 5 Digits of ZIP: Match
			'Z' => 'N',
		);
	}

	/**
	 * @param  Mage_Sales_Model_Order_Payment
	 * @return array
	 */
	protected function _getTransactions(Mage_Sales_Model_Order_Payment $payment)
	{
		$data = $this->_getAvsData($this->_getCcAvsStatus($payment));
		return !empty($data) ? array($data) : $data;
	}

	/**
	 * @param  string
	 * @return array
	 */
	protected function _getAvsData($avs)
	{
		return $avs ? $this->_getAvsTransaction($avs) : array();
	}

	/**
	 * @param  string
	 * @return array
	 */
	protected function _getAvsTransaction($avs)
	{
		$method = $this->_nullCoalesce($this->_avsToMethodMap, $avs, null);
		return $method ? $this->$method($avs) : array();
	}

	/**
	 * Mapped a AVS code to a specific method to be translated.
	 * @return array
	 */
	protected function _getAvsCodeToMethodMap()
	{
		return array(
			'A' => '_getAvsZip',
			'B' => '_getAvsAddress',
			'E' => '_getAvsAddress',
			'G' => '_getAvsAddress',
			'N' => '_getAvsAddress',
			'P' => '_getAvsAddress',
			'R' => '_getAvsAddress',
			'S' => '_getAvsAddress',
			'U' => '_getAvsAddress',
			'W' => '_getAvsAddress',
			'X' => '_getAvsAddress',
			'Y' => '_getAvsAddress',
			'Z' => '_getAvsAddress',
		);
	}

	/**
	 * @param  string
	 * @return array
	 */
	protected function _getAvsZip($avs)
	{
		return array(
			'type' => static::TYPE_AVS_ZIP,
			'response' => $this->_translateAvs($avs)
		);
	}

	/**
	 * @param  string
	 * @return array
	 */
	protected function _getAvsAddress($avs)
	{
		return array(
			'type' => static::TYPE_AVS_ADDRESS,
			'response' => $this->_translateAvs($avs)
		);
	}

	/**
	 * @param  string
	 * @return string | null
	 */
	protected function _translateAvs($avs)
	{
		return $this->_nullCoalesce($this->_avsMap, $avs, null);
	}

	/**
	 * @param  Mage_Sales_Model_Order_Payment
	 * @return string | null
	 */
	protected function _getCcAvsStatus(Mage_Sales_Model_Order_Payment $payment)
	{
		return $payment->getCcAvsStatus() ?: null;
	}
}
