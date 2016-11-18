<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Controller\Adminhtml\Sales\Order;

use Magento\Sales\Controller\Adminhtml\Order;

class FraudCheck extends Order
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Radial_FraudInsight::fraud_check';

    /**
     * Fraud check action
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
//        if (!$this->isValidPostRequest()) {
//            $this->messageManager->addError(__('Fraud Insight request could not be sent.'));
//            return $resultRedirect->setPath('sales/*/');
//        }
        $order = $this->_initOrder();
        if ($order) {
            try {
                $fraudInsight = $this->_objectManager->create('Radial\FraudInsight\Model\FraudInsight')
                    ->load($order->getIncrementId());
                $fraudCheck = $this->_objectManager->create('Radial\FraudInsight\Model\FraudInsight\FraudCheck');
                $fraudCheck->processFraudOrder($fraudInsight, $order);

                $this->messageManager->addSuccess(__('You have successfully completed fraud insight for this order.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Fraud Insight request could not be sent.'));
            }
            return $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
        }
        return $resultRedirect->setPath('sales/*/');
    }

}