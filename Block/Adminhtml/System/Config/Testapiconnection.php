<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml API connection test block
 */

namespace Radial\FraudInsight\Block\Adminhtml\System\Config;

class Testapiconnection extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * API Store ID Field Name
     *
     * @var string
     */
    protected $_apiStoreId = 'fraudinsight_api_setting_store_id';

    /**
     * API URL Field Name
     *
     * @var string
     */
    protected $_apiUrl = 'fraudinsight_api_setting_api_url';

    /**
     * API Key Field Name
     *
     * @var string
     */
    protected $_apiKey = 'fraudinsight_api_setting_api_key';

    /**
     * Test API Connection Button Label
     *
     * @var string
     */
    protected $_testButtonLabel = 'Test API Connection';

    /**
     * Set API Store ID Field Name
     *
     * @param string $apiStoreId
     * @return \Radial\FraudInsight\Block\Adminhtml\System\Config\Testapiconnection
     */
    public function setApiStoreIdField($apiStoreId)
    {
        $this->_apiStoreId = $apiStoreId;
        return $this;
    }

    /**
     * Get API Store ID Field Name
     *
     * @return string
     */
    public function getApiStoreIdField()
    {
        return $this->_apiStoreId;
    }

    /**
     * Set API URL Field Name
     *
     * @param string $apiUrlField
     * @return \Radial\FraudInsight\Block\Adminhtml\System\Config\Testapiconnection
     */
    public function setApiUrlField($apiUrlField)
    {
        $this->_apiUrl = $apiUrlField;
        return $this;
    }

    /**
     * Get API URL Field Name
     *
     * @return string
     */
    public function getApiUrlField()
    {
        return $this->_apiUrl;
    }

    /**
     * Set API URL Field Name
     *
     * @param string $apiKeyField
     * @return \Radial\FraudInsight\Block\Adminhtml\System\Config\Testapiconnection
     */
    public function setApiKeyField($apiKeyField)
    {
        $this->_apiKey = $apiKeyField;
        return $this;
    }

    /**
     * Get API Key Field Name
     *
     * @return string
     */
    public function getApiKeyField()
    {
        return $this->_apiKey;
    }

    /**
     * Set Test API Connection Button Label
     *
     * @param string $testButtonLabel
     * @return \Radial\FraudInsight\Block\Adminhtml\System\Config\Testapiconnection
     */
    public function setTestButtonLabel($testButtonLabel)
    {
        $this->_testButtonLabel = $testButtonLabel;
        return $this;
    }

    /**
     * Set template to itself
     *
     * @return \Radial\FraudInsight\Block\Adminhtml\System\Config\Testapiconnection
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/testapiconnection.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_testButtonLabel;
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('fraudinsight/system_config/testApiConnection'),
            ]
        );

        return $this->_toHtml();
    }
}