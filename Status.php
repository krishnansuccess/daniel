<?php


namespace Bd\Daniel\Controller\Payment;
use Magento\Sales\Model\Order;

class Status extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $jsonHelper;
    protected $quoteRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Product $product,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->quoteRepository = $quoteRepository;
        $this->cart = $cart;
        $this->_addressFactory = $addressFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->_storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->_product = $product;
        $this->quote = $quote;
        $this->customerRepository = $customerRepository;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $lastorderId = $this->_checkoutSession->getLastOrderId();
        $order = $this->orderRepository->get($lastorderId);
        $cart = $this->_objectManager->get(\Magento\Checkout\Model\Cart::class);
        // $formKey = $objectManager->create('\Magento\Framework\Data\Form\FormKey')->getFormKey();
        foreach ($order->getAllItems() as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_objectManager->get(\Magento\Checkout\Model\Session::class)->getUseNotice(true)) {
                    $this->messageManager->addNoticeMessage($e->getMessage());
                } else {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
                return $resultRedirect->setPath('*/*/history');
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t add this item to your shopping cart right now.')
                );
                return $resultRedirect->setPath('checkout/cart');
            }
        }
        $cart->save();
        $orderState = 'dpaypending';
        $order->setState($orderState)->setStatus('dpaypending');
        $order->setSendEmail(0);
        $order->save();

        $userData = array("username" => "abinaya", "password" => "Abi@1995");
        $ch = curl_init("https://daniels-stage.augmentes.net/rest/V1/integration/admin/token");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))));
        $token = curl_exec($ch);
        // return $resultRedirect->setPath('checkout/cart');
        $params = $this->getRequest()->getParams();
        try {

            $url = "https://daniels.infinitybuss.com/dpay/paymentservices.ashx";

            //Store your XML Request in a variable
            $token_input_xml = '<paymentservices>
                        <action>START</action>
                        <bearertoken>'.json_decode($token).'</bearertoken>
                        <sendinprocesspacket>YES</sendinprocesspacket>
                        <transactionid>'.$lastorderId.'</transactionid>
                        <purchasetotal>'.$order->getGrandTotal().'</purchasetotal>
                        <customer>
                            <firstname>'.$params['firstname'].'</firstname>
                            <lastname>'.$params['lastname'].'</lastname>
                            <emailaddr>'.$params['email'].'</emailaddr>
                        </customer>
                        <billing>
                            <street>'.$params['billingAddrstreet'].'</street>
                            <city>'.$params['billingAddrcity'].'</city>
                            <state>'.$params['billingAddrstateCode'].'</state>
                            <zipcode>'.$params['billingAddrzipcode'].'</zipcode>
                        </billing>
                        <shipping>
                            <firstname>'.$params['shippingAddrFirstName'].'</firstname>
                            <lastname>'.$params['shippingAddrLastName'].'</lastname>
                            <sameasbilling>'.$params['sameAsBilling'].'</sameasbilling>
                            <street>'.$params['shippingAddrstreet'].'</street>
                            <city>'.$params['shippingAddrcity'].'</city>
                            <state>'.$params['shippingAddrstateCode'].'</state>
                            <zipcode>'.$params['shippingAddrzipcode'].'</zipcode>
                        </shipping>
                    </paymentservices>';
			
			$error_message = date('Y-m-d H:i:s')." DPay Request: ".$token_input_xml."\n"; 
			$log_file = $_SERVER['DOCUMENT_ROOT'].'/DPay/DPay.log';			
			error_log($error_message, 3, $log_file);

            $headr = array();
            $headr[] = 'Content-type:text/xml';
            $headr[]= 'Accept:text/xml';
            $headr[] = 'Authorization:Basic 13EA389425F847CD9615C15CA3571CF8';

            //setting the curl parameters.
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            // Following line is compulsary to add as it is:
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
            curl_setopt($curl, CURLOPT_POSTFIELDS,
                        $token_input_xml);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 900);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_HTTPHEADER,$headr);
            $token_data = curl_exec($curl);
            // var_dump($token_data);
            // echo $token_data['urlkey'];
            // print_r(curl_getinfo($curl));
            // die(curl_error($curl));
            curl_close($curl);
			
			$error_message = date('Y-m-d H:i:s')." DPay URL Key Response: ".$token_data."\n"; 
			$log_file = $_SERVER['DOCUMENT_ROOT'].'/DPay/DPay.log';			
			error_log($error_message, 3, $log_file);
			
            //convert the XML result into array
            $token_array_data = json_decode(json_encode(simplexml_load_string($token_data)), true);

            // print_r($token_array_data['urlkey']);
            return $this->jsonResponse($token_array_data);

            //return $this->jsonResponse($result); // for token return


        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}