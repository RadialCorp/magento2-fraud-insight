<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Payment\Adapter;

class PurchaseOrder
	extends \Radial\FraudInsight\Model\Payment\Adapter\Type
{
	protected function _initialize()
	{
		$this->_payment = $this->_order->getPayment();
		$this->setExtractCardHolderName(null)
			->setExtractPaymentAccountUniqueId($this->_getAccountUniqueId())
			->setExtractIsToken(static::IS_TOKEN)
			->setExtractPaymentAccountBin($this->_getAccountBin())
			->setExtractExpireDate(null)
			->setExtractCardType($this->_getPaymentCardType())
			->setExtractTransactionResponses(array());
		return $this;
	}

    /**
     * Get the purchase order account unique id
     * @return null|string
     */
	protected function _getAccountUniqueId()
	{
        return $this->_payment->getPoNumber() ?
            $this->_dataHelper->hashAndEncodeCc($this->_payment->getPoNumber()) : null;
	}

    /**
     * Get the purchase order account bin
     * @return null|string
     */
	protected function _getAccountBin()
    {
        return $this->_payment->getPoNumber() ?
            $this->_dataHelper->getFirstSixChars($this->_payment->getPoNumber()) : null;
    }
}
