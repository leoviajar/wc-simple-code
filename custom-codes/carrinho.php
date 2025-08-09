<?php

add_filter('woocommerce_add_to_cart_redirect', 'meu_prefixo_forcar_redirecionamento_carrinho', 99);

function meu_prefixo_forcar_redirecionamento_carrinho() {
    return wc_get_cart_url();
}