<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Controller\Adminhtml\Sales\Order;

class ReloadComment extends \Magento\Sales\Controller\Adminhtml\Order
{
    public function execute()
    {
        $this->_initOrder();
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->createBlock('Magento\Sales\Block\Adminhtml\Order\View\History');
        return $resultLayout;
    }
}