<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FraudInsight extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('radial_fraud_insight', 'id');
    }

    /**
     * Load an object using 'order_increment_id' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    /*public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'order_increment_id';
        }

        return parent::load($object, $value, $field);
    }*/
}