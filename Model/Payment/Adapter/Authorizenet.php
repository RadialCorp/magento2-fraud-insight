<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Payment\Adapter;

class Authorizenet
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
			->setExtractPaymentAccountUniqueId(null)
			->setExtractIsToken(static::IS_NOT_TOKEN)
			->setExtractPaymentAccountBin(null)
			->setExtractExpireDate($this->_getPaymentExpireDate())
			->setExtractCardType($this->_getPaymentCardType())
			->setExtractTransactionResponses(array());
		return $this;
	}

    /**
     * Get authorize net card expiry date
     * @return null|string
     */
	protected function _getPaymentExpireDate()
    {
        $expiryDate = null;
        if (isset($this->_additionalInfo['cc_details']) && !empty($this->_additionalInfo['cc_details'])) {
            $expiryDate = sprintf('%d-%02d',
                $this->_additionalInfo['cc_details']['cc_exp_year'],
                $this->_additionalInfo['cc_details']['cc_exp_month']);
        }
        return $expiryDate;
    }

    /**
     * Get authorize net card type
     * @return string
     */
    protected function _getPaymentCardType()
    {
        if (isset($this->_additionalInfo['cc_details']) && !empty($this->_additionalInfo['cc_details'])) {
            $ccType = $this->_additionalInfo['cc_details']['cc_type'];
        } else {
            $ccType = $this->_payment->getMethod();
        }
        return $this->_getMapFraudInsightPaymentMethod($ccType);
    }
}
