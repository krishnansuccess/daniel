<?php

namespace MageWorx\OptionFeatures\Plugin;

use Magento\Catalog\Model\Product\Option\Value as ProductOptionValue;

class ArroundCustomOptionPriceCalculator
{


    public function aroundgetOptionPriceByPriceCode($subject, $proceed, $optionValue,$priceCode)
    {
     if ($optionValue->getPriceType() === ProductOptionValue::TYPE_PERCENT) {
            $basePrice = $optionValue->getOption()->getProduct()->getRetailPrice();
            $price = $basePrice * ($optionValue->getData(ProductOptionValue::KEY_PRICE) / 100);
           
            return $price;
        }
        return $optionValue->getData(ProductOptionValue::KEY_PRICE);
    }

 
}
