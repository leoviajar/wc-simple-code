<?php 
/**
 * Remove títulos h3 dos produtos
 */
add_action( 'init', 'substituir_titulo_produto_woodmart' );

function substituir_titulo_produto_woodmart() {
    remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
    add_action( 'woocommerce_shop_loop_item_title', 'meu_template_loop_product_title_com_span', 10 );
}

if ( ! function_exists( 'meu_template_loop_product_title_com_span' ) ) {
    function meu_template_loop_product_title_com_span() {
        echo '<span class="wd-entities-title"><a href="' . esc_url( get_the_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></span>';
    }
}