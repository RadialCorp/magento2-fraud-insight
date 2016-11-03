<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Payment\Adapter\Paypal;

class Express
	extends \Radial\FraudInsight\Model\Payment\Adapter\Type
{
    /**
     * @var Sales order payment additional information
     */
    protected $_additionalInfo;

	protected function _initialize()
	{
        $this->_payment = $this->_order->getPayment();
        $this->_additionalInfo = $this->_payment->getAdditionalInformation();

		$this->setExtractCardHolderName(null)
			->setExtractPaymentAccountUniqueId($this->_getAccountUniqueId())
			->setExtractIsToken(static::IS_NOT_TOKEN)
			->setExtractPaymentAccountBin(null)
			->setExtractExpireDate(null)
			->setExtractCardType($this->_getPaymentCardType())
			->setExtractTransactionResponses($this->_getPaypalTransactions());
		return $this;
	}

    /**
     * @return null|string
     */
	protected function _getAccountUniqueId()
    {
        return isset($this->_additionalInfo['paypal_payer_id']) ? $this->_additionalInfo['paypal_payer_id'] : null;
    }

    /**
     * @return array
     */
    protected function _getPaypalTransactions()
    {
        return array(
            ['type' => 'PayPalPayer', 'response' => strtolower($this->_additionalInfo['paypal_payer_status'])],
            ['type' => 'PayPalAddress', 'response' => strtolower($this->_additionalInfo['paypal_address_status'])],
        );
    }
}
