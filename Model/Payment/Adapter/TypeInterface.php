<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Payment\Adapter;

interface TypeInterface
{
	const IS_TOKEN = 'true';
	const IS_NOT_TOKEN = 'false';
    const DEFAULT_PAYMENT_METHOD = 'OTHER';

	/**
	 * @return string | null
	 */
	public function getExtractCardHolderName();

	/**
	 * @param  string
	 * @return self
	 */
	public function setExtractCardHolderName($cardHolderName);

	/**
	 * @return string | null
	 */
	public function getExtractPaymentAccountUniqueId();

	/**
	 * @param  string
	 * @return self
	 */
	public function setExtractPaymentAccountUniqueId($paymentAccountUniqueId);

	/**
	 * @return string | null
	 */
	public function getExtractPaymentAccountBin();

	/**
	 * @param  string
	 * @return self
	 */
	public function setExtractPaymentAccountBin($paymentAccountBin);

	/**
	 * @return string | null
	 */
	public function getExtractExpireDate();

	/**
	 * @param  string
	 * @return self
	 */
	public function setExtractExpireDate($expireDate);

	/**
	 * @return string | null
	 */
	public function getExtractCardType();

	/**
	 * @param  string
	 * @return self
	 */
	public function setExtractCardType($cardType);

	/**
	 * @return array
	 */
	public function getExtractTransactionResponses();

	/**
	 * @param  array
	 * @return self
	 */
	public function setExtractTransactionResponses(array $transactionResponses);

	/**
	 * @return string | null
	 */
	public function getExtractIsToken();

	/**
	 * @param  string
	 * @return self
	 */
	public function setExtractIsToken($isToken);
}
