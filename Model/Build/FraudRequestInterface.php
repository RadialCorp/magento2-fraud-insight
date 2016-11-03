<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Model\Build;

interface FraudRequestInterface
{
	const RESPONSE_TYPE             = 'avs';
	const DEFAULT_SHIPPING_METHOD   = 'Unknown';
	const PHYSICAL_SHIPMENT_TYPE    = 'physical';
	const VIRTUAL_SHIPMENT_TYPE     = 'virtual';
	const VIRTUAL_SHIPPING_METHOD   = 'EMAIL';

	/**
	 * Build the Fraud Insight request payload.
	 *
	 * @return \Radial_FraudInsight_Sdk_IPayload
	 */
	public function build();
}
