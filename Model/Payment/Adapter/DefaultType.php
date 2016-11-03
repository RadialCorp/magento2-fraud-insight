<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Payment\Adapter;

class DefaultType
	extends \Radial\FraudInsight\Model\Payment\Adapter\Type
{
	protected function _initialize()
	{
        $this->_payment = $this->_order->getPayment();
        $this->setExtractCardHolderName($this->_payment->getCcOwner())
            ->setExtractPaymentAccountUniqueId($this->_getAccountUniqueId())
            ->setExtractIsToken(static::IS_TOKEN)
            ->setExtractPaymentAccountBin($this->_getAccountBin())
            ->setExtractExpireDate($this->_getPaymentExpireDate())
            ->setExtractCardType($this->_getPaymentCardType())
            ->setExtractTransactionResponses(array());
		return $this;
	}
}
