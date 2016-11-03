<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Model;

abstract class AbstractModel
{
    /**
     * Instantiate new SDK class.
     *
     * @param  string
     * @param  mixed
     * @return mixed
     */
    protected function _getNewSdkInstance($class, $arguments=[])
    {
        return new $class($arguments);
    }
}