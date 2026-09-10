<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// =========================================================
// CONFIGURAÇÕES
// =========================================================

if ( ! defined( 'IMU_WHATSAPP_FORM_NAME' ) ) {

    define(
        'IMU_WHATSAPP_FORM_NAME',
        'imu_whatsapp'
    );
}

if ( ! defined( 'IMU_WHATSAPP_EMAIL_DESTINO' ) ) {

    define(
        'IMU_WHATSAPP_EMAIL_DESTINO',
        'diefersil@gmail.com'
    );
}


// =========================================================
// DADOS DE CONTATO DO IMÓVEL
//
// AUTOR ID = 4
// contato_nome
// contato_fone
// contato_whatsapp
//
// DEMAIS AUTORES
// display_name
// user_fone
// user_whatsapp
// =========================================================

function imu_get_contato_imovel( $post_id ) {

    $post_id = absint( $post_id );

    if ( ! $post_id ) {
        return false;
    }

    if ( get_post_type( $post_id ) !== 'imoveis' ) {
        return false;
    }


    // =====================================================
    // AUTOR DO IMÓVEL
    // =====================================================

    $author_id = (int) get_post_field(
        'post_author',
        $post_id
    );

    if ( ! $author_id ) {
        return false;
    }


    $dados = [
        'author_id' => $author_id,
        'nome'      => '',
        'fone'      => '',
        'whatsapp'  => '',
    ];


    // =====================================================
    // AUTOR ID = 4
    // Dados cadastrados diretamente no imóvel
    // =====================================================

    if ( $author_id === 4 ) {

        $dados['nome'] = get_post_meta(
            $post_id,
            'contato_nome',
            true
        );

        $dados['fone'] = get_post_meta(
            $post_id,
            'contato_fone',
            true
        );

        $dados['whatsapp'] = get_post_meta(
            $post_id,
            'contato_whatsapp',
            true
        );


    // =====================================================
    // DEMAIS AUTORES
    // Dados cadastrados no usuário WordPress
    // =====================================================

    } else {

        $user = get_userdata(
            $author_id
        );

        if ( ! $user ) {
            return false;
        }


        $dados['nome'] =
            $user->display_name;


        $dados['fone'] = get_user_meta(
            $author_id,
            'user_fone',
            true
        );


        $dados['whatsapp'] = get_user_meta(
            $author_id,
            'user_whatsapp',
            true
        );
    }


    return $dados;
}


// =========================================================
// NORMALIZA NÚMERO DE WHATSAPP
// =========================================================

function imu_normalizar_whatsapp( $numero ) {

    $numero = preg_replace(
        '/\D+/',
        '',
        (string) $numero
    );

    if ( empty( $numero ) ) {
        return '';
    }


    // =====================================================
    // CÓDIGO DO BRASIL
    // =====================================================

    if ( substr( $numero, 0, 2 ) !== '55' ) {

        $numero =
            '55' .
            $numero;
    }


    return $numero;
}


// =========================================================
// MONTA URL REAL DO WHATSAPP
// =========================================================

function imu_get_whatsapp_real_url(
    $post_id,
    $nome_visitante = ''
) {

    $post_id = absint(
        $post_id
    );

    if ( ! $post_id ) {
        return '';
    }


    // =====================================================
    // CONTATO DO ANUNCIANTE
    // =====================================================

    $dados = imu_get_contato_imovel(
        $post_id
    );

    if ( ! $dados ) {
        return '';
    }


    // =====================================================
    // WHATSAPP DO ANUNCIANTE
    // =====================================================

    $whatsapp = imu_normalizar_whatsapp(
        $dados['whatsapp']
    );

    if ( empty( $whatsapp ) ) {
        return '';
    }


    // =====================================================
    // TÍTULO DO IMÓVEL
    // =====================================================

    $post_title = get_the_title(
        $post_id
    );


    // =====================================================
    // MENSAGEM
    // =====================================================

    $msg = 'Olá';


    // Nome do anunciante

    if ( ! empty( $dados['nome'] ) ) {

        $msg .=
            ', ' .
            $dados['nome'];
    }


    $msg .= '. ';


    // Nome do visitante

    if ( ! empty( $nome_visitante ) ) {

        $msg .=
            'Meu nome é ' .
            $nome_visitante .
            '. ';
    }


    $msg .=
        'Vi seu imóvel: [' .
        $post_title .
        '], no site Imóveis Unaí e gostaria de saber mais informações.';


    // =====================================================
    // URL
    // =====================================================

    return
        'https://wa.me/' .
        $whatsapp .
        '?text=' .
        rawurlencode( $msg );
}


// =========================================================
// SHORTCODE
//
// [whatsapp_imovel_url]
// =========================================================

function imu_whatsapp_imovel_url_shortcode() {

    $post_id = get_queried_object_id();

    if ( ! $post_id ) {

        $post_id = get_the_ID();
    }

    if ( ! $post_id ) {
        return '';
    }

    if ( get_post_type( $post_id ) !== 'imoveis' ) {
        return '';
    }


    $whatsapp_url = imu_get_whatsapp_real_url(
        $post_id
    );

    if ( empty( $whatsapp_url ) ) {
        return '';
    }


    return esc_url(
        add_query_arg(
            'imu_whatsapp',
            $post_id,
            get_permalink( $post_id )
        )
    );
}


// =========================================================
// REGISTRA SHORTCODE
// =========================================================

add_shortcode(
    'whatsapp_imovel_url',
    'imu_whatsapp_imovel_url_shortcode'
);


// =========================================================
// AJAX - REGISTRA O CLIQUE ASSIM QUE ABRE O FORMULÁRIO
// =========================================================

add_action(
    'wp_ajax_imu_registrar_clique',
    'imu_ajax_registrar_clique'
);

add_action(
    'wp_ajax_nopriv_imu_registrar_clique',
    'imu_ajax_registrar_clique'
);


function imu_ajax_registrar_clique() {

    check_ajax_referer(
        'imu_registrar_clique',
        'nonce'
    );


    $post_id = isset( $_POST['post_id'] )
        ? absint( $_POST['post_id'] )
        : 0;


    if (
        ! $post_id ||
        get_post_type( $post_id ) !== 'imoveis'
    ) {

        wp_send_json_error(
            [
                'message' => 'Imóvel inválido.',
            ],
            400
        );
    }


    if (
        ! function_exists(
            'imu_registrar_clique_whatsapp'
        )
    ) {

        wp_send_json_error(
            [
                'message' => 'Função de registro não disponível.',
            ],
            500
        );
    }


    $click_id = imu_registrar_clique_whatsapp(
        $post_id
    );


    if ( ! $click_id ) {

        wp_send_json_error(
            [
                'message' => 'Não foi possível registrar o clique.',
            ],
            500
        );
    }


    // =====================================================
    // COOKIE DE APOIO
    //
    // Guarda o click_id por 1 hora. Assim, mesmo que o
    // campo hidden click_id não exista no Elementor, o
    // envio do formulário ainda consegue localizar e
    // atualizar o mesmo registro do clique.
    // =====================================================

    $cookie_name =
        'imu_whatsapp_click_' .
        $post_id;


    setcookie(
        $cookie_name,
        (string) $click_id,
        time() + HOUR_IN_SECONDS,
        COOKIEPATH ? COOKIEPATH : '/',
        COOKIE_DOMAIN,
        is_ssl(),
        true
    );


    wp_send_json_success(
        [
            'click_id' => $click_id,
        ]
    );
}


// =========================================================
// VALIDA FORMULÁRIO ELEMENTOR
//
// Form Name:
// imu_whatsapp
//
// IDs esperados:
// nome
// email
// whatsapp
// imovel_id
// click_id  (hidden, pode estar vazio antes do clique)
// =========================================================

add_action(
    'elementor_pro/forms/validation',
    function( $record, $ajax_handler ) {


        $form_name = $record->get_form_settings(
            'form_name'
        );


        if (
            IMU_WHATSAPP_FORM_NAME !==
            $form_name
        ) {

            return;
        }


        $raw_fields = $record->get(
            'fields'
        );


        $fields = [];


        foreach (
            $raw_fields as
            $id => $field
        ) {

            $fields[ $id ] =
                isset( $field['value'] )
                ? $field['value']
                : '';
        }


        // =================================================
        // NOME
        // =================================================

        $nome = isset( $fields['nome'] )
            ? trim( $fields['nome'] )
            : '';


        if ( empty( $nome ) ) {

            $ajax_handler->add_error(
                'nome',
                'Informe seu nome.'
            );
        }


        // =================================================
        // EMAIL
        // =================================================

        $email = isset( $fields['email'] )
            ? trim( $fields['email'] )
            : '';


        if (
            empty( $email ) ||
            ! is_email( $email )
        ) {

            $ajax_handler->add_error(
                'email',
                'Informe um e-mail válido.'
            );
        }


        // =================================================
        // WHATSAPP
        // =================================================

        $whatsapp = isset( $fields['whatsapp'] )
            ? preg_replace(
                '/\D+/',
                '',
                $fields['whatsapp']
            )
            : '';


        if (
            empty( $whatsapp ) ||
            strlen( $whatsapp ) < 10
        ) {

            $ajax_handler->add_error(
                'whatsapp',
                'Informe um WhatsApp válido.'
            );
        }


        // =================================================
        // IMÓVEL
        // =================================================

        $post_id = isset( $fields['imovel_id'] )
            ? absint( $fields['imovel_id'] )
            : 0;


        if (
            ! $post_id ||
            get_post_type( $post_id ) !== 'imoveis'
        ) {

            $ajax_handler->add_error(
                'imovel_id',
                'Não foi possível identificar o imóvel.'
            );
        }

    },
    10,
    2
);


// =========================================================
// CAPTURA ENVIO DO FORMULÁRIO ELEMENTOR
//
// O clique já foi salvo antes de abrir o form.
// Aqui atualizamos o MESMO registro para status "lead".
// =========================================================

add_action(
    'elementor_pro/forms/new_record',
    function( $record, $ajax_handler ) {


        // =================================================
        // IDENTIFICA O FORMULÁRIO
        // =================================================

        $form_name = $record->get_form_settings(
            'form_name'
        );


        if (
            IMU_WHATSAPP_FORM_NAME !==
            $form_name
        ) {

            return;
        }


        // =================================================
        // PEGA OS CAMPOS
        // =================================================

        $raw_fields = $record->get(
            'fields'
        );


        $fields = [];


        foreach (
            $raw_fields as
            $id => $field
        ) {

            $fields[ $id ] =
                isset( $field['value'] )
                ? $field['value']
                : '';
        }


        // =================================================
        // DADOS DO VISITANTE
        // =================================================

        $nome = isset( $fields['nome'] )
            ? sanitize_text_field(
                $fields['nome']
            )
            : '';


        $email = isset( $fields['email'] )
            ? sanitize_email(
                $fields['email']
            )
            : '';


        $whatsapp_visitante =
            isset( $fields['whatsapp'] )
            ? sanitize_text_field(
                $fields['whatsapp']
            )
            : '';


        // =================================================
        // ID DO IMÓVEL
        // =================================================

        $post_id =
            isset( $fields['imovel_id'] )
            ? absint(
                $fields['imovel_id']
            )
            : 0;


        if ( ! $post_id ) {
            return;
        }


        if (
            get_post_type( $post_id )
            !== 'imoveis'
        ) {

            return;
        }


        // =================================================
        // CLICK ID
        //
        // Hidden field do Elementor:
        // ID = click_id
        // =================================================

        $click_id =
            isset( $fields['click_id'] )
            ? absint(
                $fields['click_id']
            )
            : 0;


        // =================================================
        // FALLBACK PELO COOKIE
        //
        // Se o campo hidden click_id não estiver no
        // formulário Elementor, tenta recuperar o ID do
        // clique registrado ao abrir o formulário.
        // =================================================

        if ( ! $click_id ) {

            $cookie_name =
                'imu_whatsapp_click_' .
                $post_id;


            if ( ! empty( $_COOKIE[ $cookie_name ] ) ) {

                $click_id = absint(
                    wp_unslash(
                        $_COOKIE[ $cookie_name ]
                    )
                );
            }
        }


        // =================================================
        // GARANTE QUE EXISTA UM REGISTRO
        //
        // Se por algum motivo o AJAX do clique falhou,
        // cria um registro agora para não perder o lead.
        // =================================================

        if (
            ! $click_id &&
            function_exists(
                'imu_registrar_clique_whatsapp'
            )
        ) {

            $click_id = imu_registrar_clique_whatsapp(
                $post_id
            );
        }


        // =================================================
        // ATUALIZA O MESMO REGISTRO PARA LEAD
        // =================================================

        if (
            $click_id &&
            function_exists(
                'imu_atualizar_clique_whatsapp'
            )
        ) {

            imu_atualizar_clique_whatsapp(
                $click_id,
                $post_id,
                $nome,
                $email,
                $whatsapp_visitante
            );


            // =============================================
            // REMOVE COOKIE DE APOIO APÓS VIRAR LEAD
            // =============================================

            $cookie_name =
                'imu_whatsapp_click_' .
                $post_id;


            setcookie(
                $cookie_name,
                '',
                time() - HOUR_IN_SECONDS,
                COOKIEPATH ? COOKIEPATH : '/',
                COOKIE_DOMAIN,
                is_ssl(),
                true
            );
        }


        // =================================================
        // ID DO AUTOR
        // =================================================

        $autor_id =
            (int) get_post_field(
                'post_author',
                $post_id
            );


        // =================================================
        // DATA
        // =================================================

        $data =
            current_time(
                'mysql'
            );


        // =================================================
        // IP
        // =================================================

        $ip = '';


        if (
            ! empty(
                $_SERVER['REMOTE_ADDR']
            )
        ) {

            $ip =
                sanitize_text_field(
                    wp_unslash(
                        $_SERVER['REMOTE_ADDR']
                    )
                );
        }


        // =================================================
        // CATEGORIA
        // =================================================

        $categorias =
            wp_get_post_terms(
                $post_id,
                'categoria',
                [
                    'fields' => 'names',
                ]
            );


        $categoria = '';


        if (
            ! is_wp_error( $categorias ) &&
            ! empty( $categorias )
        ) {

            $categoria =
                implode(
                    ', ',
                    $categorias
                );
        }


        // =================================================
        // DADOS DO CONTATO / ANUNCIANTE
        // =================================================

        $dados_contato =
            imu_get_contato_imovel(
                $post_id
            );


        if ( ! $dados_contato ) {
            return;
        }


        // =================================================
        // DADOS DO IMÓVEL
        // =================================================

        $titulo =
            get_the_title(
                $post_id
            );


        $link_imovel =
            get_permalink(
                $post_id
            );


        // =================================================
        // EMAIL
        // =================================================

        $email_destino =
            IMU_WHATSAPP_EMAIL_DESTINO;


        $assunto =
            'Novo Lead WhatsApp - ' .
            $titulo;


        $mensagem_email = '';


        $mensagem_email .=
            "NOVO LEAD - IMÓVEIS UNAÍ\n\n";


        // =================================================
        // VISITANTE
        // =================================================

        $mensagem_email .=
            "DADOS DO INTERESSADO\n";

        $mensagem_email .=
            "------------------------------\n";


        $mensagem_email .=
            "Nome: " .
            $nome .
            "\n";


        $mensagem_email .=
            "E-mail: " .
            $email .
            "\n";


        $mensagem_email .=
            "WhatsApp: " .
            $whatsapp_visitante .
            "\n\n";


        // =================================================
        // IMÓVEL
        // =================================================

        $mensagem_email .=
            "DADOS DO IMÓVEL\n";

        $mensagem_email .=
            "------------------------------\n";


        $mensagem_email .=
            "Imóvel: " .
            $titulo .
            "\n";


        $mensagem_email .=
            "ID do imóvel: " .
            $post_id .
            "\n";


        $mensagem_email .=
            "Categoria: " .
            $categoria .
            "\n";


        $mensagem_email .=
            "URL: " .
            $link_imovel .
            "\n\n";


        // =================================================
        // ANUNCIANTE
        // =================================================

        $mensagem_email .=
            "DADOS DO ANUNCIANTE\n";

        $mensagem_email .=
            "------------------------------\n";


        $mensagem_email .=
            "Contato: " .
            $dados_contato['nome'] .
            "\n";


        $mensagem_email .=
            "Fone: " .
            $dados_contato['fone'] .
            "\n";


        $mensagem_email .=
            "WhatsApp: " .
            $dados_contato['whatsapp'] .
            "\n";


        $mensagem_email .=
            "Autor ID: " .
            $autor_id .
            "\n\n";


        // =================================================
        // ACESSO
        // =================================================

        $mensagem_email .=
            "DADOS DO ACESSO\n";

        $mensagem_email .=
            "------------------------------\n";


        $mensagem_email .=
            "Registro do clique: #" .
            $click_id .
            "\n";


        $mensagem_email .=
            "Data/Hora: " .
            mysql2date(
                'd/m/Y H:i:s',
                $data
            ) .
            "\n";


        $mensagem_email .=
            "IP: " .
            $ip .
            "\n";


        // =================================================
        // ENVIA EMAIL
        // =================================================

        wp_mail(
            $email_destino,
            $assunto,
            $mensagem_email
        );


        // =================================================
        // MONTA WHATSAPP DO ANUNCIANTE
        // =================================================

        $whatsapp_url =
            imu_get_whatsapp_real_url(
                $post_id,
                $nome
            );


        if ( empty( $whatsapp_url ) ) {
            return;
        }


        // =================================================
        // REDIRECIONAMENTO ELEMENTOR AJAX
        // =================================================

        $ajax_handler->add_response_data(
            'redirect_url',
            $whatsapp_url
        );

    },
    10,
    2
);


// =========================================================
// FORM WHATSAPP
//
// - abre / fecha
// - expande container no mobile
// - registra clique mesmo se a pessoa não preencher o form
// - coloca click_id no campo hidden do Elementor
// =========================================================

add_action( 'wp_footer', function() {

    if ( ! is_singular( 'imoveis' ) ) {
        return;
    }


    $post_id = get_queried_object_id();

    if ( ! $post_id ) {
        return;
    }


    $ajax_url = admin_url(
        'admin-ajax.php'
    );


    $nonce = wp_create_nonce(
        'imu_registrar_clique'
    );

    ?>

    <style>

        /* =====================================================
           FORMULÁRIO FECHADO
        ===================================================== */

        .imu-form-whatsapp {
            display: none !important;
            position: relative;
            width: 100%;
        }


        /* =====================================================
           FORMULÁRIO ABERTO
        ===================================================== */

        .imu-form-whatsapp.imu-form-aberto {
            display: flex !important;
            width: 100%;
        }


        /* =====================================================
           BOTÃO X
        ===================================================== */

        .imu-fechar-form-whatsapp {

            position: absolute;

            top: 0;
            right: 0;

            width: 32px;
            height: 32px;

            padding: 0 !important;
            margin: 0 !important;

            border: 0 !important;

            background: transparent !important;

            color: #000;

            font-size: 30px;
            font-weight: 400;

            line-height: 32px;

            text-align: center;

            cursor: pointer;

            z-index: 100;
        }


        /* =====================================================
           MOBILE
           QUANDO ABERTO, CONTATO OCUPA O GRID INTEIRO
        ===================================================== */

        @media (max-width: 767px) {

            .imu-contato-container.imu-contato-expandido {

                grid-column: 1 / -1 !important;

                width: 100% !important;

                max-width: 100% !important;

                flex-basis: 100% !important;
            }


            .imu-contato-container.imu-contato-expandido
            .imu-form-whatsapp {

                width: 100% !important;

                max-width: 100% !important;
            }


            .imu-contato-container.imu-contato-expandido
            .elementor-widget-form {

                width: 100% !important;

                max-width: 100% !important;
            }


            .imu-contato-container.imu-contato-expandido
            .elementor-form {

                width: 100% !important;
            }

        }

    </style>


    <script>

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            // =================================================
            // ELEMENTOS
            // =================================================

            const botao = document.querySelector(
                '.imu-abrir-form-whatsapp'
            );

            const form = document.querySelector(
                '.imu-form-whatsapp'
            );

            const contatoContainer = document.querySelector(
                '.imu-contato-container'
            );


            if (!botao) {

                console.log(
                    'IMU: botão de abrir formulário não encontrado.'
                );

                return;
            }


            if (!form) {

                console.log(
                    'IMU: container do formulário não encontrado.'
                );

                return;
            }


            if (!contatoContainer) {

                console.log(
                    'IMU: container de contato não encontrado.'
                );

                return;
            }


            // =================================================
            // CAMPO HIDDEN click_id DO ELEMENTOR
            // =================================================

            const clickIdInput = form.querySelector(
                'input[name="form_fields[click_id]"]'
            );


            if (!clickIdInput) {

                console.log(
                    'IMU: campo hidden click_id não encontrado no formulário Elementor.'
                );
            }


            // =================================================
            // CONTROLE DO REGISTRO DO CLIQUE
            //
            // 1 clique por carregamento da página.
            // Abrir, fechar e abrir novamente não cria
            // vários registros.
            // =================================================

            let clickId = 0;

            let registrandoClique = false;

            let cliqueRegistrado = false;


            // =================================================
            // CRIA BOTÃO X
            // =================================================

            let fechar = form.querySelector(
                '.imu-fechar-form-whatsapp'
            );


            if (!fechar) {

                fechar = document.createElement(
                    'button'
                );


                fechar.type =
                    'button';


                fechar.className =
                    'imu-fechar-form-whatsapp';


                fechar.innerHTML =
                    '&times;';


                fechar.setAttribute(
                    'aria-label',
                    'Fechar formulário'
                );


                form.prepend(
                    fechar
                );
            }


            // =================================================
            // REGISTRA O CLIQUE NO BANCO
            // =================================================

            async function registrarClique() {

                if (
                    cliqueRegistrado ||
                    registrandoClique
                ) {
                    return;
                }


                registrandoClique = true;


                const dados = new FormData();


                dados.append(
                    'action',
                    'imu_registrar_clique'
                );


                dados.append(
                    'post_id',
                    <?php echo wp_json_encode( (int) $post_id ); ?>
                );


                dados.append(
                    'nonce',
                    <?php echo wp_json_encode( $nonce ); ?>
                );


                try {

                    const resposta = await fetch(
                        <?php echo wp_json_encode( $ajax_url ); ?>,
                        {
                            method: 'POST',

                            body: dados,

                            credentials: 'same-origin'
                        }
                    );


                    const json = await resposta.json();


                    if (
                        json.success &&
                        json.data &&
                        json.data.click_id
                    ) {

                        clickId =
                            parseInt(
                                json.data.click_id,
                                10
                            ) || 0;


                        cliqueRegistrado =
                            clickId > 0;


                        // =====================================
                        // COLOCA O ID DO REGISTRO NO FORM
                        // =====================================

                        if (
                            cliqueRegistrado &&
                            clickIdInput
                        ) {

                            clickIdInput.value =
                                String(
                                    clickId
                                );


                            // Dispara change para garantir
                            // que o Elementor reconheça o valor.

                            clickIdInput.dispatchEvent(
                                new Event(
                                    'change',
                                    {
                                        bubbles: true
                                    }
                                )
                            );
                        }

                    } else {

                        console.log(
                            'IMU: não foi possível registrar o clique.',
                            json
                        );
                    }


                } catch (erro) {

                    console.log(
                        'IMU: erro ao registrar clique.',
                        erro
                    );

                } finally {

                    registrandoClique = false;
                }
            }


            // =================================================
            // ABRIR
            // =================================================

            function abrirFormulario() {

                form.classList.add(
                    'imu-form-aberto'
                );


                contatoContainer.classList.add(
                    'imu-contato-expandido'
                );


                // Registra imediatamente o interesse,
                // mesmo que o visitante nunca envie o form.

                registrarClique();
            }


            // =================================================
            // FECHAR
            // =================================================

            function fecharFormulario() {

                form.classList.remove(
                    'imu-form-aberto'
                );


                contatoContainer.classList.remove(
                    'imu-contato-expandido'
                );
            }


            // =================================================
            // CLIQUE NO "FALAR COM VENDEDOR"
            // =================================================

            botao.addEventListener(
                'click',
                function (event) {

                    event.preventDefault();


                    if (
                        form.classList.contains(
                            'imu-form-aberto'
                        )
                    ) {

                        fecharFormulario();

                    } else {

                        abrirFormulario();
                    }

                }
            );


            // =================================================
            // CLIQUE NO X
            // =================================================

            fechar.addEventListener(
                'click',
                function (event) {

                    event.preventDefault();

                    event.stopPropagation();

                    fecharFormulario();

                }
            );

        }
    );

    </script>

    <?php

}, 99);
