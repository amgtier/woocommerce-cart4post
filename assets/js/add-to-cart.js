/* global wc_add_to_cart_params */
jQuery( function( $ ) {

    if ( typeof wc_add_to_cart_params === 'undefined' ) {
        return false;
    }

    var timeout;
    var timeout_amt = 0;

    var c4p_cart = {

        init: function() {
            if ( typeof post_id === 'undefined' ) {
                return; 
            }
            this.update_cart = this.update_cart.bind( this );

            $( ".checkout-button" ).attr( "href", $( ".checkout-button" ).attr( "href" ) + "?c4p=" + post_id );

            $( document ).on(
                'change',
                '.c4p-add-to-cart',
                ( event ) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => { this.update_cart( true, event ) }, timeout_amt );
                }
            );

        },
        update_cart: async ( preserve_notices, event, form ) => {
            if ( typeof post_id === 'undefined' ) {
                return; 
            }
            var target = $(event.target);
            var form = target.parent();
            var data = {};
            data[ 'cart_id' ] = post_id;

            $( ".cart-subtotal .woocommerce-Price-amount" ).html( "" );
            $( ".shipping .woocommerce-Price-amount" ).html( "" );
            $( ".order-total .woocommerce-Price-amount" ).html( "" );

            if ( target.attr( 'name' ).length == 0 ) { // value: 0 -> 1
                $.each( $(target).data(), (key, val) => {
                    data[key] = val;
                });

                data[ 'quantity' ] = target.val();

                $.ajax( {
                    type:       'POST',
                    url:        wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' ),
                    data:       data,
                    success: ( res ) => {
                        console.log( data );
                        console.log( res );
                        console.log( $(res.fragments[ 'div.widget_shopping_cart_content' ] ).find( 'a[data-product_id=' + data.product_id + ']' ) );
                        let item_key = $( $(res.fragments[ 'div.widget_shopping_cart_content' ] ).find( 'a[data-product_id=' + data.product_id + ']' )[0] ).attr( 'data-cart_item_key' );
                        $(target).attr( 'name', 'cart[' + item_key + '][qty]' );
                        set_cart_totals();
                    }
                } );
            }
            else {
                await $.each( $(target).data(), (key, val) => {
                    data[ $(target).attr( 'name' ) ] = $(target).val();
                    data[ 'update_cart' ] = 'Update cart';
                    data[ '_wpnonce' ] = $( '#_wpnonce' ).val();
                });

                $.ajax( {
                    type:       'POST',
                    url:        $(form).attr( 'action' ),
                    data:       data,
                    dataType:   'html',
                    success: ( res ) => {
                        set_cart_totals();
                    }
                } );

                if ( target.val() == 0 ) {
                    $(target).attr( 'name', '' );
                }
            }
        }

    };

    function set_cart_totals() {
        if ( typeof post_id === 'undefined' ) {
            return; 
        }
        var data = [];
        data[ 'cart_id' ] = post_id;
        $.ajax( {
            type:       'POST',
            url:        '/?c4p-ajax=get_cart_totals&cart_id=' + post_id,
            data:       data,
            dataType:   'html',
            success: ( res ) => {
                // console.log(res);
                $( '.cart_totals' ).html( JSON.parse(res).totals );
                $( '.woocommerce-message' ).remove();
                $( '.woocommerce' ).prepend( $(JSON.parse(res).notices ) );
                $( ".checkout-button" ).attr( "href", $( ".checkout-button" ).attr( "href" ) + "?c4p=" + post_id );
            }
        } );
    }

    c4p_cart.init();
});
