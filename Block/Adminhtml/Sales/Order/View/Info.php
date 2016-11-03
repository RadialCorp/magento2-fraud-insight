<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Block\Adminhtml\Sales\Order\View;

class Info extends \Magento\Sales\Block\Adminhtml\Order\View\Info
{
    /** @var \Radial\FraudInsight\Model\FraudInsightFactory */
    protected $_fraudInsightFactory;

    /**
     * Info constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Customer\Api\CustomerMetadataInterface $metadata
     * @param \Magento\Customer\Model\Metadata\ElementFactory $elementFactory
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param array $data
     * @param \Radial\FraudInsight\Model\FraudInsightFactory $fraudInsightFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\CustomerMetadataInterface $metadata,
        \Magento\Customer\Model\Metadata\ElementFactory $elementFactory,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        array $data = [],
        \Radial\FraudInsight\Model\FraudInsightFactory $fraudInsightFactory
    ) {
        $this->_fraudInsightFactory = $fraudInsightFactory;
        parent::__construct($context, $registry, $adminHelper, $groupRepository, $metadata, $elementFactory, $addressRenderer, $data);
    }

    /**
     * Retrieve the fraud insight object for an order
     *
     * @return object
     */
    public function getFraudInsight()
    {
        $orderIncrementId = $this->getOrder()->getIncrementId();
        return $this->_fraudInsightFactory->create()->load($orderIncrementId);
    }
}