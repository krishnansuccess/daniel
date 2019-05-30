define([
    'ko',
    'jquery',
    'Magento_Checkout/js/action/place-order',
    'Magento_Payment/js/view/payment/cc-form',
    'mage/url',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert'
],
function (ko, $, placeOrderAction, Component, url, redirectOnSuccessAction, quote, modal, alert) {
    'use strict';
    window.customAlert = alert;
    return Component.extend({
        defaults: {
            //redirectAfterPlaceOrder: false,
            template: 'Bd_Daniel/payment/daniel'
        },

        isDanielCompleted : ko.observable(false),

        context: function() {
            return this;
        },

        getCode: function() {
            return 'daniel';
        },

        isActive: function() {
            return true;
        },

        isSuccess: function() {
            return true;
        },
        /**
         * Get payment method data
        */
        getPaymentData: function () {
            return {
                'method': this.getCode(),
                'po_number': null,
                'additional_data': null
            };
        },
        getData: function () {
            var data = {
                    'method': this.getCode(),
                    'cc_cid': this.creditCardVerificationNumber(),
                    'cc_type': this.creditCardType(),
                    'cc_exp_year': this.creditCardExpYear(),
                    'cc_exp_month': this.creditCardExpMonth(),
                    'cc_number': this.creditCardNumber()
            };

            return data;
        },
        /**
         * @return {*}
         */
        getPlaceOrderDeferredObject: function () {
            console.log(this.getPaymentData());
            return $.when(
                placeOrderAction(this.getPaymentData(), this.messageContainer)
            );
        },
        afterPlaceOrder: function () {
            var billingAddress = quote.billingAddress();
            var shippingAddress = quote.shippingAddress();
            var firstname = billingAddress.firstname;
            var lastname = billingAddress.lastname;
            var email = window.customerData.email ? window.customerData.email : quote.guestEmail;
            var billingAddrstreet = billingAddress.street[0];
            var billingAddrcity = billingAddress.city;
            var billingAddrstate = billingAddress.region;
            var billingAddrzipcode = billingAddress.postcode;
            var shippingAddrstreet = shippingAddress.street[0];
            var shippingAddrcity = shippingAddress.city;
            var shippingAddrstate = shippingAddress.region;
            var shippingAddrzipcode = shippingAddress.postcode;
            var shippingAddrFirstName = shippingAddress.firstname;
            var shippingAddrLastName = shippingAddress.lastname;
            var sameAsBilling = $('[name="billing-address-same-as-shipping"]').is(":checked") ? 'Y' : 'N';
            
            $.ajax({
                url: url.build('daniel/payment/status'),
                type: 'POST',
                data : {firstname:firstname,lastname: lastname, email:email, billingAddrstreet: billingAddrstreet, billingAddrcity: billingAddrcity, billingAddrstate: billingAddrstate, billingAddrzipcode: billingAddrzipcode, shippingAddrstreet: shippingAddrstreet, shippingAddrcity: shippingAddrcity, shippingAddrstate: shippingAddrstate, shippingAddrzipcode: shippingAddrzipcode, shippingAddrFirstName: shippingAddrFirstName, shippingAddrLastName: shippingAddrLastName,sameAsBilling: sameAsBilling },
                success: function (data) {
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        title: 'Daniel Payment',
                        modalCloseBtnHandler:function () {
                                 window.location=window.location.origin+"/checkout";
                          
                            },
                        buttons: [{
                            text: $.mage.__('Close'),
                            class: '',
                            click: function () {
                                 window.location=window.location.origin+"/checkout";
                                this.closeModal();
                            }
                        }]
                    };
                    
                    $('#danielIframe').css('display','block');
                    var popup = modal(options, $('#popup-modal'));
                    $('#danielIframe').attr('src','https://daniels.infinitybuss.com/dpay/dp_Payment.aspx?key='+data.urlkey);
                    $('#popup-modal').modal('openModal');
                },
                error : function (xhr, ajaxOptions, thrownError){  
                    console.log(xhr.status);
                },
            });
        },
        placeOrder: function () {
            console.log(quote);
            this.isPlaceOrderActionAllowed(false);
            var self = this;
            // if(this.isDanielCompleted() == false){
                // var billingAddress = quote.billingAddress();
                // var shippingAddress = quote.shippingAddress();
                // var firstname = billingAddress.firstname;
                // var lastname = billingAddress.lastname;
                // var email = window.customerData.email ? window.customerData.email : quote.guestEmail;
                // var billingAddrstreet = billingAddress.street[0];
                // var billingAddrcity = billingAddress.city;
                // var billingAddrstate = billingAddress.region;
                // var billingAddrzipcode = billingAddress.postcode;
                // var shippingAddrstreet = shippingAddress.street[0];
                // var shippingAddrcity = shippingAddress.city;
                // var shippingAddrstate = shippingAddress.region;
                // var shippingAddrzipcode = shippingAddress.postcode;
                // var shippingAddrFirstName = shippingAddress.firstname;
                // var shippingAddrLastName = shippingAddress.lastname;
                // var sameAsBilling = $('[name="billing-address-same-as-shipping"]').is(":checked") ? 'Y' : 'N';
                
                // $.ajax({
                //     url: url.build('daniel/payment/status'),
                //     type: 'POST',
                //     data : {firstname:firstname,lastname: lastname, email:email, billingAddrstreet: billingAddrstreet, billingAddrcity: billingAddrcity, billingAddrstate: billingAddrstate, billingAddrzipcode: billingAddrzipcode, shippingAddrstreet: shippingAddrstreet, shippingAddrcity: shippingAddrcity, shippingAddrstate: shippingAddrstate, shippingAddrzipcode: shippingAddrzipcode, shippingAddrFirstName: shippingAddrFirstName, shippingAddrLastName: shippingAddrLastName,sameAsBilling: sameAsBilling },
                //     success: function (data) {
                //         var options = {
                //             type: 'popup',
                //             responsive: true,
                //             innerScroll: true,
                //             title: 'Daniel Payment',
                //             buttons: [{
                //                 text: $.mage.__('Close'),
                //                 class: '',
                //                 click: function () {
                //                     this.closeModal();
                //                 }
                //             }]
                //         };
                        
                //         $('#danielIframe').css('display','block');
                //         var popup = modal(options, $('#popup-modal'));
                //         $('#danielIframe').attr('src','https://daniels.infinitybuss.com/dpay/dp_Payment.aspx?key='+data.urlkey);
                //         $('#popup-modal').modal('openModal');
                //     },
                //     error : function (xhr, ajaxOptions, thrownError){  
                //         console.log(xhr.status);
                //     },
                // });

            // }else {

                this.getPlaceOrderDeferredObject()
                        .fail(
                            function () {
                                self.isPlaceOrderActionAllowed(true);
                            }
                        ).done(
                        function () {
                            self.afterPlaceOrder();
                            
                            // if (self.redirectAfterPlaceOrder) {
                            //     redirectOnSuccessAction.execute();
                            // }
                        }
                );
                // return true;
            // }

            // return false;
        }
    });
});