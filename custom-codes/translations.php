<?php
add_filter( 'gettext', 'traduzir_mensagem_cupom_woocommerce', 999, 3 );

function traduzir_mensagem_cupom_woocommerce( $translated, $untranslated, $domain ) {

    if ( ! is_admin() && 'woocommerce' === $domain ) {

        switch ( $untranslated ) {
            case 'Coupon code "%s" already applied!':
                $translated = 'O cupom "%s" já foi aplicado!';
                break;
        }
    }
    
    return $translated;
}