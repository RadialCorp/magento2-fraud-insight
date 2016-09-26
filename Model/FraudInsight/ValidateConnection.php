<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Model\FraudInsight;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class ValidateConnection
{
    const RISK_INSIGHT_REQUEST = <<<EOF
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

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var PsrLogger
     */
    protected $logger;

    /**
     * @param $objectManager $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->_objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
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

        if (!$this->canTestApiConnection($storeId, $apiUrl, $apiKey)) {
            return $gatewayResponse;
        }

        try {
            // @todo implement logic
            $gatewayResponse->setIsValid(true);

            if ($gatewayResponse->getIsValid()) {
                $gatewayResponse->setRequestMessage(__('API Connection is successful.'));
            } else {
                $gatewayResponse->setRequestMessage(__('Please enter a valid API credentials.'));
            }
        } catch (\Exception $exception) {
            $gatewayResponse->setIsValid(false);
            $gatewayResponse->setRequestDate('');
            $gatewayResponse->setRequestIdentifier('');
        }

        return $gatewayResponse;
    }

    public function canTestApiConnection($storeId, $apiUrl, $apiKey)
    {
        return !(
            !is_string($storeId) || !is_string($apiUrl) || !is_string($apiKey)
            || empty($storeId) || empty($apiUrl || empty($apiKey))
        );
    }
}