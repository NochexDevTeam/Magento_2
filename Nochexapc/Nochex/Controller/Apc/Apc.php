<?php
/**
 * Copyright © 2015 Inchoo d.o.o.
 * created by Zoran Salamun(zoran.salamun@inchoo.net)
 */
namespace Nochexapc\Nochex\Controller\Apc;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\CsrfAwareActionInterface;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\RemoteServiceUnavailableException;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
    
class Apc extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;
   /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

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

    /**
     * Instantiate IPN model and pass IPN request to it
     *
     * @return void
     */
    public function execute()
    {
				
			if ($this->getRequest()->getParam('optional_1') <> ""){
			
			$order = $this->_objectManager->create('Magento\Sales\Model\Order');
		
			$order_id = $this->getRequest()->getParam('optional_1');
			$orderinc_id = $this->getRequest()->getParam('order_id');
			if($this->getRequest()->getParam("transaction_status") == "100"){
				$testStatus = "Test"; 
			}else{
				$testStatus = "Live";
			}
				
			$transaction_id = $this->getRequest()->getParam('transaction_id');
			$transaction_date = $this->getRequest()->getParam('transaction_date');
			$amount = $this->getRequest()->getParam('amount'); 
	
			//$data = $this->getRequest()->getPostValue();
			$data = $this->getRequest()->getParams();
			
			// Get the POST information from Nochex server
			$postvars = http_build_query($data);
				
			// Set parameters for the email
			$url = "https://secure.nochex.com/callback/callback.aspx";
			
			//// Curl code to post variables back
			$ch = curl_init(); // Initialise the curl tranfer
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postvars);
			curl_setopt ($ch, CURLOPT_SSLVERSION,6);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$output = curl_exec($ch); // Post back
			curl_close($ch);
			
			$orders = $order->loadByIncrementId($orderinc_id);	
				
			if (!strstr($output, "AUTHORISED")) {  // searches response to see if AUTHORISED is present if it isn’t a failure message is displayed
			
				$msg = "Callback was not AUTHORISED, this was a " . $testStatus . ", and the transaction id for this transaction is: ".$transaction_id;
				
				$payment = $orders->getPayment();
				$payment->setPreparedMessage($output)
						->setTransactionId($transaction_id)
						->setParentTransactionId($transaction_id)
						->setCurrencyCode("GB")
						->setIsTransactionClosed(1);
				
				$orders->addStatusToHistory("processing", $msg, false);
				$orders->save();
				
			} else { 
				
				$msg = "Callback was AUTHORISED, this was a " . $testStatus . ", and the transaction id for this transaction is: ".$transaction_id;
				
				$payment = $orders->getPayment();
				$payment->setPreparedMessage($output)
						->setTransactionId($transaction_id)
						->setParentTransactionId($transaction_id)
						->setCurrencyCode("GB")
						->setIsTransactionClosed(1);
				
				$orders->addStatusToHistory("processing", $msg, false);
				$orders->save();
				
				$invoice = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($orders);
				$invoice->register();
				$invoice->save();
				
				$emailSender = $this->_objectManager->create('Magento\Sales\Model\Order\Email\Sender\OrderSender');
				$emailSender->send($orders);
			}
			
			} else {
			
			$order = $this->_objectManager->create('Magento\Sales\Model\Order');
		
			$order_id = $this->getRequest()->getParam('custom');
			$orderinc_id = $this->getRequest()->getParam('order_id');
			$testStatus = $this->getRequest()->getParam('status');
				
			$transaction_id = $this->getRequest()->getParam('transaction_id');
			$transaction_date = $this->getRequest()->getParam('transaction_date');
			$amount = $this->getRequest()->getParam('amount'); 
	
			//$data = $this->getRequest()->getPostValue();
			$data = $this->getRequest()->getParams();
			
			// Get the POST information from Nochex server
			$postvars = http_build_query($data);
				
			// Set parameters for the email
			$url = "https://www.nochex.com/apcnet/apc.aspx";
			
			//// Curl code to post variables back
			$ch = curl_init(); // Initialise the curl tranfer
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postvars);
			curl_setopt ($ch, CURLOPT_SSLVERSION,6);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$output = curl_exec($ch); // Post back
			curl_close($ch);
			
			$orders = $order->loadByIncrementId($orderinc_id);	
				
			if (!strstr($output, "AUTHORISED")) {  // searches response to see if AUTHORISED is present if it isn’t a failure message is displayed
			
				$msg = "APC was not AUTHORISED, this was a " . $testStatus . ", and the transaction id for this transaction is: ".$transaction_id;
				
				$payment = $orders->getPayment();
				$payment->setPreparedMessage($output)
						->setTransactionId($transaction_id)
						->setParentTransactionId($transaction_id)
						->setCurrencyCode("GB")
						->setIsTransactionClosed(1);
				
				$orders->addStatusToHistory("processing", $msg, false);
				$orders->save();
				
			} else { 
				
				$msg = "APC was AUTHORISED, this was a " . $testStatus . ", and the transaction id for this transaction is: ".$transaction_id;
				
				$payment = $orders->getPayment();
				$payment->setPreparedMessage($output)
						->setTransactionId($transaction_id)
						->setParentTransactionId($transaction_id)
						->setCurrencyCode("GB")
						->setIsTransactionClosed(1);
				
				$orders->addStatusToHistory("processing", $msg, false);
				$orders->save();
				
				$invoice = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($orders);
				$invoice->register();
				$invoice->save();
				
				$emailSender = $this->_objectManager->create('Magento\Sales\Model\Order\Email\Sender\OrderSender');
				$emailSender->send($orders);
				
			}
		}
	}
	
}