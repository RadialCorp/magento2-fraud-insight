<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Payment;

interface AdapterInterface
{
	const DEFAULT_ADAPTER = '\Radial\FraudInsight\Model\Payment\Adapter\DefaultType';
	const GIFT_CARD_PAYMENT_METHOD = 'giftcard';

	/**
	 * @return \Radial\FraudInsight\Model\Payment\AdapterInterface
	 */
	public function getAdapter();
}
