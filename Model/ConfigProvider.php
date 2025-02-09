<?php

namespace Radarsofthouse\Reepay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        LayoutInterface $layout,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Provide payment icons html
     *
     * @return array
     */
    public function getConfig()
    {
        $store_id = $this->_storeManager->getStore()->getId();
        return [
            'payment_icons' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\Paymenticons::class)
                ->setTemplate('Radarsofthouse_Reepay::payment_icons.phtml')->toHtml(),
            'viabill_payment_icons' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\Paymenticons::class)
                ->setTemplate('Radarsofthouse_Reepay::viabill_payment_icons.phtml')->toHtml(),
            'mobilepay_payment_icons' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\Paymenticons::class)
                ->setTemplate('Radarsofthouse_Reepay::mobilepay_payment_icons.phtml')->toHtml(),
            'applepay_payment_icons' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\Paymenticons::class)
                ->setTemplate('Radarsofthouse_Reepay::applepay_payment_icons.phtml')->toHtml(),
            'paypal_payment_icons' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\Paymenticons::class)
                ->setTemplate('Radarsofthouse_Reepay::paypal_payment_icons.phtml')->toHtml(),
            'klarnapaynow_payment_icons' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\Paymenticons::class)
                ->setTemplate('Radarsofthouse_Reepay::klarnapaynow_payment_icons.phtml')->toHtml(),
            'klarnapaylater_payment_icons' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\Paymenticons::class)
                ->setTemplate('Radarsofthouse_Reepay::klarnapaylater_payment_icons.phtml')->toHtml(),
            'klarnasliceit_payment_icons' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\Paymenticons::class)
                ->setTemplate('Radarsofthouse_Reepay::klarnasliceit_payment_icons.phtml')->toHtml(),
            'swish_payment_icons' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\Paymenticons::class)
                ->setTemplate('Radarsofthouse_Reepay::swish_payment_icons.phtml')->toHtml(),
            'resurs_payment_icons' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\Paymenticons::class)
                ->setTemplate('Radarsofthouse_Reepay::resurs_payment_icons.phtml')->toHtml(),
            'vipps_payment_icons' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\Paymenticons::class)
                ->setTemplate('Radarsofthouse_Reepay::vipps_payment_icons.phtml')->toHtml(),
            'forbrugsforeningen_payment_icons' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\Paymenticons::class)
                ->setTemplate('Radarsofthouse_Reepay::forbrugsforeningen_payment_icons.phtml')->toHtml(),
            'googlepay_payment_icons' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\Paymenticons::class)
                ->setTemplate('Radarsofthouse_Reepay::googlepay_payment_icons.phtml')->toHtml(),
            'saved_credit_cards' => $this->_layout->createBlock(\Radarsofthouse\Reepay\Block\SavedCreditCards::class)
                ->setTemplate('Radarsofthouse_Reepay::saved_credit_cards.phtml')->toHtml(),
            "reepay_payment_instructions" => $this->_scopeConfig->getValue(
                'payment/reepay_payment/instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            ),
            "reepay_applepay_instructions" => $this->_scopeConfig->getValue(
                'payment/reepay_applepay/instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            ),
            "reepay_mobilepay_instructions" => $this->_scopeConfig->getValue(
                'payment/reepay_mobilepay/instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            ),
            "reepay_klarnapaynow_instructions" => $this->_scopeConfig->getValue(
                'payment/reepay_klarnapaynow/instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            ),
            "reepay_klarnapaylater_instructions" => $this->_scopeConfig->getValue(
                'payment/reepay_klarnapaylater/instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            ),
            "reepay_klarnasliceit_instructions" => $this->_scopeConfig->getValue(
                'payment/reepay_klarnasliceit/instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            ),
            "reepay_swish_instructions" => $this->_scopeConfig->getValue(
                'payment/reepay_swish/instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            ),
            "reepay_resurs_instructions" => $this->_scopeConfig->getValue(
                'payment/reepay_resurs/instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            ),
            "reepay_vipps_instructions" => $this->_scopeConfig->getValue(
                'payment/reepay_vipps/instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            ),
            "reepay_forbrugsforeningen_instructions" => $this->_scopeConfig->getValue(
                'payment/reepay_forbrugsforeningen/instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            ),
            "reepay_viabill_instructions" => $this->_scopeConfig->getValue(
                'payment/reepay_viabill/instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            ),
            "reepay_paypal_instructions" => $this->_scopeConfig->getValue(
                'payment/reepay_paypal/instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            ),
            "reepay_googlepay_instructions" => $this->_scopeConfig->getValue(
                'payment/reepay_googlepay/instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store_id
            )
        ];
    }
}
