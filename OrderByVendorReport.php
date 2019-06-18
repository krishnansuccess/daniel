<?php

namespace Bd\Report\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Bd\Report\Model\Export\ConvertToCsv;
use Bd\Report\Model\Export\ConvertToJson;
use Bd\Report\Model\Export\ConvertToXml;
use Bd\Report\Model\VendorReportFactory;
use Bd\Report\Model\VendorFactory;
use Magento\Sales\Model\OrderFactory;
use Bd\Report\Model\Mail\MailSender;

class OrderByVendorReport {

    protected $orderCollectionFactory;
    protected $convertToCsv;
    protected $convertToJson;
    protected $convertToXml;
    protected $vendorReportFactory;
    protected $vendorFactory;
    protected $orderFactory;
    protected $mailSender;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(OrderFactory $orderFactory, VendorReportFactory $vendorReportFactory, VendorFactory $vendorFactory, ConvertToXml $convertToXml, MailSender $mailSender, ConvertToJson $convertToJson, ConvertToCsv $convertToCsv, OrderCollectionFactory $orderCollectionFactory) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->convertToCsv = $convertToCsv;
        $this->convertToJson = $convertToJson;
        $this->convertToXml = $convertToXml;
        $this->vendorReportFactory = $vendorReportFactory;
        $this->vendorFactory = $vendorFactory;
        $this->orderFactory = $orderFactory;
        $this->mailSender = $mailSender;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute() {
        $orderCollection = $this->orderCollectionFactory->create();
         $orderCollection->addFieldTofilter('is_exported', 0);
        foreach ($orderCollection as $order) {

            $orderData = $this->getOrderArray($order);
            try {
                foreach ($orderData['vendors'] as $vendor) {

                    $filteredOrder = array_filter($orderData['orderData'], function($v, $k) use ($vendor) {
                        return $v['ITEMVEND'] == $vendor;
                    }, ARRAY_FILTER_USE_BOTH);


                    $fileName = "";
                    $vendorModel = $this->vendorFactory->create();
                    $vendorModel->load($vendor, 'vendor_code');
                     $type='txt';
                    if ($vendorModel->getFormat() == 'tab') {
                         $type='txt';
                        $fileName = $this->convertToCsv->getCsvFile($filteredOrder, $order->getId() . '_' . $vendor);
                    }
                    if ($vendorModel->getFormat() == 'JSON') {
                         $type='json';
                        $fileName = $this->convertToJson->getJsonFile($filteredOrder, $order->getId() . '_' . $vendor);
                    }
                    if ($vendorModel->getFormat() == 'XML') {
                         $type='xml';
                        $fileName = $this->convertToXml->getXmlFile($filteredOrder, $order->getId() . '_' . $vendor);
                    }
                    if (!isset($fileName['value']))
                        continue;
                    $this->mailSender->send($fileName['value'], $type, $vendorModel->getVendorMailId());
                    $vendorReport = $this->vendorReportFactory->create();
                    $vendorReport->setOrderId($order->getId());
                    $vendorReport->setVendorCode($vendor);
                    $vendorReport->setEmail($vendorModel->getVendorMailId());
                    $vendorReport->setFile($fileName['value']);
                    $vendorReport->save();
                }

                $orderModel = $this->orderFactory->create();
                $orderModel->load($order->getId());
                $orderModel->setIsExported(1);
                $orderModel->save();
            } catch (\Exception $e) {
                echo $e->getMessage();
                exit;
            }
        }
        echo $orderCollection->getSize();
    }

    private function getOrderArray($order) {

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $payment = $order->getPayment();

        $orderItems = $order->getItems();
        $result = [];
        $i = 0;
        $venders = [];
        foreach ($orderItems as $orderItem) {

            $venders[] = $orderItem->getProduct()->getWebVendorCode();
            $result['orderData'][$i]['ORDERNO'] = $order->getIncrementId();
            $result['orderData'][$i]['ORDERDT'] = $order->getCreatedAtFormatted(2);
            $result['orderData'][$i]['BILLTONAME'] = $billingAddress->getFirstname() . " " . $billingAddress->getLastname();
            $billStreet = $billingAddress->getStreet();
            $result['orderData'][$i]['BILLTOADDR1'] = $billStreet[0];
            $result['orderData'][$i]['BILLTOADDR2'] = isset($billStreet[1]) ? $billStreet[1] : "";
            $result['orderData'][$i]['BILLTOCITY'] = $billingAddress->getCity();
            $result['orderData'][$i]['BILLTOSTATE'] = $billingAddress->getRegion();
            $result['orderData'][$i]['BILLTOZIP'] = $billingAddress->getPostcode();
            $result['orderData'][$i]['BILLTOCNTRY'] = $billingAddress->getCountryId();
            $result['orderData'][$i]['SHIPTONAME'] = $shippingAddress->getFirstname() . " " . $shippingAddress->getLastname();
            $shipStreet = $shippingAddress->getStreet();
            $result['orderData'][$i]['SHIPTOADDR1'] = $shipStreet[0];
            $result['orderData'][$i]['SHIPTOADDR2'] = isset($shipStreet[1]) ? $shipStreet[1] : "";
            ;
            $result['orderData'][$i]['SHIPTOCITY'] = $shippingAddress->getCity();
            $result['orderData'][$i]['SHIPTOSTATE'] = $shippingAddress->getRegion();
            $result['orderData'][$i]['SHIPTOZIP'] = $shippingAddress->getPostcode();
            $result['orderData'][$i]['SHIPTOCNTRY'] = $shippingAddress->getCountryId();
            $result['orderData'][$i]['SHIPTYPE'] = $shippingAddress->getIncrementId();
            $result['orderData'][$i]['CUSTPHONE'] = $shippingAddress->getTelephone();
            $result['orderData'][$i]['CUSTEMAIL'] = $order->getCustomerEmail();
            $result['orderData'][$i]['NUMITEMS']=$order->getTotalItemCount();
            $result['orderData'][$i]['SUBTOTAL'] = $order->getSubtotal();
            $result['orderData'][$i]['TAX'] = $order->getTaxAmount();
            $result['orderData'][$i]['SHIPPING'] = $order->getShippingAmount();
            $result['orderData'][$i]['TOTAL'] = $order->getGrandTotal();
            $result['orderData'][$i]['PAYMENTTYPE'] = $payment->getMethod();
            $result['orderData'][$i]['PAYMENTREF'] = $order->getIncrementId();
            $result['orderData'][$i]['PRINTCOMMENT'] = $order->getIncrementId();
            $result['orderData'][$i]['PROMOCODE'] = $order->getCouponCode();
            $result['orderData'][$i]['COMMENT'] = $order->getIncrementId();
            $result['orderData'][$i]['ITEMNO'] = $orderItem->getSku();
            $result['orderData'][$i]['ITEMDESC'] = $orderItem->getDescription();
            $result['orderData'][$i]['ITEMQTY'] = $orderItem->getQtyOrdered();
            $result['orderData'][$i]['ITEMPRICE'] = $orderItem->getPrice();
            $result['orderData'][$i]['ITEMVEND'] = $orderItem->getProduct()->getWebVendorCode();

            $i++;
        }
        $result['vendors'] = $venders;
        return $result;
    }

}
