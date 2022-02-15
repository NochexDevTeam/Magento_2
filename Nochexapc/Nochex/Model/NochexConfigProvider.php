<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Nochexapc\Nochex\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class NochexConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = Nochex::PAYMENT_METHOD_NOCHEX_CODE;

    /**
     * @var Checkmo
     */
    protected $method;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper
    ) {
        $this->escaper = $escaper;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                'nochex' => [
                    'mailingAddress' => $this->getMailingAddress(),
                    'payableTo' => $this->getPayableTo(),
                ],
            ],
        ] : []; 
    }

    /**
     * Get mailing address from config
     *
     * @return string
     */
    protected function getMailingAddress()
    {
        return nl2br($this->escaper->escapeHtml($this->method->getMailingAddress()));
    }

    /**
     * Get payable to from config
     *
     * @return string
     */
    protected function getPayableTo()
    {
        return $this->method->getPayableTo();
    }
	
	 /**xmlCollect
     * Get payable to from config
     *
     * @return string
     */
    protected function getTestTransaction()
    {
        return $this->method->getTestTransaction();
    }
	
		 /**
     * Get payable to from config
     *
     * @return string
     */
    protected function getXmlCollect()
    {
        return $this->method->getXmlCollect();
    }
	
}
