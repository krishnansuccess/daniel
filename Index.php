<?php

namespace Bd\CustomOrder\Controller\Add;

class Index extends \Magento\Framework\App\Action\Action {

    protected $cart;
    protected $productRepository;
    protected $customOptionRepository;
    protected$serializer;

    public function __construct(
        \Bd\CustomOrder\Api\CustomOptionRepositoryInterface $customOptionRepository,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
    \Magento\Framework\App\Action\Context $context, \Magento\Checkout\Model\Cart $cart, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository, \Magento\Checkout\Model\Session $checkoutSession, array $data = []) {

        $this->cart = $cart;
        $this->serializer=$serializer;
        $this->customOptionRepository=$customOptionRepository;
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute() {

        $data = $data = json_decode($this->getRequest()->getParam('data'), true);
        $sku = $data['sku'];

        $customOptionId=$data['custom_option_id'];


        $_product = $this->productRepository->get($sku);

        $customOption=$this->customOptionRepository->getById($customOptionId);
        $additionalOptions = [];
        $options=json_decode($customOption->getOptions(),true);;


        $additionalOptions[] = array(
               'label' => "Option",
                'value' => $options['options'],
        );


        $_product->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
        $quote = $this->checkoutSession->getQuote();
        $price = $options['price'];
        $request = new \Magento\Framework\DataObject(['qty' => 1,'custom_price'=>$price]);
        $item=$quote->addProduct($_product, $request);
        $this->cart->save();
        $this->customOptionRepository->delete($customOption);
       /* if ($item->getProduct()->getSku() == $sku) {

                $item = ( $item->getParentItem() ? $item->getParentItem() : $item );

                $price = $options['price']; //set your price here
                $item->setCustomOption($options['options']);
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
                $item->calcRowTotal();
                $item->save();
                //$item->calcRowTotal();
                //$this->customOptionRepository->delete($customOption);
               // break;
            }
            $quote ->setTotalsCollectedFlag(false);
            $quote->collectTotals()->save();*/

        $this->_redirect('checkout/cart/');

    }

}
