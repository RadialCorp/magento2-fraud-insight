<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Block\Adminhtml\Sales\Order;

/**
 * FraudInsight adminhtml sales order view
 * @package Radial\FraudInsight\Block\Adminhtml\Sales\Order
 */
class View extends \Magento\Sales\Block\Adminhtml\Order\View
{
    /**
     * @var \Radial\FraudInsight\Helper\Data
     */
    protected $_fraudInsightHelper;

    /**
     * View constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Sales\Helper\Reorder $reorderHelper
     * @param array $data
     * @param \Radial\FraudInsight\Helper\Data $fraudInsightHelper
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        array $data,
        \Radial\FraudInsight\Helper\Data $fraudInsightHelper
    ) {
        $this->_fraudInsightHelper = $fraudInsightHelper;
        parent::__construct($context, $registry, $salesConfig, $reorderHelper, $data);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();

        // fraud insight check action
        if ($this->_fraudInsightHelper->isFraudInsightEnabled()
            && $this->_isAllowedAction('Radial_FraudInsight::fraud_check')
            && $this->_canCheckOrderForFraud()
        ) {
            $message = __('Are you sure you want to send a fraud insight request for this order?');
            $this->buttonList->add(
                'order_fraud_check',
                [
                    'label' => __('Fraud Check'),
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getFraudCheckUrl()}')",
//                    'onclick' => 'setLocation(\'' . $this->getFraudCheckUrl() . '\')',
                    'class' => 'fraud_check',
                ]
            );
        }

    }

    /**
     * Fraud Check URL getter
     *
     * @return string
     */
    public function getFraudCheckUrl()
    {
        return $this->getUrl('fraudinsight/sales_order/fraudCheck');
    }

    /**
     * Check if order can process through fraud insight
     *
     * @return bool
     */
    public function _canCheckOrderForFraud()
    {
        $order = $this->getOrder();
        $orderState = $order->getState();
        if ($orderState == \Magento\Sales\Model\Order::STATE_CANCELED
            || $orderState == \Magento\Sales\Model\Order::STATE_CLOSED
            || $orderState == \Magento\Sales\Model\Order::STATE_COMPLETE
        ) {
            return false;
        }

        return !($this->_fraudInsightHelper->getIsOrderFraudChecked($order->getIncrementId()));
    }
}