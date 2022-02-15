<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * OfflinePayments Observer
 */
namespace Nochex\Nochexapc\Observer;

use Magento\Framework\Event\ObserverInterface;
use Nochexapc\Nochex\Model\Nochex;

class BeforeOrderPaymentSaveObserver implements ObserverInterface
{
    /**
     * Sets current instructions for bank transfer account
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
    }
}
