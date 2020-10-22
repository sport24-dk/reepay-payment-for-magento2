<?php

namespace Radarsofthouse\Reepay\Controller\Standard;

use Magento\Framework\App\ResponseInterface;

/**
 * Class Accept
 *
 * @package Radarsofthouse\Reepay\Controller\Standard
 */
class Accept extends \Magento\Framework\App\Action\Action
{
    protected $_orderInterface;
    protected $_reepayCharge;
    protected $_reepaySession;
    protected $_logger;
    protected $_request;
    protected $_url;
    protected $_resultJsonFactory;
    protected $_reepayHelper;
    protected $_reepayStatus;
    protected $_priceHelper;
    protected $_orderSender;
    protected $_checkoutSession;
    protected $_reepayHelperEmail;
    protected $_reepayHelperSurchargeFee;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $_resultRedirectFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Sales\Api\Data\OrderInterface $orderInterface
     * @param \Radarsofthouse\Reepay\Helper\Charge $reepayCharge
     * @param \Radarsofthouse\Reepay\Helper\Session $reepaySession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Radarsofthouse\Reepay\Helper\Data $reepayHelper
     * @param \Radarsofthouse\Reepay\Helper\Email $reepayHelperEmail
     * @param \Radarsofthouse\Reepay\Helper\SurchargeFee $reepayHelperSurchargeFee
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     * @param \Radarsofthouse\Reepay\Model\Status $reepayStatus
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Radarsofthouse\Reepay\Helper\Charge $reepayCharge,
        \Radarsofthouse\Reepay\Helper\Session $reepaySession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Radarsofthouse\Reepay\Helper\Data $reepayHelper,
        \Radarsofthouse\Reepay\Helper\Email $reepayHelperEmail,
        \Radarsofthouse\Reepay\Helper\SurchargeFee $reepayHelperSurchargeFee,
        \Radarsofthouse\Reepay\Helper\Logger $logger,
        \Radarsofthouse\Reepay\Model\Status $reepayStatus,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_request = $request;
        $this->_orderInterface = $orderInterface;
        $this->_url = $context->getUrl();
        $this->_reepayCharge = $reepayCharge;
        $this->_reepaySession = $reepaySession;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_reepayHelper = $reepayHelper;
        $this->_reepayHelperEmail = $reepayHelperEmail;
        $this->_reepayHelperSurchargeFee = $reepayHelperSurchargeFee;
        $this->_logger = $logger;
        $this->_reepayStatus = $reepayStatus;
        $this->_priceHelper = $priceHelper;
        $this->_orderSender = $orderSender;
        $this->_checkoutSession = $checkoutSession;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\framework\Exception\PaymentException
     */
    public function execute()
    {
        $params = $this->_request->getParams();
        $orderId = $params['invoice'];
        $id = $params['id'];
        $isAjax = 0;
        if (isset($params['_isAjax'])) {
            $isAjax = 1;
        }

        $this->_logger->addDebug(__METHOD__, $params);

        if (empty($params['invoice']) || empty($params['id'])) {
            return;
        }

        $order = $this->_orderInterface->loadByIncrementId($orderId);
        $apiKey = $this->_reepayHelper->getApiKey($order->getStoreId());

        $this->_checkoutSession->setLastOrderId($order->getId());
        $this->_checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->_checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
        $this->_checkoutSession->setLastQuoteId($order->getQuoteId());

        $reepayStatus = $this->_reepayStatus->load($orderId, 'order_id');
        if ($reepayStatus->getStatusId()) {
            $this->_logger->addDebug('order : ' . $orderId . ' has been accepted already');
            if ($isAjax === 1) {
                $result = [];
                $result['status'] = 'success';
                if (!empty($order->getRemoteIp())) {
                    // place online
                    $result['redirect_url'] = $this->_url->getUrl('checkout/onepage/success');
                } else {
                    // place by admin
                    $result['redirect_url'] = $this->_url->getUrl('reepay/standard/success');
                }
                return $this->_resultJsonFactory->create()
                    ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true)
                    ->setData($result);
            }
            $this->_logger->addDebug('Redirect to checkout/onepage/success');
            if (!empty($order->getRemoteIp())) {
                // place online
                return $this->redirect('checkout/onepage/success');
            }
            // place by admin
            return $this->redirect('reepay/standard/success');
        }

        $chargeRes = $this->_reepayCharge->get(
            $apiKey,
            $orderId
        );

        // add Reepay payment data
        $data = [
            'order_id' => $orderId,
            'first_name' => $order->getBillingAddress()->getFirstname(),
            'last_name' => $order->getBillingAddress()->getLastname(),
            'email' => $order->getCustomerEmail(),
            'token' => $params['id'],
            'masked_card_number' => isset($chargeRes['source']['masked_card']) ? $chargeRes['source']['masked_card'] : '',
            'fingerprint' => isset($chargeRes['source']['fingerprint']) ? $chargeRes['source']['fingerprint'] : '',
            'card_type' => isset($chargeRes['source']['card_type']) ? $chargeRes['source']['card_type'] : '',
            'status' => $chargeRes['state'],
        ];
        $newReepayStatus = $this->_reepayStatus;
        $newReepayStatus->setData($data);
        $newReepayStatus->save();

        $this->_reepayHelper->addTransactionToOrder($order, $chargeRes);

        $isSurchargeFeeEnable = $this->_reepayHelper->isSurchargeFeeEnabled();
        $this->_logger->addDebug(__METHOD__, ['isSurchargeFeeEnable' => $isSurchargeFeeEnable, 'orderId' => $orderId]);
        if ($isSurchargeFeeEnable) {
            //to test add 50.00
//            $chargeRes['source']['surcharge_fee'] = '5100';
            $this->_logger->addDebug('updateFeeToOrder', $chargeRes);
            $this->_reepayHelperSurchargeFee->updateFeeToOrder($orderId, $chargeRes);
        } else {
            $this->_logger->addDebug('NotupdateFeeToOrder', $chargeRes);
            $this->_reepayHelperEmail->sendEmail($orderId);
        }

        if ($isAjax === 1) {
            $result = [];
            $result['status'] = 'success';
            if (!empty($order->getRemoteIp())) {
                // place online
                $result['redirect_url'] = $this->_url->getUrl('checkout/onepage/success');
            } else {
                // place by admin
                $result['redirect_url'] = $this->_url->getUrl('reepay/standard/success');
            }
            return $this->_resultJsonFactory->create()->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true)->setData($result);
        }
        $this->_logger->addDebug('Redirect to checkout/onepage/success');
        if (!empty($order->getRemoteIp())) {
            // place online
            return $this->redirect('checkout/onepage/success');
        }// place by admin
        return $this->redirect('reepay/standard/success');

    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function redirect($path)
    {
        $resultPage = $this->_resultRedirectFactory->create()->setPath($path);
        $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        return $resultPage;
    }
}
