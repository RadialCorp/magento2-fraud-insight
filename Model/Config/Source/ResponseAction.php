<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Model\Config\Source;

class ResponseAction implements \Magento\Framework\Option\ArrayInterface
{
    const UNDEFINED_OPTION_LABEL = '-- Please Select --';
    const ACTION_PROCESS = 'process';
    const ACTION_HOLD = 'hold';
    const ACTION_CANCEL = 'cancel';

    /**
     * @var string[]
     */
    protected $_responseActions = [
        self::ACTION_PROCESS,
        self::ACTION_HOLD,
        self::ACTION_CANCEL,
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [['value' => '', 'label' => __('-- Please Select --')]];
        foreach ($this->_responseActions as $responseAction) {
            $options[] = ['value' => $responseAction, 'label' => ucfirst($responseAction)];
        }
        return $options;
    }
}