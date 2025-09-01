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

// Shema Rank Math SEO

add_filter( 'rank_math/json_ld', function( $data, $jsonld ) {
    // 1. Verifica se estamos em uma página de produto válida.
    if ( empty( $data['richSnippet'] ) || ! in_array( $data['richSnippet']['@type'], [ 'Product', 'ProductGroup' ] ) ) {
        return $data;
    }

    // 2. Define os dados de Envio (shippingDetails) uma única vez.
    // Usamos um @id para poder reutilizar este bloco sem duplicar o código.
    $data['shippingDetails'] = [
        '@context'     => 'https://schema.org/',
        '@type'        => 'OfferShippingDetails',
        '@id'          => '#shipping_policy', // ID para referência interna
        'shippingRate' => [
            '@type'    => 'MonetaryAmount',
            'value'    => '0', // SEU FRETE GRÁTIS
            'currency' => 'BRL',
        ],
        'shippingDestination' => [
            '@type'        => 'DefinedRegion',
            'addressCountry' => 'BR',
        ],
        'deliveryTime' => [
            '@type' => 'ShippingDeliveryTime',
            'handlingTime' => [
                '@type'    => 'QuantitativeValue',
                'minValue' => 1,
                'maxValue' => 2,
                'unitCode' => 'DAY',
            ],
            'transitTime' => [
                '@type'    => 'QuantitativeValue',
                'minValue' => 7,
                'maxValue' => 14,
                'unitCode' => 'DAY',
            ],
        ],
    ];

    // 3. Define os dados da Política de Devolução (hasMerchantReturnPolicy ) uma única vez.
    $data['hasMerchantReturnPolicy'] = [
        '@context'             => 'https://schema.org/',
        '@type'                => 'MerchantReturnPolicy',
        '@id'                  => '#merchant_policy', // ID para referência interna
        'applicableCountry'    => 'BR',
        'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
        'merchantReturnDays'   => 30, // SEUS 30 DIAS
        'returnMethod'         => 'https://schema.org/ReturnByMail',
        'returnFees'           => 'https://schema.org/FreeReturn',
    ];

    // 4. Aplica as políticas ao produto.

    // Caso 1: Produto Simples (tem 'offers', mas não 'hasVariant' )
    if ( 'Product' === $data['richSnippet']['@type'] && isset($data['richSnippet']['offers']) ) {
        $data['richSnippet']['offers']['shippingDetails'] = [ '@id' => '#shipping_policy' ];
        $data['richSnippet']['offers']['hasMerchantReturnPolicy'] = ['@id' => '#merchant_policy'];
    }

    // Caso 2: Produto Variável (tem 'hasVariant')
    if ( ! empty( $data['richSnippet']['hasVariant'] ) ) {
        // Itera sobre cada variação do produto
        foreach ( $data['richSnippet']['hasVariant'] as $key => $variant ) {
            if ( empty( $variant['offers'] ) ) {
                continue; // Pula se a variação não tiver uma oferta
            }
            // Adiciona a referência da política de envio e devolução à oferta da variação
            $data['richSnippet']['hasVariant'][ $key ]['offers']['shippingDetails'] = [ '@id' => '#shipping_policy' ];
            $data['richSnippet']['hasVariant'][ $key ]['offers']['hasMerchantReturnPolicy'] = [ '@id' => '#merchant_policy' ];
        }
    }

    return $data;

}, 99, 2);
