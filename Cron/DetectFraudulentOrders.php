<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Cron;

class DetectFraudulentOrders
{
    /**
     * @var \Radial\FraudInsight\Model\ResourceModel\FraudInsight\CollectionFactory
     */
    protected $_collectionFactory;
    /**
     * @var \Radial\FraudInsight\Model\FraudInsight\FraudCheckFactory
     */
    protected $_fraudCheckFactory;
    /**
     * @var \Radial\FraudInsight\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Detect Fraudulent Orders constructor.
     * @param \Radial\FraudInsight\Model\ResourceModel\FraudInsight\CollectionFactory $collectionFactory
     * @param \Radial\FraudInsight\Model\FraudInsight\FraudCheckFactory $fraudCheckFactory
     * @param \Radial\FraudInsight\Helper\Data $dataHelper
     */
    public function __construct(
        \Radial\FraudInsight\Model\ResourceModel\FraudInsight\CollectionFactory $collectionFactory,
        \Radial\FraudInsight\Model\FraudInsight\FraudCheckFactory $fraudCheckFactory,
        \Radial\FraudInsight\Helper\Data $dataHelper
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_fraudCheckFactory = $fraudCheckFactory;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * Process mass orders for fraud insight
     */
    public function execute()
    {
        if (!$this->_dataHelper->isFraudInsightEnabled()) {
            return $this;
        }

        try {
            /** @var $collection \Radial\FraudInsight\Model\ResourceModel\FraudInsight\Collection */
            $collection = $this->_collectionFactory->create();
            $collection->addFieldToFilter('is_request_sent', 0);
            $collection->getSelect()
                ->joinInner(
                    ['order_table' => $collection->getTable('sales_order')],
                    'main_table.order_increment_id=order_table.increment_id',
                    null)
                ->where('order_table.state<>\'complete\' OR order_table.state<>\'canceled\'');

            if (count($collection) > 0) {
                $this->_fraudCheckFactory->create()->massProcessFraudOrder($collection);
            }
        } catch (\Exception $e) {
            $this->_dataHelper->getLogger()->critical(__CLASS__ . $e->getMessage());
        }

        return $this;
    }
}