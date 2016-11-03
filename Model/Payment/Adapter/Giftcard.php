<?php

/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Payment\Adapter;

class Giftcard
	extends \Radial\FraudInsight\Model\Payment\Adapter\Type
{
	const PAYMENT_METHOD_TYPE = 'giftcard';

	/** @var \Magento\GiftCardAccount\Model\Giftcardaccount */
	protected $_giftcardaccount;

	/**
     * Giftcard constructor.
     * @param \Magento\Sales\Model\Order $order
     * @param \Radial\FraudInsight\Helper\Data $dataHelper
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\GiftCardAccount\Model\Giftcardaccount $giftcardaccount
     */
	public function __construct(
	    \Magento\Sales\Model\Order $order,
        \Radial\FraudInsight\Helper\Data $dataHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\GiftCardAccount\Model\Giftcardaccount $giftcardaccount
    ) {
	    $this->_giftcardaccount = $giftcardaccount;
        parent::__construct($order, $dataHelper, $encryptor);
    }

	protected function _initialize()
	{
		$this->setExtractCardHolderName(null)
			->setExtractPaymentAccountUniqueId($this->_getBase64HashGiftcardCode())
			->setExtractIsToken(static::IS_TOKEN)
			->setExtractPaymentAccountBin($this->_getGiftcardAccountBin())
			->setExtractExpireDate($this->_getGiftCardExpireDate())
			->setExtractCardType($this->_helper->getPaymentMethodValueFromMap(static::PAYMENT_METHOD_TYPE))
			->setExtractTransactionResponses(array());
		return $this;
	}

	/**
	 * Get the raw gift card code from a passed in gift card data.
	 *
	 * @return string | null
	 */
	protected function _getRawGiftCardCode()
	{
		$cards = $this->_dataHelper->getGiftCard($this->_order);
		$card = count($cards) ? $cards[0] : array();
		return $this->_nullCoalesce($card, 'c', null);
	}

	/**
	 * @return string | null
	 */
	protected function _getBase64HashGiftcardCode()
	{
		$rawGiftCardCode = $this->_getRawGiftCardCode();
		return $rawGiftCardCode ? $this->_dataHelper->hashAndEncodeCc($rawGiftCardCode) : null;
	}

	/**
	 * @return string | null
	 */
	protected function _getGiftcardAccountBin()
	{
		$rawGiftCardCode = $this->_getRawGiftCardCode();
		return $this->_dataHelper->getFirstSixChars($rawGiftCardCode);
	}

	/**
	 * @return string | null
	 */
	protected function _getGiftCardExpireDate()
	{
		$this->_giftcardaccount->loadByCode($this->_getRawGiftCardCode());
		return $this->_giftcardaccount->getId()
			? $this->_extractGiftCardExpireDate() : null;
	}

	/**
	 * @return string
	 */
	protected function _extractGiftCardExpireDate()
	{
		$expireDate = $this->_getNewDateTimeOfGiftCardExpireDate();
		return $expireDate->format('Y-m');
	}

	/**
	 * @return DateTime
	 */
	protected function _getNewDateTimeOfGiftCardExpireDate()
	{
		return new \DateTime($this->_giftcardaccount->getDateExpires());
	}
}
