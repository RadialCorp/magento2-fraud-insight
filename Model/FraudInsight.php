<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Model;

use Radial\FraudInsight\Api\Data\FraudInsightInterface;

class FraudInsight extends \Magento\Framework\Model\AbstractModel implements FraudInsightInterface
{
    /**
     * Config path to Fraud Insight API settings
     */
    const XML_PATH_FRAUD_INSIGHT_STORE_ID   = 'fraundinsight/api_setting/store_id';
    const XML_PATH_FRAUD_INSIGHT_API_URL    = 'fraundinsight/api_setting/api_url';
    const XML_PATH_FRAUD_INSIGHT_API_KEY    = 'fraundinsight/api_setting/api_key';
    const XML_PATH_FRAUD_INSIGHT_TIMEOUT    = 'fraundinsight/api_setting/timeout';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Radial\FraudInsight\Model\ResourceModel\FraudInsight');
    }

    /**
     * Load an object using 'order_increment_id' field if there's no field specified and value is not numeric
     *
     * @param mixed $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if (!is_int($id) && is_null($field)) {
            $field = 'order_increment_id';
        }

        return parent::load($id, $field);
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get order increment id
     *
     * @return int
     */
    public function getOrderIncrementId()
    {
        return $this->getData(self::ORDER_INCREMENT_ID);
    }

    /**
     * Get http headers
     *
     * @return string
     */
    public function getHttpHeaders()
    {
        return $this->getData(self::HTTP_HEADERS);
    }

    /**
     * Get response code
     *
     * @return string
     */
    public function getResponseCode()
    {
        return $this->getData(self::RESPONSE_CODE);
    }

    /**
     * Get response code description
     *
     * @return string
     */
    public function getResponseCodeDescription()
    {
        return $this->getData(self::RESPONSE_CODE_DESCRIPTION);
    }

    /**
     * Is request sent
     *
     * @return bool
     */
    public function getIsRequestSent()
    {
        return (bool)$this->getData(self::IS_REQUEST_SENT);
    }

    /**
     * Is feedback sent
     *
     * @return bool
     */
    public function getIsFeedbackSent()
    {
        return (bool)$this->getData(self::IS_FEEDBACK_SENT);
    }

    /**
     * Get feedback request count
     *
     * @return int
     */
    public function getFeedbackAttemptCount()
    {
        return (int)$this->getData(self::FEEDBACK_ATTEMPT_COUNT);
    }

    /**
     * Get action taken acknowledgement
     *
     * @return bool
     */
    public function getActionTakenAcknowledgement()
    {
        return (bool)$this->getData(self::ACTION_TAKEN_ACKNOWLEDGE);
    }

    /**
     * Get chargeback acknowledgement
     *
     * @return bool
     */
    public function getChargebackAcknowledgement()
    {
        return (bool)$this->getData(self::CHARGEBACK_ACKNOWLEDGE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set order increment ID
     *
     * @param string $orderIncrementId
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * Set http headers
     *
     * @param string $httpHeaders
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setHttpHeaders($httpHeaders)
    {
        return $this->setData(self::HTTP_HEADERS, $httpHeaders);
    }

    /**
     * Set response code
     *
     * @param string $responseCode
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setResponseCode($responseCode)
    {
        return $this->setData(self::RESPONSE_CODE, $responseCode);
    }

    /**
     * Set response code description
     *
     * @param string $responseCodeDescription
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setResponseCodeDescription($responseCodeDescription)
    {
        return $this->setData(self::RESPONSE_CODE_DESCRIPTION, $responseCodeDescription);
    }

    /**
     * Is request sent
     *
     * @param int|bool $isRequestSent
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setIsRequestSent($isRequestSent)
    {
        return $this->setData(self::IS_REQUEST_SENT, $isRequestSent);
    }

    /**
     * Is feedback sent
     *
     * @param int|bool $isFeedbackSent
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setIsFeedbackSent($isFeedbackSent)
    {
        return $this->setData(self::IS_FEEDBACK_SENT, $isFeedbackSent);
    }

    /**
     * Set feedback request count
     *
     * @param int $feedbackAttemptCount
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setFeedbackAttemptCount($feedbackAttemptCount)
    {
        return $this->setData(self::FEEDBACK_ATTEMPT_COUNT, $feedbackAttemptCount);
    }

    /**
     * Set action taken acknowledgement
     *
     * @param bool $actionTakenAcknowledgement
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setActionTakenAcknowledgement($actionTakenAcknowledgement)
    {
        return $this->setData(self::ACTION_TAKEN_ACKNOWLEDGE, $actionTakenAcknowledgement);
    }

    /**
     * Set charge back acknowledgement
     *
     * @param bool $chargebackAcknowledgement
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setChargeBackAcknowledgement($chargebackAcknowledgement)
    {
        return $this->setData(self::CHARGEBACK_ACKNOWLEDGE, $chargebackAcknowledgement);
    }
}