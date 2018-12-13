function whiteResponseHandler( status, response ) {
    if ( response.error ) {
        // re-enable the submit button
        jQuery( '#tc_payment_confirm' ).removeAttr( "disabled" ).show();
        jQuery( '#white_processing' ).hide();
        // show the errors on the form
        jQuery( "#white_checkout_errors" ).append( '<div class="tc_checkout_error">' + response.error.message + '</div>' );
    } else {
        // token contains id, last4, and card type
        var token = response['id'];//response.id
        // insert the token into the form so it gets submitted to the server
        jQuery( "#tc_payment_form" ).append( "<input type='hidden' name='whiteToken' value='" + token + "' />" );
        // and submit
        jQuery( "#tc_payment_form" ).get( 0 ).submit();
    }
}

jQuery( document ).ready( function( $ ) {
    $( "#tc_payment_form" ).submit( function( event ) {


        // FPM: Seems this JS is trapping on all types of payment. So we need to only process if the payment 

        // If we have the radio buttons allowing the user to select the payment method? ...
        if ( $( 'input.tc_choose_gateway' ).length ) {
            if ( $( 'input.tc_choose_gateway:checked' ).val() != "white" ) {
                return true;
            }
        }

        if ( $( 'input.tc_choose_gateway' ).length ) {
            // If the payment option selected is not Paymill then return and bypass input validations

            if ( $( 'input.tc_choose_gateway:checked' ).val() != "white" ) {
                return true;
                current_payment_method = $( 'input.tc_choose_gateway:checked' ).val();
            } else {
                current_payment_method = $( 'input.tc_choose_gateway:checked' ).val();
            }
        } else {
            if ( $( 'input[name="tc_choose_gateway"]' ).val() != "white" ) {
                return true;
                current_payment_method = $( 'input[name="tc_choose_gateway"]' ).val();
            } else {
                current_payment_method = $( 'input[name="tc_choose_gateway"]' ).val();
            }
        }

        if ( current_payment_method == 'white' ) {
            //clear errors
            $( "#white_checkout_errors" ).empty();
            // disable the submit button to prevent repeated clicks
            $( '#tc_payment_confirm' ).attr( "disabled", "disabled" ).hide();
            $( '#white_processing' ).show();

            // createToken returns immediately - the supplied callback submits the form if there are no errors

            white_payments = new White( white.publisher_key );
            
            // Send the card details to White; get back a token
            white_payments.createToken( {
                    number: $( '#cc_number' ).val(),
                    exp_month: $( '#cc_month' ).val(),
                    exp_year: $( '#cc_year' ).val(),
                    cvc: $( '#cc_cvv2' ).val()
            }, whiteResponseHandler );
            return false; // submit from callback
        }

    } );
} );