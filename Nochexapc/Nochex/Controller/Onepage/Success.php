<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Nochexapc\Nochex\Controller\Onepage;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\PaymentException;
use Nochex\Nochexapc\Model\Nochex;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;

class Success extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Order success action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

	$storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
	$checkout = $this->_objectManager->create('Magento\Checkout\Model\Session');
	
	$order = $this->_objectManager->get('Magento\Sales\Model\Order');
	
	$ordered = $checkout->getLastRealOrder();
	$order_info = $ordered->getData();

	$order_dets = $order->loadByIncrementId($order_info["increment_id"]);	
	
	$orderInfo = $order_dets->getData();
	
	$order_pay = $order->getPayment();
	$order_paymentMethod = $order_pay->getData();
	
	$successURL = $storeManager->getStore()->getBaseUrl() . "checkout/";	
	
	if($order_paymentMethod["method"] == "nochex" & $orderInfo['status'] == "pending" & $_SERVER['HTTP_REFERER'] == $successURL){
	
		return $this->resultRedirectFactory->create()->setPath('nochex/success/success/');
		
	}else{
        $session = $this->getOnepage()->getCheckout();
        if (!$this->_objectManager->get('Magento\Checkout\Model\Session\SuccessValidator')->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        $session->clearQuote();
        //@todo: Refactor it to match CQRS
        $resultPage = $this->resultPageFactory->create();
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            ['order_ids' => [$session->getLastOrderId()]]
        );
			
		return $resultPage;
	}
		
    }
}
