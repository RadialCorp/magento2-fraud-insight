<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Controller\Adminhtml\Sales\Order;

class SendChargebackFeedback extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Send the chargeback feedback
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {
                $data = $this->getRequest()->getPost('chargeback');
                if (!$data['has_chargeback']) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Please select a chargeback.'));
                }
                if (empty($data['code'])) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a chargeback code.'));
                }
                $fraudInsight = $this->_objectManager->create('Radial\FraudInsight\Model\FraudInsight')
                    ->load($order->getIncrementId());
                $chargeback = $this->_objectManager->create('Radial\FraudInsight\Model\FraudInsight\Chargeback', ['data' => $data]);
                $chargeback->sendFraudOrderChargeback($fraudInsight, $order);

                $this->messageManager->addSuccess(__('Chargeback feedback has successfully submitted.'));
                return $this->resultPageFactory->create();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => __('We cannot send the chargeback feedback.')];
            }
        }
        if (is_array($response)) {
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData($response);
            return $resultJson;
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }
}