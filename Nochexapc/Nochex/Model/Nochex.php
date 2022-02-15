<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Nochexapc\Nochex\Model;

/**
 * Class Checkmo
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 */
class Nochex extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_NOCHEX_CODE = 'nochex';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_NOCHEX_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = 'Nochexapc\Nochex\Block\Form\Nochex';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Nochexapc\Nochex\Block\Info\Nochex';
    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseInternal = false;
    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseCheckout = true;
    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = false;
    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canOrder = true;
    /**
     * @return string
     */
    public function getPayableTo()
    {
        return $this->getConfigData('payable_to');
    }

    /**
     * @return string
     */
    public function getMailingAddress()
    {
        return $this->getConfigData('mailing_address');
    }
	
	/**xmlCollect
     * @return string
     */
    public function getTestTransaction()
    {
        return $this->getConfigData('testTransaction');
    }
	
	/**
     * @return string
     */
    public function getXmlCollect()
    {
        return $this->getConfigData('xmlCollect');
    }
	
	public function getCheckoutRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('Nochex/success/success');
    }
	
	 public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }
	
}
