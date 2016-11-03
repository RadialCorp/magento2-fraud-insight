<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Payment\Adapter;

class Checkmo
	extends \Radial\FraudInsight\Model\Payment\Adapter\Type
{
	protected function _initialize()
	{
        $this->_payment = $this->_order->getPayment();
		$this->setExtractCardHolderName(null)
			->setExtractPaymentAccountUniqueId(null)
			->setExtractIsToken(static::IS_NOT_TOKEN)
			->setExtractPaymentAccountBin(null)
			->setExtractExpireDate(null)
			->setExtractCardType($this->_getPaymentCardType())
			->setExtractTransactionResponses(array());
		return $this;
	}
}
