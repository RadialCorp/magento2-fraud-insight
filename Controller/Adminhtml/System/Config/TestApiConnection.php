<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Radial\FraudInsight\Model\FraudInsight\ValidateConnection;

/**
 * Fraud Insight Test API connection controller
 */
class TestApiConnection extends \Magento\Backend\App\Action
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Check if the API connection is successful
     *
     * @return $this
     */
    public function execute()
    {
        //$result = $this->_validateConnection();

        // TODO: Implement execute() method.
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'valid' => (int)true,//$result->getIsValid(),
            'message' => 'Success',//$result->getRequestMessage(),
        ]);
    }

    /**
     * Perform the test for API connection
     *
     * @return $this
     */
    protected function _validateConnection()
    {
        return $this->_objectManager->get('Radial\FraudInsight\Model\FraudInsight\ValidateConnection')
            ->testApiConnection(
                $this->getRequest()->getParam('apiUrl'),
                $this->getRequest()->getParam('apiKey'),
                $this->getRequest()->getParam('apiStoreId')
            );

    }
}