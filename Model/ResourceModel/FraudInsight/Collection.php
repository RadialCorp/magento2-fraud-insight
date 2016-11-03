<?php
/**
 * Copyright © 2016 Radial Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\ResourceModel\FraudInsight;

/**
 * Fraud Insight collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Radial\FraudInsight\Model\FraudInsight', 'Radial\FraudInsight\Model\ResourceModel\FraudInsight');
    }
}
