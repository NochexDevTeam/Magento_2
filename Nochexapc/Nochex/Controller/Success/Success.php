<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Nochexapc\Nochex\Controller\Success;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\CsrfAwareActionInterface;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\RemoteServiceUnavailableException;

use Magento\Sales\Model\OrderFactory;

#[AllowDynamicProperties]
class Success extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    public $method;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\HTTP\PhpEnvironment\Request $RequestHttp
    ) {
        parent::__construct($context, $RequestHttp);
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
	
    public function execute()
    {
	
	$checkout = $this->_objectManager->create('Magento\Checkout\Model\Session');
	$storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
	$PaymentHelper = $this->_objectManager->get('\Magento\Payment\Helper\Data');
	
	$this->method = $PaymentHelper->getMethodInstance("nochex");
	
	$merchantId = $this->method->getPayableTo();
	
	if ($this->method->getTestTransaction() == 1){
		$testTran = "100";
	} else {
		$testTran = "0";
	}
	
	$testTransaction = $testTran;
		
	$callbackURL = $storeManager->getStore()->getBaseUrl() . "nochex/apc/apc/";
	$successURL = $storeManager->getStore()->getBaseUrl() . "checkout/onepage/success/";
	$cancel_url = $storeManager->getStore()->getBaseUrl(); 
		
	$order = $this->_objectManager->get('Magento\Sales\Model\Order');
	
	$ordered = $checkout->getLastRealOrder();
	$order_info = $ordered->getData();
	
	$order_dets = $order->loadByIncrementId($order_info["increment_id"]);	
	
	$orderInfo = $order_dets->getData();
	
	$order_billing = $order->getBillingAddress();
	$billing_address = $order_billing->getData();
	$order_shipping = $order->getShippingAddress();
	$shipping_address = $order_shipping->getData();
	
	$orderItems = $order->getAllItems();
	
	$description = "";
	
	$xmlCollection = "<items>";
	
	foreach ($orderItems as $item) {
	
		$product = $item->getData();
		
		$description .= $product['name'] . ", " . number_format($product['qty_ordered'], 0, '', ''). " * " . number_format($product['base_row_total'], 2, '.', ''). ", " . $product['description'];
	
		$xmlCollection .= "<item><id>".$product['product_id']."</id><name>".$product['name']."</name><description>".$product['description']."</description><quantity>".number_format($product['qty_ordered'], 0, '', '')."</quantity><price>".number_format($product['base_row_total'], 2, '.', '')."</price></item>";
	
	}
	
	$xmlCollection .= "</items>";
	
	$xml = $this->method->getXmlCollect();
		
	if($xml == 1){
	
		$description = "Order created for ".$order_info['increment_id']."";
	
	}else{
	
		$xmlCollection = "";
	
	}
	
	
	if($order_info["status"] == "pending"){

	echo"<script>window.onload = function(){
	  document.forms['co-transparent-form'].submit();
	}</script>
	<form class=\"form\" id=\"co-transparent-form\" action=\"https://secure.nochex.com/default.aspx\" method=\"post\">   
		<input name=\"merchant_id\" type=\"hidden\" value=\"".$merchantId."\"/>
		<input name=\"optional_1\" type=\"hidden\" value=\"". $order_info["entity_id"] ."\" />
		<input name=\"order_id\" type=\"hidden\" value=\"". $order_info["increment_id"] ."\" />
		<input name=\"test_transaction\" type=\"hidden\" value=\"".$testTransaction."\"/>
		<input name=\"test_success_url\" type=\"hidden\" value=\"".$successURL."\"/>
		<input name=\"success_url\" type=\"hidden\" value=\"".$successURL."\"/>
		<input name=\"callback_url\" type=\"hidden\" value=\"".$callbackURL."\"/>
		<input name=\"cancel_url\" type=\"hidden\" value=\"".$cancel_url."\"/>
		<input name=\"amount\" type=\"hidden\" value=\"".number_format($order_info["grand_total"], 2, '.', '')."\" />
		<input name=\"billing_fullname\" type=\"hidden\" value=\"".$billing_address["firstname"].", ".$billing_address["lastname"]."\" /> 
		<input name=\"billing_address\" type=\"hidden\" value=\"".$billing_address["street"]."\" />
		<input name=\"billing_city\" type=\"hidden\" value=\"".$billing_address["city"]."\" />
		<input name=\"billing_postcode\" type=\"hidden\" value=\"".$billing_address["postcode"]."\" />
		<input name=\"customer_phone_number\" type=\"hidden\" value=\"".preg_replace("/[^0-9]/", "", $billing_address["telephone"])."\" />
		<input name=\"email_address\" type=\"hidden\" value=\"".$billing_address["email"]."\" />		
		<input name=\"delivery_fullname\" type=\"hidden\" value=\"".$shipping_address["firstname"].", ".$shipping_address["lastname"]."\" /> 
		<input name=\"delivery_address\" type=\"hidden\" value=\"".$shipping_address["street"]."\" />
		<input name=\"delivery_city\" type=\"hidden\" value=\"".$shipping_address["city"]."\" />
		<input name=\"delivery_postcode\" type=\"hidden\" value=\"".$shipping_address["postcode"]."\" />
		<input name=\"description\" type=\"hidden\" value=\"". $description ."\" />
		<input name=\"xml_item_collection\" type=\"hidden\" value=\"". $xmlCollection ."\" />
		<button type=\"submit\">
          <span>Place Order</span>
        </button>
        </form>";
		}
		
    }
	

}
