<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// =========================================================
// CONFIGURAÇÃO DA TABELA
// =========================================================

function imu_whatsapp_nome_tabela() {

    global $wpdb;

    return $wpdb->prefix . 'imu_whatsapp_cliques';
}


// =========================================================
// CRIAR / ATUALIZAR TABELA
//
// Versão 1.3:
// - mantém os registros antigos
// - adiciona nome, email e WhatsApp do visitante
// - adiciona status: clique / lead
// =========================================================

function imu_whatsapp_criar_tabela() {

    global $wpdb;

    $tabela = imu_whatsapp_nome_tabela();

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$tabela} (

        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

        imovel_id BIGINT UNSIGNED NOT NULL,

        data_clique DATETIME NOT NULL,

        ip VARCHAR(45) NOT NULL DEFAULT '',

        autor_id BIGINT UNSIGNED NOT NULL DEFAULT 0,

        categoria TEXT NULL,

        nome_visitante VARCHAR(190) NOT NULL DEFAULT '',

        email_visitante VARCHAR(190) NOT NULL DEFAULT '',

        whatsapp_visitante VARCHAR(50) NOT NULL DEFAULT '',

        status VARCHAR(20) NOT NULL DEFAULT 'clique',

        PRIMARY KEY (id),

        KEY imovel_id (imovel_id),

        KEY autor_id (autor_id),

        KEY data_clique (data_clique),

        KEY status (status)

    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( $sql );

    update_option(
        'imu_whatsapp_db_version',
        '1.3'
    );
}


// =========================================================
// VERIFICA SE A TABELA PRECISA SER CRIADA / ATUALIZADA
// =========================================================

add_action( 'init', function() {

    $versao = get_option(
        'imu_whatsapp_db_version'
    );

    if ( $versao !== '1.3' ) {

        imu_whatsapp_criar_tabela();
    }

});


// =========================================================
// REGISTRAR CLIQUE
//
// Executada assim que a pessoa clica em "Falar com Vendedor".
//
// Retorna o ID do registro criado para depois atualizar
// o MESMO registro quando o formulário for enviado.
// =========================================================

function imu_registrar_clique_whatsapp( $post_id ) {

    global $wpdb;

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

    $autor_id = (int) get_post_field(
        'post_author',
        $post_id
    );


    // =====================================================
    // CATEGORIA DO IMÓVEL
    // =====================================================

    $categorias = wp_get_post_terms(
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

        $categoria = implode(
            ', ',
            $categorias
        );
    }


    // =====================================================
    // IP
    // =====================================================

    $ip = '';

    if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {

        $ip = sanitize_text_field(
            wp_unslash(
                $_SERVER['REMOTE_ADDR']
            )
        );
    }


    // =====================================================
    // GRAVA O CLIQUE
    // =====================================================

    $resultado = $wpdb->insert(

        imu_whatsapp_nome_tabela(),

        [
            'imovel_id'          => $post_id,
            'data_clique'        => current_time( 'mysql' ),
            'ip'                 => $ip,
            'autor_id'           => $autor_id,
            'categoria'          => $categoria,
            'nome_visitante'     => '',
            'email_visitante'    => '',
            'whatsapp_visitante' => '',
            'status'             => 'clique',
        ],

        [
            '%d',
            '%s',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
        ]
    );


    if ( false === $resultado ) {
        return false;
    }


    return (int) $wpdb->insert_id;
}


// =========================================================
// ATUALIZAR CLIQUE PARA LEAD
//
// Quando o formulário Elementor é enviado, atualiza
// o MESMO registro criado no clique.
//
// O post_id também entra no WHERE para impedir que um
// click_id de outro imóvel seja usado por engano.
// =========================================================

function imu_atualizar_clique_whatsapp(
    $click_id,
    $post_id,
    $nome,
    $email,
    $whatsapp
) {

    global $wpdb;

    $click_id = absint( $click_id );
    $post_id  = absint( $post_id );

    if ( ! $click_id || ! $post_id ) {
        return false;
    }

    if ( get_post_type( $post_id ) !== 'imoveis' ) {
        return false;
    }


    $resultado = $wpdb->update(

        imu_whatsapp_nome_tabela(),

        [
            'nome_visitante'     => sanitize_text_field( $nome ),
            'email_visitante'    => sanitize_email( $email ),
            'whatsapp_visitante' => sanitize_text_field( $whatsapp ),
            'status'             => 'lead',
        ],

        [
            'id'        => $click_id,
            'imovel_id' => $post_id,
        ],

        [
            '%s',
            '%s',
            '%s',
            '%s',
        ],

        [
            '%d',
            '%d',
        ]
    );


    return $resultado;
}


// =========================================================
// MENU ADMIN
// Imóveis → Cliques WhatsApp
// =========================================================

add_action( 'admin_menu', function() {

    add_submenu_page(

        'edit.php?post_type=imoveis',

        'Cliques WhatsApp',

        'Cliques WhatsApp',

        'manage_options',

        'imu-whatsapp-cliques',

        'imu_whatsapp_pagina_admin'
    );

});


// =========================================================
// PÁGINA ADMIN
// =========================================================

function imu_whatsapp_pagina_admin() {

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    global $wpdb;

    $tabela = imu_whatsapp_nome_tabela();


    // =====================================================
    // PAGINAÇÃO
    // =====================================================

    $por_pagina = 50;

    $pagina_atual = isset( $_GET['paged'] )
        ? max( 1, absint( $_GET['paged'] ) )
        : 1;

    $offset =
        ( $pagina_atual - 1 )
        * $por_pagina;


    // =====================================================
    // INDICADORES
    // =====================================================

    $total = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$tabela}"
    );

    $total_leads = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$tabela} WHERE status = %s",
            'lead'
        )
    );

    $total_so_cliques = max(
        0,
        $total - $total_leads
    );

    $conversao = $total > 0
        ? ( $total_leads / $total ) * 100
        : 0;


    // =====================================================
    // REGISTROS
    // =====================================================

    $registros = $wpdb->get_results(

        $wpdb->prepare(

            "SELECT *
             FROM {$tabela}
             ORDER BY data_clique DESC
             LIMIT %d OFFSET %d",

            $por_pagina,
            $offset
        )
    );


    // =====================================================
    // TOTAL DE PÁGINAS
    // =====================================================

    $total_paginas = max(
        1,
        ceil(
            $total / $por_pagina
        )
    );

    ?>

    <div class="wrap">

        <h1>
            Cliques no WhatsApp
        </h1>


        <!-- =================================================
        INDICADORES
        ================================================== -->

        <div
            style="
                display:flex;
                flex-wrap:wrap;
                gap:12px;
                margin:20px 0;
            "
        >

            <div
                style="
                    background:#fff;
                    border:1px solid #dcdcde;
                    padding:14px 18px;
                    min-width:160px;
                "
            >
                <div style="color:#646970;">
                    Total de cliques
                </div>

                <strong style="font-size:24px;">
                    <?php echo number_format_i18n( $total ); ?>
                </strong>
            </div>


            <div
                style="
                    background:#fff;
                    border:1px solid #dcdcde;
                    padding:14px 18px;
                    min-width:160px;
                "
            >
                <div style="color:#646970;">
                    Leads preenchidos
                </div>

                <strong style="font-size:24px;">
                    <?php echo number_format_i18n( $total_leads ); ?>
                </strong>
            </div>


            <div
                style="
                    background:#fff;
                    border:1px solid #dcdcde;
                    padding:14px 18px;
                    min-width:160px;
                "
            >
                <div style="color:#646970;">
                    Não preencheram
                </div>

                <strong style="font-size:24px;">
                    <?php echo number_format_i18n( $total_so_cliques ); ?>
                </strong>
            </div>


            <div
                style="
                    background:#fff;
                    border:1px solid #dcdcde;
                    padding:14px 18px;
                    min-width:160px;
                "
            >
                <div style="color:#646970;">
                    Conversão
                </div>

                <strong style="font-size:24px;">
                    <?php echo esc_html( number_format_i18n( $conversao, 1 ) ); ?>%
                </strong>
            </div>

        </div>


        <!-- =================================================
        TABELA
        ================================================== -->

        <div style="overflow-x:auto;">

            <table
                class="wp-list-table widefat striped"
                style="min-width:1450px;"
            >

                <thead>

                    <tr>

                        <th style="width:65px;">
                            ID
                        </th>

                        <th style="width:220px;">
                            Imóvel
                        </th>

                        <th style="width:155px;">
                            Data
                        </th>

                        <th style="width:150px;">
                            Nome
                        </th>

                        <th style="width:190px;">
                            E-mail
                        </th>

                        <th style="width:150px;">
                            WhatsApp
                        </th>

                        <th style="width:120px;">
                            Status
                        </th>

                        <th style="width:140px;">
                            IP
                        </th>

                        <th style="width:170px;">
                            Autor
                        </th>

                        <th style="width:170px;">
                            Categoria
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php

                if ( ! empty( $registros ) ) :

                    foreach ( $registros as $registro ) :


                        // =====================================
                        // IMÓVEL
                        // =====================================

                        $titulo_imovel = get_the_title(
                            $registro->imovel_id
                        );

                        if ( ! $titulo_imovel ) {

                            $titulo_imovel =
                                'Imóvel #' .
                                $registro->imovel_id;
                        }


                        $link_imovel = get_permalink(
                            $registro->imovel_id
                        );


                        // =====================================
                        // AUTOR
                        // =====================================

                        $autor = get_userdata(
                            $registro->autor_id
                        );

                        if ( $autor ) {

                            $nome_autor =
                                $autor->display_name;

                        } else {

                            $nome_autor =
                                'Autor #' .
                                $registro->autor_id;
                        }


                        // =====================================
                        // COMPATIBILIDADE COM REGISTROS ANTIGOS
                        // =====================================

                        $nome_visitante = isset( $registro->nome_visitante )
                            ? $registro->nome_visitante
                            : '';

                        $email_visitante = isset( $registro->email_visitante )
                            ? $registro->email_visitante
                            : '';

                        $whatsapp_visitante = isset( $registro->whatsapp_visitante )
                            ? $registro->whatsapp_visitante
                            : '';

                        $status = isset( $registro->status ) && $registro->status
                            ? $registro->status
                            : 'clique';

                        ?>

                        <tr>

                            <!-- ID -->

                            <td>

                                <?php
                                echo esc_html(
                                    $registro->id
                                );
                                ?>

                            </td>


                            <!-- IMÓVEL -->

                            <td>

                                <?php if ( $link_imovel ) : ?>

                                    <a
                                        href="<?php echo esc_url( $link_imovel ); ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >

                                        <strong>

                                            <?php
                                            echo esc_html(
                                                $titulo_imovel
                                            );
                                            ?>

                                        </strong>

                                    </a>

                                <?php else : ?>

                                    <?php
                                    echo esc_html(
                                        $titulo_imovel
                                    );
                                    ?>

                                <?php endif; ?>


                                <br>


                                <small>

                                    ID:

                                    <?php
                                    echo esc_html(
                                        $registro->imovel_id
                                    );
                                    ?>

                                </small>

                            </td>


                            <!-- DATA -->

                            <td>

                                <?php

                                echo esc_html(

                                    mysql2date(
                                        'd/m/Y H:i:s',
                                        $registro->data_clique
                                    )

                                );

                                ?>

                            </td>


                            <!-- NOME -->

                            <td>

                                <?php if ( $nome_visitante ) : ?>

                                    <strong>
                                        <?php echo esc_html( $nome_visitante ); ?>
                                    </strong>

                                <?php else : ?>

                                    <span style="color:#999;">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- EMAIL -->

                            <td>

                                <?php if ( $email_visitante ) : ?>

                                    <a href="mailto:<?php echo esc_attr( $email_visitante ); ?>">
                                        <?php echo esc_html( $email_visitante ); ?>
                                    </a>

                                <?php else : ?>

                                    <span style="color:#999;">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- WHATSAPP VISITANTE -->

                            <td>

                                <?php if ( $whatsapp_visitante ) : ?>

                                    <?php echo esc_html( $whatsapp_visitante ); ?>

                                <?php else : ?>

                                    <span style="color:#999;">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php if ( 'lead' === $status ) : ?>

                                    <span
                                        style="
                                            display:inline-block;
                                            background:#d7f5df;
                                            color:#176b2c;
                                            padding:4px 8px;
                                            border-radius:4px;
                                            font-weight:600;
                                        "
                                    >
                                        Lead
                                    </span>

                                <?php else : ?>

                                    <span
                                        style="
                                            display:inline-block;
                                            background:#fff3cd;
                                            color:#7a5d00;
                                            padding:4px 8px;
                                            border-radius:4px;
                                            font-weight:600;
                                        "
                                    >
                                        Clique
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- IP -->

                            <td>

                                <?php
                                echo esc_html(
                                    $registro->ip
                                );
                                ?>

                            </td>


                            <!-- AUTOR -->

                            <td>

                                <?php
                                echo esc_html(
                                    $nome_autor
                                );
                                ?>

                                <br>

                                <small>

                                    ID:

                                    <?php
                                    echo esc_html(
                                        $registro->autor_id
                                    );
                                    ?>

                                </small>

                            </td>


                            <!-- CATEGORIA -->

                            <td>

                                <?php
                                echo esc_html(
                                    $registro->categoria
                                );
                                ?>

                            </td>

                        </tr>

                        <?php

                    endforeach;

                else :

                    ?>

                    <tr>

                        <td colspan="10">

                            Nenhum clique registrado ainda.

                        </td>

                    </tr>

                    <?php

                endif;

                ?>

                </tbody>

            </table>

        </div>


        <?php

        // =================================================
        // PAGINAÇÃO
        // =================================================

        if ( $total_paginas > 1 ) {

            echo '<div style="margin-top:20px;">';

            echo paginate_links(

                [
                    'base' => add_query_arg(
                        'paged',
                        '%#%'
                    ),

                    'format' => '',

                    'current' => $pagina_atual,

                    'total' => $total_paginas,

                    'prev_text' => '« Anterior',

                    'next_text' => 'Próxima »',
                ]
            );

            echo '</div>';
        }

        ?>

    </div>

    <?php
}
