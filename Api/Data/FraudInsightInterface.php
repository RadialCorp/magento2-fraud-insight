<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Api\Data;

/**
 * Fraud Insight Interface
 * @api
 */
interface FraudInsightInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                        = 'id';
    const ORDER_INCREMENT_ID        = 'order_increment_id';
    const HTTP_HEADERS              = 'http_headers';
    const RESPONSE_CODE             = 'response_code';
    const RESPONSE_CODE_DESCRIPTION = 'response_code_description';
    const IS_REQUEST_SENT           = 'is_request_sent';
    const IS_FEEDBACK_SENT          = 'is_feedback_sent';
    const FEEDBACK_ATTEMPT_COUNT    = 'feedback_attempt_count';
    const ACTION_TAKEN_ACKNOWLEDGE  = 'action_taken_acknowledgement';
    const CHARGEBACK_ACKNOWLEDGE    = 'chargeback_acknowledgement';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get order increment ID
     *
     * @return string
     */
    public function getOrderIncrementId();

    /**
     * Get http headers
     *
     * @return string
     */
    public function getHttpHeaders();

    /**
     * Get response code
     *
     * @return string
     */
    public function getResponseCode();

    /**
     * Get response code description
     *
     * @return string
     */
    public function getResponseCodeDescription();

    /**
     * Is request sent
     *
     * @return bool
     */
    public function getIsRequestSent();

    /**
     * Is feedback sent
     *
     * @return bool|null
     */
    public function getIsFeedbackSent();

    /**
     * Get feedback request count
     *
     * @return int|null
     */
    public function getFeedbackAttemptCount();

    /**
     * Get action taken acknowledgement
     *
     * @return bool|null
     */
    public function getActionTakenAcknowledgement();

    /**
     * Get chargeback acknowledgement
     *
     * @return bool|null
     */
    public function getChargebackAcknowledgement();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setId($id);

    /**
     * Set order increment ID
     *
     * @param string $orderIncrementId
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setOrderIncrementId($orderIncrementId);

    /**
     * Set http headers
     *
     * @param string $httpHeaders
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setHttpHeaders($httpHeaders);

    /**
     * Set response code
     *
     * @param string $responseCode
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setResponseCode($responseCode);

    /**
     * Set response code description
     *
     * @param string $responseCodeDescription
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setResponseCodeDescription($responseCodeDescription);

    /**
     * Is request sent
     *
     * @param int|bool $isRequestSent
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setIsRequestSent($isRequestSent);

    /**
     * Is feedback sent
     *
     * @param int|bool $isFeedbackSent
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setIsFeedbackSent($isFeedbackSent);

    /**
     * Set feedback request count
     *
     * @param int $feedbackAttemptCount
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setFeedbackAttemptCount($feedbackAttemptCount);

    /**
     * Set action taken acknowledgement
     *
     * @param bool $actionTakenAcknowledgement
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setActionTakenAcknowledgement($actionTakenAcknowledgement);

    /**
     * Set chargeback acknowledgement
     *
     * @param bool $chargebackAcknowledgement
     * @return \Radial\FraudInsight\Api\Data\FraudInsightInterface
     */
    public function setChargebackAcknowledgement($chargebackAcknowledgement);
}