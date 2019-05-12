// var token_xml = "<serviceresponse><action>START</action><result>failure</result><errorcode></errorcode><message>Missing API Key</message><message2>Please add your API Key to the HTTP Headers</message2><transactionid>10000012345</transactionid><urlkey>C5F40MV7H31416</urlkey></serviceresponse>";
window.addEventListener('message', function(event) {
// function onComplete(token_xml){
    var parser, xmlDoc;
    parser = new DOMParser();
    xmlDoc = parser.parseFromString(event.data,"text/xml");
    var token_result = xmlDoc.getElementsByTagName("result")[0].childNodes[0].nodeValue;
    console.log(xmlDoc.getElementsByTagName("result")[0]);
    if(token_result == 'OK') {
        var url=window.checkout.baseUrl+'/daniel/payment/result';
        jQuery.ajax({
            type: "POST",
            url: url,
            data: {xmlData : 1},
            success: function(result) {
                console.log(result);
				window.location=window.checkout.baseUrl+'/checkout/onepage/success';
            }
        });
    }
    else {
        var message = 'Your Transaction did not completed successfully. Please try again';
      //  var message2 = xmlDoc.getElementsByTagName("message2")[0].childNodes[0].nodeValue;
        window.customAlert({
            title: '',
            content: message,
            actions: {
                always: function(){
                    window.location.reload();
                }
            }
        });
        // jQuery('#transaction_error').show();
        // jQuery('#transaction_error').html('<h1>'+message+'</h1><p>'+message2+'</p>');
    }
});
