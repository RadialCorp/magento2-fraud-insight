<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Build;

interface FeedbackRequestInterface
{
	const CHARGEBACK_CODE = 'false';
	const CHARGEBACK_FLAG_DESCRIPTION = 'No charge back';

    /**
     * Build the Fraud Insight feedback request payload.
     *
     * @return \Radial_FraudInsight_Sdk_IPayload
     */
    public function build();
}
