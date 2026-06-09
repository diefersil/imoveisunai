<?php

/**
 * CONFIGURAÇÃO DOS SITES
 *
 * Edite este arquivo para adicionar/remover sites e ajustar seletores.
 */
$sites = [
    [
        "nome_site" => "Prime Imóveis - Locação",
        "usuario" => "imoveisunai",
        "cidade" => "Unaí",
        "uf" => "MG",
        "categoria" => "",
        "tags" => "",
        "contato" => "(38) 99970-6070",
        "periodo" => 30,
        "url" => ["https://primeimoveisunai.com.br/imoveis","https://primeimoveisunai.com.br/imoveis/pagina/2"],
        "numero_registros" => 20,
        "frequencia" => [
            "tipo" => "nunca",
            "horario_inicio" => "23:00",
            "horario_fim" => "24:00"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//div[contains(@class,'property-main')]",
            "card_nome" => ".//h3[contains(@class,'property-title')]",
            "card_cidade" => "",
            "card_uf" => "",
            "card_contato" => "",
            "card_localizacao" => "",
            "preco" => ".//div[contains(@class,'property-price')]//span",
            "card_imagem_url" => ".//img[contains(@class,'img-fluid')]",
            "card_url" => ".//a",
            "galeria" => "//img[contains(@class,'img-fluid')]",
            "descricao" => "//div[contains(@class,'inner-box property-dsc')]"
        ]
    ],


    [
        "nome_site" => "Terra Fértil",
        "usuario" => "imoveisunai",
        "cidade" => "Unaí",
        "uf" => "MG",
        "categoria" => "",
        "tags" => "",
        "contato" => "(38) 99958-5454",
        "periodo" => 30,
        "url" => [
            "https://terrafertil.com.br/imoveis/para-alugar/apartamento",
            "https://terrafertil.com.br/imoveis/a-venda/apartamento",
            "https://terrafertil.com.br/imoveis/novos/apartamento",
            "https://terrafertil.com.br/imoveis/a-venda/apartamento-duplex",
            "https://terrafertil.com.br/imoveis/a-venda/fazenda",
            "https://terrafertil.com.br/imoveis/para-alugar/hotel",
            "https://terrafertil.com.br/imoveis/para-alugar/casa",
            "https://terrafertil.com.br/imoveis/a-venda/casa",
            "https://terrafertil.com.br/imoveis/a-venda/terreno",
            "https://terrafertil.com.br/imoveis/para-alugar/ponto",
            "https://terrafertil.com.br/imoveis/a-venda/ponto",
            "https://terrafertil.com.br/imoveis/para-alugar/sala",
            "https://terrafertil.com.br/imoveis/a-venda/sala",
            "https://terrafertil.com.br/imoveis/novos/sala",
            "https://terrafertil.com.br/imoveis/para-alugar/barracao",
            "https://terrafertil.com.br/imoveis/a-venda/barracao",
        ],
        "numero_registros" => 120,
        "frequencia" => [
            "tipo" => "sempre"/*,
            "horario_inicio" => "16:00",
            "horario_fim" => "18:00"*/
        ],
        "verificar_string" => "",
        "seletores" => [
            //"card" => "//a[contains(concat(' ', normalize-space(@class), ' '), ' card-with-buttons ') and contains(concat(' ', normalize-space(@class), ' '), ' borderHover ')]",
            "card" => "//a[contains(@class,'card-with-buttons') and contains(@class,'borderHover')]",
            "card_nome" => ".//p[contains(@class,'card-with-buttons__title')]",
            "card_cidade" => "",
            "card_uf" => "",
            "card_contato" => "",
            "card_localizacao" => ".//h2[contains(@class,'card-with-buttons__heading')]",
            "preco" => ".//*[contains(@class,'card-with-buttons__value')]",
            "card_imagem_url" => ".//li[contains(@class,'cards_digital_carousel-item-0')]//img",           
            "card_url" => ".",
            "galeria" => "//div[contains(@class,'overflow-image-gallery')]//img",
            "descricao" => "//div[contains(@class,'box-description')] | //div[contains(@class,'box-detail')]"
            //"descricao" => "//div[contains(@class,'listing-details')]",
            //"descricao" => "//div[contains(@class,'box-description')]"
            //"descricao" => "//div[contains(@class,'box-description') and contains(@class,'box-detail')]",listing-details
        ]
    ],


    [
        "nome_site" => "Sucesso Imóveis - Geral",
        "usuario" => "imoveisunai",
        "cidade" => "Unaí",
        "uf" => "MG",
        "categoria" => "",
        "tags" => "",
        "contato" => "(38) 99935-9555",
        "periodo" => 30,
        "url" => [
            "https://sucessoimoveis.imb.br/imoveis",
            "https://sucessoimoveis.imb.br/imoveis/page/2",
            "https://sucessoimoveis.imb.br/imoveis/page/3"
        ],
        "numero_registros" => 20,
        "frequencia" => [
            "tipo" => "nunca",
            "horario_inicio" => "23:00",
            "horario_fim" => "24:00"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//div[contains(@class,'g5ere__property-item-inner')]",
            "card_nome" => ".//h3[contains(@class,'g5ere__loop-property-title')]",
            "card_cidade" => "",
            "card_uf" => "",
            "card_contato" => "",
            "card_localizacao" => "",
            "preco" => ".//span[contains(@class,'g5ere__lpp-price')]",
            "card_imagem_url" => ".//div[contains(@class,'g5ere__property-featured')]//a[contains(@style,'background-image')]",
            "card_url" => ".//a[contains(@class,'g5core__entry-thumbnail')]",
            "galeria" => "//div[contains(@class,'g5core__entry-thumbnail')]//img",
            "descricao" => "//div[contains(@class,'g5ere__property-block-description')]"
        ]
    ],


    [
        "nome_site" => "Área 38",
        "usuario" => "imoveisunai",
        "cidade" => "Paracatu",
        "uf" => "MG",
        "categoria" => "",
        "tags" => "",
        "contato" => "(38) 3671-0038",
        "periodo" => 30,
        "url" => [
            "https://area38.com.br/busca?tipo=Fazenda",
        ],
        "numero_registros" => 5,
        "frequencia" => [
            "tipo" => "nunca"/*,
            "horario_inicio" => "15:00",
            "horario_fim" => "18:00"*/
        ],
        "verificar_string" => "",
        "seletores" => [
            
            "card" => "//a[contains(@class,'mb-2')]",
            "card_nome" => ".//h4[contains(@class,'text-lg')]",
            "card_cidade" => "",
            "card_uf" => "",
            "card_contato" => "",
            "card_localizacao" => ".//div[contains(@class,'container-endereco')]//span",
            "preco" => ".//h5[contains(@class,'text-lg')]",
            "card_imagem_url" => ".//img[contains(@class,'w-full')]",           
            "card_url" => ".",
            "galeria" => "//img[contains(@class,'transition-all')]",
            "descricao" => "//p[contains(@class,'my-5')]//span"
        ]
    ]
];
