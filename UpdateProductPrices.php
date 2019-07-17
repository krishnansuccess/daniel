<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageWorx\OptionFeatures\Plugin;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;

class UpdateProductPrices {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
    \Magento\Framework\Registry $registry, EncoderInterface $jsonEncoder, DecoderInterface $jsonDecoder
    ) {

        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * Change default JavaScript templates for options rendering
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function afterGetJsonConfig(\Magento\Catalog\Block\Product\View $subject, $result) {

        $resultDecoded = $this->jsonDecoder->decode($result);
        $_product = $this->registry->registry('current_product');
        if (!$_product) {
            return $this;
        }
        $resultDecoded['prices']['retailPrice'] = [
            'amount' => $_product->getRetailPrice(),
            
            //'amount' => $_product->getPriceInfo()->getPrice('retail_price')->getAmount()->getValue(),
            'adjustments' => []
        ];


        $resultEncoded = $this->jsonEncoder->encode($resultDecoded);

        return $resultEncoded;
    }

}
