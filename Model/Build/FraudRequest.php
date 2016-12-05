<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Model\Build;

use Radial\FraudInsight\Model\Build\FraudInsightInterface;
use Magento\Framework\ObjectManagerInterface;

class FraudRequest
	implements FraudRequestInterface
{
    /** @var \Radial_FraudInsight_Sdk_IPayload */
	protected $_request;
    /** @var \Radial\FraudInsight\Model\FraudInsight */
	protected $_fraudInsight;
	/** @var \Magento\Sales\Model\Order */
	protected $_order;
    /** @var \Magento\Quote\Model\Quote */
	protected $_quote;
    /** @var \Radial\FraudInsight\Helper\Data */
	protected $_dataHelper;
	/** @var \Radial\FraudInsight\Helper\Config */
	protected $_configHelper;
    /** @var \Magento\Catalog\Model\Product */
	protected $_product;
    /** @var ObjectManagerInterface */
    protected $_objectManager;
	/** @var string */
	protected $_shippingId;
	/** @var string */
	protected $_billingId;

    /**
     * FraudRequest constructor.
     * @param \Radial_FraudInsight_Sdk_IPayload $request
     * @param \Radial\FraudInsight\Model\FraudInsight $fraudInsight
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Catalog\Model\Product $product
     * @param ObjectManagerInterface $objectManager
     * @param \Radial\FraudInsight\Helper\Data $dataHelper
     */
    public function __construct(
        \Radial_FraudInsight_Sdk_IPayload $request,
        \Radial\FraudInsight\Model\FraudInsight $fraudInsight,
        \Magento\Sales\Model\Order $order,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Radial\FraudInsight\Helper\Data $dataHelper
    ) {
	    $this->_request = $request;
        $this->_fraudInsight = $fraudInsight;
        $this->_order = $order;
        $this->_quote = $quote;
        $this->_product = $product;
        $this->_objectManager = $objectManager;
        $this->_dataHelper = $dataHelper;
    }

	public function build()
	{
		$this->_buildRequest();
		return $this->_request;
	}

	/**
	 * @return string
	 */
	protected function _getOrderSource()
	{
		return $this->_dataHelper->getOrderSource() ?: $this->_dataHelper->getOrderSourceByArea($this->_order);
	}

	/**
	 * @return string | null
	 */
	protected function _getPaymentTransactionDate()
	{
		$quote = $this->_quote->loadByIdWithoutStore($this->_order->getQuoteId());
		return $quote->getId() ? $this->_getPaymentCreatedDate($quote) : null;
	}

	/**
     * @param \Magento\Quote\Model\Quote $quote
     * @return string | null
     */
	protected function _getPaymentCreatedDate(\Magento\Quote\Model\Quote $quote)
	{
		$payment = $quote->getPayment();
		return $payment ? $payment->getCreatedAt() : null;
	}

	/**
	 * @return array
	 */
	protected function _getHttpHeaders()
	{
		$headers = $this->_fraudInsight->getHttpHeaders();
		return $headers ? json_decode($headers, true) : array();
	}

	/**
	 * @return string | null
	 */
	protected function _getShippingId()
	{
		if (!$this->_shippingId) {
			$shippingAddress = $this->_order->getShippingAddress();
			$this->_shippingId = $shippingAddress ? $shippingAddress->getId() : null;
		}
		return $this->_shippingId;
	}

	/**
	 * @return string | null
	 */
	protected function _getBillingId()
	{
		if (!$this->_billingId) {
			$this->_billingId = $this->_order->getBillingAddress()->getId();
		}
		return $this->_billingId;
	}

	/**
	 * @param  \Magento\Framework\Model\AbstractModel
	 * @return string | null
	 */
	protected function _getItemCategory(\Magento\Framework\Model\AbstractModel $item)
	{
		$product = $this->_product->load($item->getProductId());
		return $product->getId() ? $this->_getCategoryName($product) : null;
	}

	/**
	 * Get category collection.
	 *
	 * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
	 */
	protected function _getCategoryCollection()
	{
	    return $this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Category\Collection')
            ->addAttributeToSelect(\Magento\Catalog\Model\Category::KEY_NAME);
	}

	/**
	 * @param  \Magento\Framework\Model\AbstractModel
	 * @return string | null
	 */
	protected function _getCategoryName(\Magento\Framework\Model\AbstractModel $product)
	{
		$categoryName = '';
		$categories = $product->getCategoryCollection();
		$collection = $this->_getCategoryCollection();
		foreach ($categories as $category) {
			$pathArr = explode('/', $category->getPath());
			array_walk($pathArr, function(&$val) use ($collection) {
				$part = $collection->getItemById((int) $val);
				$val = $part ? $part->getName() : null;
			});
			$catString = implode('->', array_filter($pathArr));
			if ($catString) {
				$categoryName .= $this->_getCategoryDelimiter($categoryName) . $catString;
			}
		}
		return $categoryName;
	}

	/**
     * @param $categoryName
     * @return string
     */
	protected function _getCategoryDelimiter($categoryName)
	{
		return $categoryName ? ',' : '';
	}

	/**
	 * @return self
	 */
	protected function _buildRequest()
	{
		$this->_request->setPrimaryLangId($this->_dataHelper->getLanguageCode());
		$this->_buildOrder($this->_request->getOrder());
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_IOrder
	 * @return self
	 */
	protected function _buildOrder(\Radial_FraudInsight_Sdk_IOrder $subPayloadOrder)
	{
		$subPayloadOrder->setOrderId($this->_order->getIncrementId())
			->setOrderSource($this->_getOrderSource())
			->setOrderDate($this->_dataHelper->getNewDateTime($this->_order->getCreatedAt()))
			->setStoreId($this->_dataHelper->getStoreId());

        $this->_buildShippingList($subPayloadOrder->getShippingList())
            ->_buildLineItems($subPayloadOrder->getLineItems())
            ->_buildFormOfPayments($subPayloadOrder->getFormOfPayments())
            ->_buildTotalCost($subPayloadOrder->getTotalCost())
            ->_buildDeviceInfo($subPayloadOrder->getDeviceInfo());
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_Shipping_IList
	 * @return self
	 */
	protected function _buildShippingList(\Radial_FraudInsight_Sdk_Shipping_IList $subPayloadShippingList)
	{
		$shipments = $this->_getOrderShippingData();
		foreach ($shipments as $shipment) {
			$subPayloadShipment = $subPayloadShippingList->getEmptyShipment();
			$this->_buildShipment($subPayloadShipment, $shipment['address'], $shipment['type']);
			$subPayloadShippingList->offsetSet($subPayloadShipment);
		}
		return $this;
	}

	/**
	 * When the order is virtual simply return virtual shipment data otherwise
	 * find out if the order has any items that are virtual to return a combination
	 * of both virtual and physical shipment data. However, if the order only
	 * has physical items simply return physical shipment data.
	 *
	 * @return array
	 */
	protected function _getOrderShippingData()
	{
		return $this->_order->getIsVirtual()
			? $this->_getVirtualOrderShippingData()
			: $this->_getPhysicalVirtualShippingData();
	}

	/**
	 * Determine if the order has an virtual items, if so,
	 * simply return a combination of physical and virtual shipment
	 * data. Otherwise, simply return physical shipment data.
	 *
	 * @return array
	 */
	protected function _getPhysicalVirtualShippingData()
	{
		return $this->_hasVirtualItems()
			? array_merge($this->_getPhysicalOrderShippingData(), $this->_getVirtualOrderShippingData())
			: $this->_getPhysicalOrderShippingData();
	}

	/**
	 * Returns virtual shipment data.
	 *
	 * @return array
	 */
	protected function _getVirtualOrderShippingData()
	{
		return array(array(
			'type' => static::VIRTUAL_SHIPMENT_TYPE,
			'address' => $this->_order->getBillingAddress(),
		));
	}

	/**
	 * Returns physical shipment data.
	 *
	 * @return array
	 */
	protected function _getPhysicalOrderShippingData()
	{
		return array(array(
			'type' => static::PHYSICAL_SHIPMENT_TYPE,
			'address' => $this->_order->getShippingAddress(),
		));
	}

	/**
	 * Returns true when the item is virtual otherwise false.
	 *
	 * @param  \Magento\Sales\Model\Order\Item
	 * @return bool
	 */
	protected function _isItemVirtual(\Magento\Sales\Model\Order\Item $item)
	{
		return ((int) $item->getIsVirtual() === 1);
	}

	/**
	 * Returns true when the passed in type is a physical shipment type
	 * otherwise false.
	 *
	 * @param  string
	 * @return bool
	 */
	protected function _isVirtualShipmentType($type)
	{
		return ($type !== static::PHYSICAL_SHIPMENT_TYPE);
	}

	/**
	 * Returns true if any items in the order is virtual, otherwise,
	 * return false.
	 *
	 * @return bool
	 */
	protected function _hasVirtualItems()
	{
		$hasVirtual = false;
		foreach ($this->_order->getAllItems() as $orderItem) {
			if ($this->_isItemVirtual($orderItem)) {
				$hasVirtual = true;
				break;
			}
		}
		return $hasVirtual;
	}

	/**
	 * Returns the billing id if the item is virtual otherwise returns
	 * the shipping id.
	 *
	 * @param  \Magento\Sales\Model\Order\Item
	 * @return string
	 */
	protected function _getShipmentIdByItem(\Magento\Sales\Model\Order\Item $item)
	{
		return $this->_isItemVirtual($item) ? $this->_getBillingId() : $this->_getShippingId();
	}

	/**
	 * Returns the virtual shipping method when the types is a virtual shipment
	 * otherwise returns the shipping method in the order.
	 *
	 * @param  string
	 * @return string
	 */
	protected function _getShippingMethodByType($type)
	{
		return $this->_isVirtualShipmentType($type)
			? static::VIRTUAL_SHIPPING_METHOD
			: $this->_order->getShippingMethod();
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_Line_IItems
	 * @return self
	 */
	protected function _buildLineItems(\Radial_FraudInsight_Sdk_Line_IItems $subPayloadLineItems)
	{
		foreach ($this->_order->getAllVisibleItems() as $orderItem) {
			$subPayloadLineItem = $subPayloadLineItems->getEmptyLineItem();
			$this->_buildLineItem($subPayloadLineItem, $orderItem);
			$subPayloadLineItems->offsetSet($subPayloadLineItem);
		}
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_IPayments
	 * @return self
	 */
	protected function _buildFormOfPayments(\Radial_FraudInsight_Sdk_IPayments $subPayloadFormOfPayments)
	{
		$orderBillingAddress = $this->_order->getBillingAddress();
		$orderPayment = $this->_order->getPayment();
		if ($orderBillingAddress && $orderPayment) {
			$subPayloadPayment = $subPayloadFormOfPayments->getEmptyPayment();
			$this->_buildPayment($subPayloadPayment, $orderBillingAddress, $orderPayment);
			$subPayloadFormOfPayments->offsetSet($subPayloadPayment);
		}
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_ITotal
	 * @return self
	 */
	protected function _buildTotalCost(\Radial_FraudInsight_Sdk_ITotal $subPayloadTotalCost)
	{
		$subPayloadCostTotals = $subPayloadTotalCost->getCostTotals();
		$subPayloadCostTotals->setCurrencyCode($this->_order->getBaseCurrencyCode())
			->setAmountBeforeTax($this->_order->getSubtotal())
			->setAmountAfterTax($this->_order->getGrandTotal());
		$subPayloadTotalCost->setCostTotals($subPayloadCostTotals);
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_Device_IInfo
	 * @return self
	 */
	protected function _buildDeviceInfo(\Radial_FraudInsight_Sdk_Device_IInfo $subPayloadDeviceInfo)
	{
		$subPayloadDeviceInfo->setDeviceIP($this->_order->getRemoteIp());
		$this->_buildHttpHeaders($subPayloadDeviceInfo->getHttpHeaders());
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_IShipment
	 * @param  \Magento\Customer\Model\Address\AddressModelInterface
	 * @param  string
	 * @return self
	 */
	protected function _buildShipment(
		\Radial_FraudInsight_Sdk_IShipment $subPayloadShipment,
        \Magento\Customer\Model\Address\AddressModelInterface $orderShippingAddress,
		$type
	)
	{
		$subPayloadShipment->setShipmentId($orderShippingAddress->getId())
			->setShippingMethod($this->_getShippingMethodByType($type));

		$this->_buildPersonName($subPayloadShipment->getPersonName(), $orderShippingAddress);
		if ($this->_isVirtualShipmentType($type)) {
			$subPayloadShipment->setEmail($this->_order->getCustomerEmail());
		} else {
			$this->_buildTelephone($subPayloadShipment->getTelephone(), $orderShippingAddress)
				->_buildAddress($subPayloadShipment->getAddress(), $orderShippingAddress);
		}
		return $this;
	}

	/**
     * @param \Radial_FraudInsight_Sdk_Person_IName $subPayloadPersonName
     * @param \Magento\Customer\Model\Address\AddressModelInterface $orderAddress
     * @return self
     */
	protected function _buildPersonName(
		\Radial_FraudInsight_Sdk_Person_IName $subPayloadPersonName,
        \Magento\Customer\Model\Address\AddressModelInterface $orderAddress
	)
	{
		$subPayloadPersonName->setLastName($orderAddress->getFirstname())
			->setMiddleName($orderAddress->getMiddlename())
			->setFirstName($orderAddress->getLastname());
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_ITelephone
	 * @param  \Magento\Customer\Model\Address\AddressModelInterface
	 * @return self
	 */
	protected function _buildTelephone(
		\Radial_FraudInsight_Sdk_ITelephone $subPayloadTelephone,
        \Magento\Customer\Model\Address\AddressModelInterface $orderAddress
	)
	{
		$subPayloadTelephone->setCountryCode(null)
			->setAreaCode(null)
			->setNumber($orderAddress->getTelephone())
			->setExtension(null);
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_IAddress
	 * @param  \Magento\Customer\Model\Address\AddressModelInterface
	 * @return self
	 */
	protected function _buildAddress(
		\Radial_FraudInsight_Sdk_IAddress $subPayloadAddress,
        \Magento\Customer\Model\Address\AddressModelInterface $orderAddress
	)
	{
		$subPayloadAddress->setLineA($orderAddress->getStreetLine(1))
			->setLineB($orderAddress->getStreetLine(2))
			->setLineC($orderAddress->getStreetLine(3))
			->setLineD($orderAddress->getStreetLine(4))
			->setCity($orderAddress->getCity())
			->setPostalCode($orderAddress->getPostcode())
			->setMainDivisionCode($orderAddress->getRegionCode())
			->setCountryCode($orderAddress->getCountryId());
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_Line_IItem
	 * @param  \Magento\Framework\Model\AbstractModel
	 * @return self
	 */
	protected function _buildLineItem(
		\Radial_FraudInsight_Sdk_Line_IItem $subPayloadLineItem,
		\Magento\Framework\Model\AbstractModel $orderItem
	)
	{
		$subPayloadLineItem->setLineItemId($orderItem->getId())
			->setShipmentId($this->_getShipmentIdByItem($orderItem))
			->setProductId($orderItem->getSku())
			->setDescription(htmlspecialchars($orderItem->getName(), ENT_QUOTES | ENT_XML1. 'UTF-8'))
			->setUnitCost($orderItem->getPrice())
			->setUnitCurrencyCode($this->_order->getBaseCurrencyCode())
			->setQuantity((int) $orderItem->getQtyOrdered())
			->setCategory($this->_getItemCategory($orderItem))
			->setPromoCode($this->_order->getCouponCode());
		return $this;
	}

	/**
	 * @return \Radial\FraudInsight\Model\Payment\AdapterInterface
	 */
	protected function _getPaymentAdapter()
	{
		return $this->_objectManager->create(
		    '\Radial\FraudInsight\Model\Payment\Adapter',
            ['order' => $this->_order]
        );
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_IPayment
	 * @param  \Magento\Customer\Model\Address\AddressModelInterface
	 * @param  \Magento\Sales\Model\Order\Payment
	 * @return self
	 */
	protected function _buildPayment(
		\Radial_FraudInsight_Sdk_IPayment $subPayloadPayment,
        \Magento\Customer\Model\Address\AddressModelInterface $orderBillingAddress,
		\Magento\Sales\Model\Order\Payment $orderPayment
	)
	{
		$subPayloadPayment->setEmail($this->_order->getCustomerEmail())
			->setPaymentTransactionDate($this->_dataHelper->getNewDateTime($this->_getPaymentTransactionDate()))
			->setCurrencyCode($this->_order->getBaseCurrencyCode())
			->setAmount($orderPayment->getAmountAuthorized())
			->setTotalAuthAttemptCount(null);

		$paymentAdapterType = $this->_getPaymentAdapter()->getAdapter();
		$this->_buildPaymentCard($subPayloadPayment->getPaymentCard(), $paymentAdapterType)
			->_buildPersonName($subPayloadPayment->getPersonName(), $orderBillingAddress)
			->_buildTelephone($subPayloadPayment->getTelephone(), $orderBillingAddress)
			->_buildAddress($subPayloadPayment->getAddress(), $orderBillingAddress)
			->_buildTransactionResponses($subPayloadPayment->getTransactionResponses(), $paymentAdapterType);
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_Payment_ICard
	 * @param  \Radial\FraudInsight\Model\Payment\Adapter\TypeInterface
	 * @return self
	 */
	protected function _buildPaymentCard(
		\Radial_FraudInsight_Sdk_Payment_ICard $subPayloadCard,
        \Radial\FraudInsight\Model\Payment\Adapter\TypeInterface $paymentAdapterType
	)
	{
		$subPayloadCard->setCardHolderName($paymentAdapterType->getExtractCardHolderName())
			->setPaymentAccountUniqueId($paymentAdapterType->getExtractPaymentAccountUniqueId())
			->setIsToken($paymentAdapterType->getExtractIsToken())
			->setPaymentAccountBin($paymentAdapterType->getExtractPaymentAccountBin())
			->setExpireDate($paymentAdapterType->getExtractExpireDate())
			->setCardType($paymentAdapterType->getExtractCardType());
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_Transaction_IResponses
	 * @param  \Radial\FraudInsight\Model\Payment\Adapter\TypeInterface
	 * @return self
	 */
	protected function _buildTransactionResponses(
		\Radial_FraudInsight_Sdk_Transaction_IResponses $subPayloadResponses,
		\Radial\FraudInsight\Model\Payment\Adapter\TypeInterface $paymentAdapterType
	)
	{
		$transactionResponses = (array)$paymentAdapterType->getExtractTransactionResponses();
		foreach ($transactionResponses as $transaction) {
			$subPayloadResponse = $subPayloadResponses->getEmptyTransactionResponse();
			$this->_buildTransactionResponse($subPayloadResponse, $transaction['response'], $transaction['type']);
			$subPayloadResponses->offsetSet($subPayloadResponse);
		}
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_Transaction_IResponse
	 * @param  string
	 * @param  string
	 * @return self
	 */
	protected function _buildTransactionResponse(
		\Radial_FraudInsight_Sdk_Transaction_IResponse $subPayloadResponse,
		$response,
		$type
	)
	{
        $subPayloadResponse->setResponse($response)
			->setResponseType($type);
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_Http_IHeaders
	 * @return self
	 */
	protected function _buildHttpHeaders(\Radial_FraudInsight_Sdk_Http_IHeaders $subPayloadHttpHeaders)
	{
		foreach ($this->_getHttpHeaders() as $name => $message) {
            $message = htmlentities($message, ENT_QUOTES, 'UTF-8');
			$subPayloadHttpHeader = $subPayloadHttpHeaders->getEmptyHttpHeader();
			$this->_buildHttpHeader($subPayloadHttpHeader, $name, $message);
			$subPayloadHttpHeaders->offsetSet($subPayloadHttpHeader);
		}
		return $this;
	}

	/**
	 * @param  \Radial_FraudInsight_Sdk_Http_IHeader
	 * @param  string
	 * @param  string
	 * @return self
	 */
	protected function _buildHttpHeader(
	    \Radial_FraudInsight_Sdk_Http_IHeader $subPayloadHttpHeader,
        $name,
        $message
    ) {
		$subPayloadHttpHeader->setHeader($message)
			->setName($name);
		return $this;
	}
}
