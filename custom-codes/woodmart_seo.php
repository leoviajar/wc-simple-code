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

add_filter( 'rank_math/snippet/rich_snippet_product_entity', function( $entity ) {
    // Verifica se a entidade 'offers' existe. É crucial para evitar erros.
    if ( isset( $entity['offers'] ) ) {

        // --- 1. Adiciona os Detalhes de Envio (shippingDetails) ---
        $entity['offers']['shippingDetails'] = [
            '@type'             => 'OfferShippingDetails',
            'shippingRate'      => [
                '@type'    => 'MonetaryAmount',
                'value'    => '10', // Você pode usar uma variável aqui se o frete não for fixo
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

        // --- 2. Adiciona a Política de Devolução (hasMerchantReturnPolicy) ---
        $entity['offers']['hasMerchantReturnPolicy'] = [
            '@type'                 => 'MerchantReturnPolicy',
            'applicableCountry'     => 'BR',
            'returnPolicyCategory'  => 'https://schema.org/MerchantReturnFiniteReturnWindow',
            'merchantReturnDays'    => 30,
            'returnMethod'          => 'https://schema.org/ReturnByMail',
            'returnFees'            => 'https://schema.org/FreeReturn',
        ];
    }

    // Retorna a entidade modificada para o Rank Math.
    return $entity;
} );

/**
 * Adiciona telephone, address, e priceRange ao schema de Organization/Store do Rank Math.
 * Este filtro modifica a entidade principal da organização do site.
 */
add_filter( 'rank_math/json_ld/organization', function( $data ) {
    // 1. Adiciona o Telefone
    // Formato internacional é recomendado
    $data['telephone'] = '+5511912345678'; // <-- SUBSTITUA PELO SEU TELEFONE

    // 2. Adiciona a Faixa de Preço
    // Use $, $$, $$$ ou $$$$. '$$' é um bom começo.
    $data['priceRange'] = '$$'; // <-- AJUSTE SE NECESSÁRIO

    // 3. Adiciona o Endereço Completo
    $data['address'] = [
        '@type'           => 'PostalAddress',
        'streetAddress'   => 'Rua Exemplo, 123, Sala 45', // <-- SUBSTITUA
        'addressLocality' => 'Sua Cidade',               // <-- SUBSTITUA
        'addressRegion'   => 'SP',                       // <-- SUBSTITUA (Sigla do Estado)
        'postalCode'      => '12345-678',                // <-- SUBSTITUA
        'addressCountry'  => 'BR',
    ];

    return $data;
});