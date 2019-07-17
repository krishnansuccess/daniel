<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageWorx\OptionFeatures\Plugin;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;

class UpdateProductOptions {

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
    public function afterGetJsonConfig(\Magento\Catalog\Block\Product\View\Options $subject, $result) {

        $resultDecoded = $this->jsonDecoder->decode($result);
      //  return $result;
       // print_r($resultDecoded); die;
        foreach ($resultDecoded as $id => $options) {
            foreach ($options as $optionId => $option) {
          
            $resultDecoded[$id][$optionId]['prices']['retailPrice'] = $options[$optionId]['prices']['finalPrice'];
        }
        }
        $resultEncoded = $this->jsonEncoder->encode($resultDecoded);

        return $resultEncoded;
    }

}
