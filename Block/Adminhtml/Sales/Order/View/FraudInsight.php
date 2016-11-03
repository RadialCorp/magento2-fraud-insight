<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Block\Adminhtml\Sales\Order\View;

use \Magento\Backend\Block\Template;

/**
 * Adminhtml sales order Fraud Insight information
 *
 * @package Radial\FraudInsight\Block\Adminhtml\Sales\Order
 */
class FraudInsight extends Template
{
    /**
     * Fraud Insight data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_fraudInsightData = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Radial\FraudInsight\Helper\Data $fraudInsightData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Radial\FraudInsight\Helper\Data $fraudInsightData,
        array $data = []
    ) {
        $this->_fraudInsightData = $fraudInsightData;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve required options from parent
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid parent block for this block'));
        }
        $this->setFraudInsight($this->getParentBlock()->getOrder()->getFraudInsight());
        parent::_beforeToHtml();
    }

    /**
     * Set fraud insight
     *
     * @param Info $fraudInsight
     * @return $this
     */
    public function setFraudInsight($fraudInsight)
    {
        $fraudInsightBlock = $this->_fraudInsightData->getInfoBlock($fraudInsight, $this->getLayout());
        $this->setChild('info', $fraudInsightBlock);
        $this->setData('fraudInsight', $fraudInsight);
        return $this;
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getChildHtml('info');
    }
}
