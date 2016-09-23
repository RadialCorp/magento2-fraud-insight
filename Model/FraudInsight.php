<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class FraudInsight
{
    /**
     * Config path to Fraud Insight API settings
     */
    const XML_PATH_FRAUD_INSIGHT_STORE_ID   = 'fraundinsight/api_setting/store_id';
    const XML_PATH_FRAUD_INSIGHT_API_URL    = 'fraundinsight/api_setting/api_url';
    const XML_PATH_FRAUD_INSIGHT_API_KEY    = 'fraundinsight/api_setting/api_key';
    const XML_PATH_FRAUD_INSIGHT_TIMEOUT    = 'fraundinsight/api_setting/timeout';

    protected $scopeConfig;
    protected $logger;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }
}