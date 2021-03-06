var config = {
	map: {
        '*': {
             configurable: 'ConfigurableProduct_UpdateSKU/js/configurable'
        }
    },
	
    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'ConfigurableProduct_UpdateSKU/js/model/skuswitch': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'ConfigurableProduct_UpdateSKU/js/model/swatch-skuswitch': true
            }
        }
	}
};
