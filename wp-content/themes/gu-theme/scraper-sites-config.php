<?php

/**
 * CONFIGURAÇÃO DOS SITES
 *
 * Edite este arquivo para adicionar/remover sites e ajustar seletores.
 */
$sites = [
    /*----------------------------------------------------------------------------*
     * SITE: Prime Imóveis
     * ---------------------------------------------------------------------------- */
    [
        "nome_site" => "Prime Imóveis",
        "contato_nome" => "Prime Imóveis",
        "usuario" => "primeimoveis",
        "usuario_email" => "prime@imoveisunai.com.br",
        "cidade" => "Unaí",
        "uf" => "MG",
        "categoria" => "",
        "tags" => "",
        "contato_fone" => "(38) 99970-6070",
        "contato_whatsapp" => "(38) 99970-6070",
        "contato_instagram" => "",
        "contato_desc" => "",
        "periodo" => 30,
        "url" => [
            "https://primeimoveisunai.com.br/imoveis",
            "https://primeimoveisunai.com.br/imoveis/pagina/2",
            "https://primeimoveisunai.com.br/imoveis/negociacao/locacao"
        ],
        "numero_registros" => 20,
        "numero_maximo_por_url" => 10,
        "frequencia" => [
            "tipo" => "horario",
            "horario_inicio" => "00:00",
            "horario_fim" => "02:00"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//div[contains(@class,'property-main')]",
            "card_nome" => ".//h3[contains(@class,'property-title')]",
            "card_contato" => "",
            "card_contato_nome" => "",
            "card_contato_whatsapp" => "",
            "card_localizacao" => "",
            "card_area" => "",
            "card_area_contruida" => "",
            "preco" => ".//div[contains(@class,'property-price')]//span",
            "card_imagem_url" => ".//img[contains(@class,'img-fluid')]",
            "card_url" => ".//a",
            "galeria" => "//img[contains(@class,'img-fluid')]",
            "descricao" => "//div[contains(@class,'inner-box property-dsc')]"
        ]
    ],

    /*----------------------------------------------------------------------------*
     * SITE: Terra Fértil
     * ---------------------------------------------------------------------------- */
    [
        "nome_site" => "Terra Fértil",
        "contato_nome" => "Terra Fértil",
        "usuario" => "terrafertil",
        "usuario_email" => "fertil@imoveisunai.com.br",
        "cidade" => "Unaí",
        "uf" => "MG",
        "categoria" => "",
        "tags" => "",
        "contato_fone" => "(38) 99958-5454",
        "contato_whatsapp" => "(38) 99958-5454",
        "contato_instagram" => "",
        "contato_desc" => "",
        "periodo" => 30,
        "url" => [
            "https://www.terrafertilimobiliaria.com.br/imoveis",
            "https://www.terrafertilimobiliaria.com.br/imoveis/a-venda",
            "https://www.terrafertilimobiliaria.com.br/imoveis/para-alugar",
            "https://www.terrafertilimobiliaria.com.br/imoveis/novos",
            "https://www.terrafertilimobiliaria.com.br/imoveis/a-venda/fazenda"
        ],
        "numero_registros" => 15,
        "numero_maximo_por_url" => 3,
        "frequencia" => [
            "tipo" => "horario",
            "horario_inicio" => "02:00",
            "horario_fim" => "04:00"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//a[contains(@class,'card-with-buttons') and contains(@class,'borderHover')]",
            "card_nome" => ".//p[contains(@class,'card-with-buttons__title')]",
            "card_contato" => "",
            "card_contato_nome" => "",
            "card_contato_whatsapp" => "",
            "card_localizacao" => ".//h2[contains(@class,'card-with-buttons__heading')]",
            "card_area" => "",
            "card_area_contruida" => "",
            "preco" => ".//*[contains(@class,'card-with-buttons__value')]",
            "card_imagem_url" => ".//li[contains(@class,'cards_digital_carousel-item-0')]//img",
            "card_url" => ".",
            "galeria" => "//div[contains(@class,'overflow-image-gallery')]//img",
            "descricao" => "//div[contains(@class,'details')]"
        ]
    ],

    /*----------------------------------------------------------------------------*
     * SITE: Sucesso Imóveis
     * ---------------------------------------------------------------------------- */
    [
        "nome_site" => "Sucesso Imóveis",
        "contato_nome" => "Sucesso Imóveis",
        "usuario" => "sucessoimoveis",
        "usuario_email" => "sucesso@imoveisunai.com.br",
        "cidade" => "Unaí",
        "uf" => "MG",
        "categoria" => "",
        "tags" => "",
        "contato_fone" => "(38) 99935-9555",
        "contato_whatsapp" => "(38) 99935-9555",
        "contato_instagram" => "",
        "contato_desc" => "",
        "periodo" => 30,
        "url" => [
            "https://sucessoimoveis.imb.br/imoveis",
            "https://sucessoimoveis.imb.br/imoveis/page/2",
            "https://sucessoimoveis.imb.br/imoveis/page/3"
        ],
        "numero_registros" => 30,
        "numero_maximo_por_url" => 10,
        "frequencia" => [
            "tipo" => "horario",
            "horario_inicio" => "04:00",
            "horario_fim" => "06:00"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//div[contains(@class,'g5ere__property-item-inner')]",
            "card_nome" => ".//h3[contains(@class,'g5ere__loop-property-title')]",
            "card_contato" => "",
            "card_contato_nome" => "",
            "card_contato_whatsapp" => "",
            "card_localizacao" => "",
            "card_area" => "",
            "card_area_contruida" => "",
            "preco" => ".//span[contains(@class,'g5ere__lpp-price')]",
            "card_imagem_url" => ".//div[contains(@class,'g5ere__property-featured')]//a[contains(@style,'background-image')]",
            "card_url" => ".//a[contains(@class,'g5core__entry-thumbnail')]",
            "galeria" => "//div[contains(@class,'g5core__entry-thumbnail')]//img",
            "descricao" => "//div[contains(@class,'g5ere__property-block-description')]"
        ]
    ],

    /*----------------------------------------------------------------------------*
     * SITE: Área 38
     * ---------------------------------------------------------------------------- */
    [
        "nome_site" => "Área 38",
        "contato_nome" => "Área 38",
        "usuario" => "area38",
        "usuario_email" => "area38@imoveisunai.com.br",
        "cidade" => "Paracatu",
        "uf" => "MG",
        "categoria" => "",
        "tags" => "",
        "contato_fone" => "(38) 3671-0038",
        "contato_whatsapp" => "(38) 3671-0038",
        "contato_instagram" => "",
        "contato_desc" => "",
        "periodo" => 30,
        "url" => [
            "https://area38.com.br/busca?tipo=Fazenda",
            "https://area38.com.br/busca",
            "https://area38.com.br/busca?page=2",
            "https://area38.com.br/busca?page=3",
            "https://area38.com.br/busca?finalidade=Aluguel"
        ],
        "numero_registros" => 50,
        "numero_maximo_por_url" => 10,
        "frequencia" => [
            "tipo" => "horario",
            "horario_inicio" => "16:00",
            "horario_fim" => "18:00"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//a[contains(@class,'mb-2')]",
            "card_nome" => ".//h4[contains(@class,'text-lg')]",
            "card_contato" => "",
            "card_contato_nome" => "",
            "card_contato_whatsapp" => "",
            "card_localizacao" => ".//div[contains(@class,'container-endereco')]//span",
            "card_area" => "",
            "card_area_contruida" => "",
            "preco" => ".//h5[contains(@class,'text-lg')]",
            "card_imagem_url" => ".//img[contains(@class,'w-full')]",
            "card_url" => ".",
            "galeria" => "//img[contains(@class,'transition-all')]",
            "descricao" => "//p[contains(@class,'my-5')]//span"
        ]
    ],

    /*----------------------------------------------------------------------------*
     * SITE: Morado Imóveis
     * ---------------------------------------------------------------------------- */
    [
        "nome_site" => "Morado Imóveis",
        "contato_nome" => "Morado Imóveis",
        "usuario" => "moradoimoveis",
        "usuario_email" => "morado@imoveisunai.com.br",
        "cidade" => "Paracatu",
        "uf" => "MG",
        "categoria" => "",
        "tags" => "",
        "contato_fone" => "(38) 99856-5306",
        "contato_whatsapp" => "(38) 99856-5306",
        "contato_instagram" => "",
        "contato_desc" => "",
        "periodo" => 30,
        "url" => [
            "https://moradoimoveis.com.br/imoveis/venda/fazenda",
            "https://moradoimoveis.com.br/busca/?finalidade=sale"
        ],
        "numero_registros" => 48,
        "numero_maximo_por_url" => 15,
        "frequencia" => [
            "tipo" => "horario",
            "horario_inicio" => "22:00",
            "horario_fim" => "24:00"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//div[contains(@class,'ImovelItem')]",
            "card_nome" => ".//a[contains(@class,'Title')]",
            "card_contato" => "",
            "card_contato_nome" => "",
            "card_contato_whatsapp" => "",
            "card_localizacao" => ".//div[contains(@class,'container-endereco')]//span",
            "card_area" => "",
            "card_area_contruida" => "",
            "preco" => ".//span[contains(@class,'ValorMoeda')]",
            "card_imagem_url" => ".//img[contains(@class,'BannerImage')]",
            "card_url" => ".//a",
            "galeria" => "//div[contains(@class,'ms-lightbox') and @data-img]",
            "descricao" => "//div[contains(@class,'central_left')]"
        ]
    ],

    /*----------------------------------------------------------------------------*
     * SITE: Novo Lar
     * ---------------------------------------------------------------------------- */
    [
        "nome_site" => "Novo Lar",
        "contato_nome" => "Novo Lar",
        "usuario" => "novolar",
        "usuario_email" => "novolar@imoveisunai.com.br",
        "cidade" => "Unaí",
        "uf" => "MG",
        "categoria" => "",
        "tags" => "",
        "contato_fone" => "(38) 99879-9441",
        "contato_whatsapp" => "(38) 99879-9441",
        "contato_instagram" => "",
        "contato_desc" => "",
        "periodo" => 30,
        "url" => [
            "https://novolarimobiliariaunai.com.br/imoveis/",
            "https://novolarimobiliariaunai.com.br/imoveis/chacara",
            "https://novolarimobiliariaunai.com.br/imoveis/fazenda",
            "https://moradoimoveis.com.br/imoveis/venda/sitio"
        ],
        "numero_registros" => 40,
        "numero_maximo_por_url" => 12,
        "frequencia" => [
            "tipo" => "horario",
            "horario_inicio" => "08:00",
            "horario_fim" => "10:00"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//a[div[contains(@class,'resultado')]]",
            "card_nome" => ".//h3[contains(@class,'tipo')]",
            "card_contato" => "",
            "card_contato_nome" => "",
            "card_contato_whatsapp" => "",
            "card_localizacao" => ".//div[contains(@class,'container-endereco')]//span",
            "card_area" => "",
            "card_area_contruida" => "",
            "preco" => ".//div[contains(@class,'valor')]//h5",
            "card_imagem_url" => ".//div[contains(@class,'foto')]//img",
            "card_url" => ".",
            "galeria" => "//div[contains(@class,'fotorama')]//img",
            "descricao" => "//div[contains(@class,'descricao_imovel')]"
        ]
    ],

    /*----------------------------------------------------------------------------*
     * SITE: Formosa Imóveis
     * ---------------------------------------------------------------------------- */
    [
        "nome_site" => "Formosa Imóveis",
        "contato_nome" => "Formosa Imóveis",
        "usuario" => "formosaimoveis",
        "usuario_email" => "formosaimoveis@imoveisunai.com.br",
        "cidade" => "Formosa",
        "uf" => "Go",
        "categoria" => "",
        "tags" => "",
        "contato_fone" => "(61) 99932-6115",
        "contato_whatsapp" => "(61) 99932-6115",
        "contato_instagram" => "",
        "contato_desc" => "",
        "periodo" => 30,
        "url" => [
            "https://imoveisformosa.com.br/comprar/apartamento/formosa-go?by_type_or_subtype_slug%5B0%5D=apartamento&typeArea=private_area&floorComparision=equals&sort=-is_price_shown%2C-calculated_price%2Cid&offset=1&limit=21"
    
        ],
        "numero_registros" => 50,
        "numero_maximo_por_url" => 10,
        "frequencia" => [
            "tipo" => "nunca",
            "horario_inicio" => "22:00",
            "horario_fim" => "12:00"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//div[contains(@class,'CardProperty')]",
            "card_nome" => ".//div[contains(@class,'WrapperContent__content')]/span[2]",
            "card_contato" => "",
            "card_contato_nome" => "",
            "card_contato_whatsapp" => "",
            "card_localizacao" => "",
            "card_area" => "",
            "card_area_contruida" => "",
            "preco" => ".//h5[contains(@class,'text-lg')]",
            "card_imagem_url" => ".//img[contains(@class,'CardImage__image  ')]",
            "card_url" => ".",
            "galeria" => "//img[contains(@class,'ReactModal__Content')]",
            "descricao" => "//p"
        ]
    ],

    /*----------------------------------------------------------------------------*
     * SITE: Bueno Imóveis
     * ---------------------------------------------------------------------------- */
    [
        "nome_site" => "ImoBueno",
        "contato_nome" => "ImoBueno",
        "usuario" => "imobueno",
        "usuario_email" => "imobueno@imoveisunai.com.br",
        "cidade" => "Formosa",
        "uf" => "Go",
        "categoria" => "",
        "tags" => "",
        "contato_fone" => "(61) 99918-3399",
        "contato_whatsapp" => "(61) 99918-3399",
        "contato_instagram" => "",
        "contato_desc" => "",
        "periodo" => 30,
        "url" => [
            "https://imobueno.com.br/imoveis?purpose=sale",
            "https://imobueno.com.br/imoveis?purpose=rent"
    
        ],
        "numero_registros" => 50,
        "numero_maximo_por_url" => 15,
        "frequencia" => [
            "tipo" => "sempre",
            "horario_inicio" => "22:00",
            "horario_fim" => "12:00"
        ],
        "verificar_string" => "",
        "seletores" => [
            "card" => "//a[contains(@class,'listing-card')]",
            "card_nome" => ".//h4",
            "card_contato" => "",
            "card_contato_nome" => "",
            "card_contato_whatsapp" => "",
            "card_localizacao" => ".//div[contains(@class,'listing-body')]//p",
            "card_area" => "",
            "card_area_contruida" => "",
            "preco" => ".//div[contains(@class,'listing-price')]",
            "card_imagem_url" => ".//img",
            "card_url" => ".",
            "galeria" => "//div[contains(@class,'detail-hero')]",
            "descricao" => "//div[contains(@class,'detail-about')]"
        ]
    ]
];

