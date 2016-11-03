<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Payment\Adapter;

/**
 * @codeCoverageIgnore
 */
class Type
	extends \Radial\FraudInsight\Model\Payment\Adapter\AbstractType
	implements \Radial\FraudInsight\Model\Payment\Adapter\TypeInterface
{
	/** @var \Magento\Sales\Model\Order */
	protected $_order;
	/** @var \Radial\FraudInsight\Helper\Data */
	protected $_dataHelper;
    /** @var \Magento\Framework\Encryption\EncryptorInterface */
    protected $_encryptor;
    /** @var \Magento\Sales\Model\Order\Payment */
    protected $_payment;
	/** @var string | null */
	protected $_extractCardHolderName;
	/** @var string | null */
	protected $_extractPaymentAccountUniqueId;
	/** @var string | null */
	protected $_extractPaymentAccountBin;
	/** @var string | null */
	protected $_extractExpireDate;
	/** @var string | null */
	protected $_extractCardType;
	/** @var string | null */
	protected $_extractTransactionResponses;
	/** @var string */
	protected $_extractIsToken;
    protected $_mapMethods;

    /**
     * Type constructor.
     * @param \Magento\Sales\Model\Order $order
     * @param \Radial\FraudInsight\Helper\Data $dataHelper
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
	public function __construct(
	    \Magento\Sales\Model\Order $order,
        \Radial\FraudInsight\Helper\Data $dataHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
	    $this->_order = $order;
        $this->_dataHelper = $dataHelper;
        $this->_encryptor = $encryptor;
        $this->_mapMethods = $this->_dataHelper->getPaymentMethodCardTypeMap();
        $this->_initialize();
    }

	public function getExtractCardHolderName()
	{
		return $this->_extractCardHolderName;
	}

	public function setExtractCardHolderName($cardHolderName)
	{
		$this->_extractCardHolderName = $cardHolderName;
		return $this;
	}

	public function getExtractPaymentAccountUniqueId()
	{
		return $this->_extractPaymentAccountUniqueId;
	}

	public function setExtractPaymentAccountUniqueId($paymentAccountUniqueId)
	{
		$this->_extractPaymentAccountUniqueId = $paymentAccountUniqueId;
		return $this;
	}

	public function getExtractPaymentAccountBin()
	{
		return $this->_extractPaymentAccountBin;
	}

	public function setExtractPaymentAccountBin($paymentAccountBin)
	{
		$this->_extractPaymentAccountBin = $paymentAccountBin;
		return $this;
	}

	public function getExtractExpireDate()
	{
		return $this->_extractExpireDate;
	}

	public function setExtractExpireDate($expireDate)
	{
		$this->_extractExpireDate = $expireDate;
		return $this;
	}

	public function getExtractCardType()
	{
		return $this->_extractCardType;
	}

	public function setExtractCardType($cardType)
	{
		$this->_extractCardType = $cardType;
		return $this;
	}

	public function getExtractTransactionResponses()
	{
		return $this->_extractTransactionResponses;
	}

	public function setExtractTransactionResponses(array $transactionResponses)
	{
		$this->_extractTransactionResponses = $transactionResponses;
		return $this;
	}

	public function getExtractIsToken()
	{
		return $this->_extractIsToken;
	}

	public function setExtractIsToken($isToken)
	{
		$this->_extractIsToken = $isToken;
		return $this;
	}

	protected function _initialize()
	{
	    $this->_payment = $this->_order->getPayment();
		return $this;
	}

	protected function _getMethodInstance()
    {
        return $this->_payment->getMethodInstance();
    }

	protected function _getAccountUniqueId()
    {
        $cc = $this->_getDecryptedCc();
        return $cc ? base64_encode(hash('sha1', $cc, true)) : null;
    }

    protected function _getAccountBin()
    {
        $cc = $this->_getDecryptedCc();
        return $cc ? $this->_dataHelper->getFirstSixChars($cc) : null;
    }

    protected function _getPaymentExpireDate()
    {
        $month = $this->_payment->getCcExpMonth();
        $year = $this->_payment->getCcExpYear();
        return ($month && $year) ? sprintf('%d-%02d', $year, $month) : null;
    }

    protected function _getPaymentCardType()
    {
        $ccType = $this->_payment->getCcType() ?: $this->_payment->getMethod();
        return $this->_getMapFraudInsightPaymentMethod($ccType);
    }

    protected function _getMapFraudInsightPaymentMethod($methodCode)
    {
        return isset($this->_mapMethods[$methodCode]) ?
            $this->_mapMethods[$methodCode] : static::DEFAULT_PAYMENT_METHOD;
    }

    private function _getDecryptedCc()
    {
        $encryptedCc = $this->_payment->getCcNumberEnc();
        return $encryptedCc ? $this->_encryptor->decrypt($encryptedCc) : null;
    }
}
