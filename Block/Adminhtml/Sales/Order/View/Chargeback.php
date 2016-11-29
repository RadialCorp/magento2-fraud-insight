<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Block\Adminhtml\Sales\Order\View;

/**
 * Order fraud insight chargeback block
 */
class Chargeback extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $onclick = "submitChargebackAndReloadArea($('order_chargeback_block').parentNode, '" . $this->getSubmitUrl() . "')";
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Submit Chargeback Feedback'), 'class' => 'action-save action-secondary', 'onclick' => $onclick, 'disabled' => 'disabled']
        );
        $this->setChild('submit_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * Retrieve order model
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }

    /**
     * Check allow to send chargeback feedback
     *
     * @return bool
     */
    public function canSubmitChargeback()
    {
        return $this->_authorization->isAllowed('Radial_FraudInsight::send_chargeback_feedback');
    }

    /**
     * Submit URL getter
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('fraudinsight/sales_order/sendChargebackFeedback', ['order_id' => $this->getOrder()->getId()]);
    }

    /**
     * Reload Comment URL getter
     *
     * @return string
     */
    public function getReloadCommentUrl()
    {
        return $this->getUrl('fraudinsight/sales_order/reloadComment', ['order_id' => $this->getOrder()->getId()]);
    }

    public function getYesNoOptions()
    {
        return [
            '1' => __('Yes'),
            '0' => __('No'),
        ];
    }

    /**
     * Check if order is in state to send the chargeback feedback
     *
     * @return bool
     */
    protected function _isValidForChargeback()
    {
        return $this->getOrder()->getState() === \Magento\Sales\Model\Order::STATE_COMPLETE
            || $this->getOrder()->getState() === \Magento\Sales\Model\Order::STATE_CANCELED;
    }
}