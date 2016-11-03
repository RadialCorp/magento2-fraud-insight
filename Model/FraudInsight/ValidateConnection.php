<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Model\FraudInsight;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class ValidateConnection extends \Radial\FraudInsight\Model\AbstractModel
{
    const FRAUD_INSIGHT_REQUEST = <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<RiskInsightRequest xmlns="http://schema.gsicommerce.com/risk/insight/1.0/">
	<PrimaryLangId>en</PrimaryLangId>
	<Order>
		<OrderId>100000000</OrderId>
		<OrderSource>WEBSTORE</OrderSource>
		<OrderDate>2015-04-20T15:53:15+00:00</OrderDate>
		<StoreId>MAGTEST</StoreId>
		<ShippingList>
			<Shipment ShipmentId="913">
				<PersonName>
					<LastName>Scenario</LastName>
					<FirstName>One</FirstName>
				</PersonName>
				<Telephone>
					<Number>555-555-5555</Number>
				</Telephone>
				<Address>
					<Line1>630 Allendale Rd</Line1>
					<City>KING OF PRUSSIA</City>
					<PostalCode>19406-1342</PostalCode>
					<MainDivisionCode>PA</MainDivisionCode>
					<CountryCode>US</CountryCode>
				</Address>
				<ShippingMethod>ups_GND</ShippingMethod>
			</Shipment>
		</ShippingList>
		<LineItems>
			<LineItem LineItemId="878" ShipmentId="913">
				<ProductId>hdd005</ProductId>
				<Description>Fragrance Diffuser Reeds</Description>
				<UnitCost>86.42</UnitCost>
				<UnitCurrencyCode>USD</UnitCurrencyCode>
				<Quantity>1</Quantity>
				<Category>Root Catalog-&gt;Default Category-&gt;Home &amp; Decor-&gt;Decorative Accents</Category>
			</LineItem>
		</LineItems>
		<FormOfPayments>
			<FormOfPayment>
				<PaymentCard>
					<CardHolderName>Scenario One</CardHolderName>
					<PaymentAccountUniqueId isToken="true">aL+zlvNa84dvxQlmWz3COgkwqrE=</PaymentAccountUniqueId>
					<PaymentAccountBin>411111</PaymentAccountBin>
					<ExpireDate>2023-09</ExpireDate>
					<CardType>VC</CardType>
				</PaymentCard>
				<PersonName>
					<LastName>Scenario</LastName>
					<FirstName>One</FirstName>
				</PersonName>
				<Email>EMAIL.IP.CC@EBAY.COM</Email>
				<Telephone>
					<Number>555-555-5555</Number>
				</Telephone>
				<Address>
					<Line1>630 Allendale Rd</Line1>
					<City>KING OF PRUSSIA</City>
					<PostalCode>19406-1342</PostalCode>
					<MainDivisionCode>PA</MainDivisionCode>
					<CountryCode>US</CountryCode>
				</Address>
				<PaymentTransactionDate>2015-04-20T15:52:33+00:00</PaymentTransactionDate>
				<CurrencyCode>USD</CurrencyCode>
			</FormOfPayment>
		</FormOfPayments>
		<TotalCost>
			<CostTotals>
				<CurrencyCode>USD</CurrencyCode>
				<AmountBeforeTax>86.42</AmountBeforeTax>
				<AmountAfterTax>99.96</AmountAfterTax>
			</CostTotals>
		</TotalCost>
		<DeviceInfo>
			<DeviceIP>172.17.42.1</DeviceIP>
			<HttpHeaders>
				<HttpHeader name="Authorization"/>
				<HttpHeader name="Host">digi-ucp.com</HttpHeader>
				<HttpHeader name="User-Agent">Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:37.0) Gecko/20100101 Firefox/37.0</HttpHeader>
				<HttpHeader name="Accept">text/javascript, text/html, application/xml, text/xml, */*</HttpHeader>
				<HttpHeader name="Accept-Language">en-US,en;q=0.5</HttpHeader>
				<HttpHeader name="Accept-Encoding">gzip, deflate</HttpHeader>
				<HttpHeader name="X-Requested-With">XMLHttpRequest</HttpHeader>
				<HttpHeader name="X-Prototype-Version">1.7</HttpHeader>
				<HttpHeader name="Referer">http://digi-ucp.com/checkout/onepage/</HttpHeader>
				<HttpHeader name="Cookie">adminhtml=d8f1f4f496267a9259e4304e893eecba; frontend=1a481226b2df9dcbcb7ad1257782f99a</HttpHeader>
				<HttpHeader name="Connection">keep-alive</HttpHeader>
				<HttpHeader name="Pragma">no-cache</HttpHeader>
				<HttpHeader name="Cache-Control">no-cache</HttpHeader>
			</HttpHeaders>
		</DeviceInfo>
	</Order>
</RiskInsightRequest>
EOF;

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager;
    /** @var \Radial\FraudInsight\Helper\Data */
    protected $_helper;
    /** @var LoggerInterface */
    protected $_logger;

    /**
     * ValidateConnection constructor.
     * @param ObjectManagerInterface $objectManager
     * @param \Radial\FraudInsight\Helper\Data $helper
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Radial\FraudInsight\Helper\Data $helper
    ) {
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        $this->_logger = $this->_helper->getLogger();
    }

    public function testApiConnection($storeId, $apiUrl, $apiKey)
    {
        // Default response
        $gatewayResponse = new DataObject([
            'is_valid' => false,
            'request_date' => '',
            'request_identifier' => '',
            'request_success' => false,
            'request_message' => __('Error during API Connection verification.'),
        ]);

        if (!extension_loaded('curl')) {
            $this->logger->critical(new LocalizedException(__('PHP CURL extension is required.')));
            return $gatewayResponse;
        }

        if (!$this->_canTestApiConnection($storeId, $apiUrl, $apiKey)) {
            return $gatewayResponse;
        }

        try {
            $request = $this->_loadRequest();
            $apiConfig = $this->_setupApiConfig($request, $this->_getNewEmptyResponse());
            $response = $this->_sendRequest($this->_getApi($apiConfig));

            if ($response) {
                $this->_logger->debug($response->serialize());
                $gatewayResponse->setIsValid(true);
                $gatewayResponse->setRequestMessage(__('API Connection is successful.'));
            } else {
                $gatewayResponse->setRequestMessage(__('Please enter a valid API credentials.'));
            }
        } catch (\Exception $e) {
            $gatewayResponse->setIsValid(false);
            $gatewayResponse->setRequestMessage(__('API Connection failed'));
            $logMessage = sprintf('[%s] Api Connection Error: %s', __CLASS__, $e->getMessage());
            $this->_logger->critical($logMessage);
        }

        return $gatewayResponse;
    }

    protected function _canTestApiConnection($storeId, $apiUrl, $apiKey)
    {
        return !(
            !is_string($storeId) || !is_string($apiUrl) || !is_string($apiKey)
            || empty($storeId) || empty($apiUrl || empty($apiKey))
        );
    }

    /**
     * Get new API config object.
     *
     * @param \Radial_FraudInsight_Sdk_IPayload $request
     * @param \Radial_FraudInsight_Sdk_IPayload $response
     * @return \Radial_FraudInsight_Sdk_IConfig
     */
    protected function _setupApiConfig(
        \Radial_FraudInsight_Sdk_IPayload $request,
        \Radial_FraudInsight_Sdk_IPayload $response
    ) {
        return $this->_getNewSdkInstance(
            'Radial_FraudInsight_Sdk_Config',
            [
                'api_key'   => $this->_helper->getApiKey(),
                'host'      => $this->_helper->getApiHostname(),
                'store_id'  => $this->_helper->getStoreId(),
                'request'   => $request,
                'response'  => $response,
            ]);
    }

    /**
     * @param \Radial_FraudInsight_Sdk_IApi $api
     * @return \Radial_FraudInsight_Sdk_IPayload
     */
    protected function _sendRequest(\Radial_FraudInsight_Sdk_IApi $api)
    {
        $response = null;
        try {
            $api->send();
            $response = $api->getResponseBody();
        } catch (\Exception $e) {
            $logMessage = sprintf('[%s] The following error has occurred while sending request: %s', __CLASS__, $e->getMessage());
            $this->_helper->getLogger()->warning($logMessage);
        }
        return $response;
    }

    /**
     * @return \Radial_FraudInsight_Sdk_IPayload
     */
    protected function _loadRequest()
    {
        $request = $this->_getNewEmptyRequest();
        $request->deserialize(static::FRAUD_INSIGHT_REQUEST);
        return $request;
    }
}