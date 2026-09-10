<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// =========================================================
// CONFIGURAÇÃO
// =========================================================

function imu_whatsapp_nome_tabela() {

    global $wpdb;

    return $wpdb->prefix . 'imu_whatsapp_cliques';
}


function imu_whatsapp_get_ip_atual() {

    if ( empty( $_SERVER['REMOTE_ADDR'] ) ) {
        return '';
    }

    return sanitize_text_field(
        wp_unslash( $_SERVER['REMOTE_ADDR'] )
    );
}


// =========================================================
// IPS BLOQUEADOS
//
// O bloqueio vale para o módulo de WhatsApp:
// - não registra novos cliques
// - não aceita lead pelo formulário
// Não bloqueia o acesso ao restante do site.
// =========================================================

function imu_whatsapp_get_ips_bloqueados() {

    $ips = get_option(
        'imu_whatsapp_ips_bloqueados',
        []
    );

    return is_array( $ips ) ? $ips : [];
}


function imu_whatsapp_ip_bloqueado( $ip ) {

    $ip = trim( (string) $ip );

    if ( ! $ip ) {
        return false;
    }

    $ips = imu_whatsapp_get_ips_bloqueados();

    return isset( $ips[ $ip ] );
}


function imu_whatsapp_bloquear_ip( $ip ) {

    $ip = trim( sanitize_text_field( $ip ) );

    if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
        return false;
    }

    $ips = imu_whatsapp_get_ips_bloqueados();

    $ips[ $ip ] = [
        'data' => current_time( 'mysql' ),
    ];

    update_option(
        'imu_whatsapp_ips_bloqueados',
        $ips,
        false
    );

    return true;
}


function imu_whatsapp_desbloquear_ip( $ip ) {

    $ip = trim( sanitize_text_field( $ip ) );

    $ips = imu_whatsapp_get_ips_bloqueados();

    if ( ! isset( $ips[ $ip ] ) ) {
        return true;
    }

    unset( $ips[ $ip ] );

    update_option(
        'imu_whatsapp_ips_bloqueados',
        $ips,
        false
    );

    return true;
}


// =========================================================
// CRIAR / ATUALIZAR TABELA
//
// Versão 1.4:
// - mantém registros antigos
// - nome / email / WhatsApp do visitante
// - status clique / lead
// - filtros, exclusão e bloqueio de IP no painel
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
        KEY status (status),
        KEY ip (ip)

    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( $sql );

    update_option(
        'imu_whatsapp_db_version',
        '1.4'
    );
}


add_action( 'init', function() {

    $versao = get_option(
        'imu_whatsapp_db_version'
    );

    if ( $versao !== '1.4' ) {
        imu_whatsapp_criar_tabela();
    }
} );


// =========================================================
// REGISTRAR CLIQUE
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

    $ip = imu_whatsapp_get_ip_atual();

    if ( imu_whatsapp_ip_bloqueado( $ip ) ) {
        return false;
    }

    $autor_id = (int) get_post_field(
        'post_author',
        $post_id
    );

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
        $categoria = implode( ', ', $categorias );
    }

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
// ATUALIZAR O MESMO CLIQUE PARA LEAD
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

    if ( imu_whatsapp_ip_bloqueado( imu_whatsapp_get_ip_atual() ) ) {
        return false;
    }

    return $wpdb->update(
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
}


// =========================================================
// MENU ADMIN
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
} );


// =========================================================
// AÇÕES ADMIN: EXCLUIR / BLOQUEAR / DESBLOQUEAR
// =========================================================

add_action( 'admin_init', function() {

    if ( empty( $_POST['imu_whatsapp_admin_action'] ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    check_admin_referer(
        'imu_whatsapp_admin_action',
        'imu_whatsapp_admin_nonce'
    );

    global $wpdb;

    $acao = sanitize_key(
        wp_unslash( $_POST['imu_whatsapp_admin_action'] )
    );

    $pagina_admin = admin_url(
        'edit.php?post_type=imoveis&page=imu-whatsapp-cliques'
    );

    $retorno = isset( $_POST['return_url'] )
        ? wp_validate_redirect(
            esc_url_raw( wp_unslash( $_POST['return_url'] ) ),
            $pagina_admin
        )
        : $pagina_admin;

    $notice = '';

    if ( 'excluir' === $acao ) {

        $registro_id = isset( $_POST['registro_id'] )
            ? absint( $_POST['registro_id'] )
            : 0;

        if ( $registro_id ) {
            $wpdb->delete(
                imu_whatsapp_nome_tabela(),
                [ 'id' => $registro_id ],
                [ '%d' ]
            );

            $notice = 'excluido';
        }
    }

    if ( 'bloquear_ip' === $acao ) {

        $ip = isset( $_POST['ip'] )
            ? sanitize_text_field( wp_unslash( $_POST['ip'] ) )
            : '';

        if ( imu_whatsapp_bloquear_ip( $ip ) ) {
            $notice = 'ip_bloqueado';
        }
    }

    if ( 'desbloquear_ip' === $acao ) {

        $ip = isset( $_POST['ip'] )
            ? sanitize_text_field( wp_unslash( $_POST['ip'] ) )
            : '';

        if ( $ip ) {
            imu_whatsapp_desbloquear_ip( $ip );
            $notice = 'ip_desbloqueado';
        }
    }

    if ( $notice ) {
        $retorno = add_query_arg(
            'imu_notice',
            $notice,
            $retorno
        );
    }

    wp_safe_redirect( $retorno );
    exit;
} );


// =========================================================
// VALIDA DATA YYYY-MM-DD
// =========================================================

function imu_whatsapp_admin_data_valida( $data ) {

    $data = sanitize_text_field( (string) $data );

    if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $data ) ) {
        return '';
    }

    $obj = DateTime::createFromFormat( 'Y-m-d', $data );

    if ( ! $obj || $obj->format( 'Y-m-d' ) !== $data ) {
        return '';
    }

    return $data;
}


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
    // FILTROS
    // =====================================================

    $filtro_autor_raw = isset( $_GET['autor_id'] )
        ? sanitize_text_field( wp_unslash( $_GET['autor_id'] ) )
        : '';

    $filtro_autor_ativo = ( '' !== $filtro_autor_raw );
    $filtro_autor       = $filtro_autor_ativo ? (int) $filtro_autor_raw : 0;

    $filtro_status = isset( $_GET['status'] )
        ? sanitize_key( wp_unslash( $_GET['status'] ) )
        : '';

    if ( ! in_array( $filtro_status, [ '', 'clique', 'lead' ], true ) ) {
        $filtro_status = '';
    }

    $data_inicio = isset( $_GET['data_inicio'] )
        ? imu_whatsapp_admin_data_valida( wp_unslash( $_GET['data_inicio'] ) )
        : '';

    $data_fim = isset( $_GET['data_fim'] )
        ? imu_whatsapp_admin_data_valida( wp_unslash( $_GET['data_fim'] ) )
        : '';


    // =====================================================
    // WHERE DINÂMICO
    // =====================================================

    $where = [ '1=1' ];
    $args  = [];

    if ( $filtro_autor_ativo ) {
        $where[] = 'autor_id = %d';
        $args[]  = $filtro_autor;
    }

    if ( $filtro_status ) {
        $where[] = 'status = %s';
        $args[]  = $filtro_status;
    }

    if ( $data_inicio ) {
        $where[] = 'data_clique >= %s';
        $args[]  = $data_inicio . ' 00:00:00';
    }

    if ( $data_fim ) {
        $where[] = 'data_clique <= %s';
        $args[]  = $data_fim . ' 23:59:59';
    }

    $where_sql = implode( ' AND ', $where );


    // =====================================================
    // INDICADORES RESPEITAM OS FILTROS
    // =====================================================

    $sql_total = "SELECT COUNT(*) FROM {$tabela} WHERE {$where_sql}";

    if ( $args ) {
        $sql_total = $wpdb->prepare( $sql_total, $args );
    }

    $total = (int) $wpdb->get_var( $sql_total );


    $where_leads = $where;
    $args_leads  = $args;

    if ( ! $filtro_status ) {
        $where_leads[] = 'status = %s';
        $args_leads[]  = 'lead';
    }

    $sql_leads = 'SELECT COUNT(*) FROM ' . $tabela .
        ' WHERE ' . implode( ' AND ', $where_leads );

    if ( $args_leads ) {
        $sql_leads = $wpdb->prepare( $sql_leads, $args_leads );
    }

    if ( 'clique' === $filtro_status ) {
        $total_leads = 0;
    } else {
        $total_leads = (int) $wpdb->get_var( $sql_leads );
    }

    $total_so_cliques = max( 0, $total - $total_leads );

    $conversao = $total > 0
        ? ( $total_leads / $total ) * 100
        : 0;


    // =====================================================
    // PAGINAÇÃO E REGISTROS
    // =====================================================

    $por_pagina = 50;

    $pagina_atual = isset( $_GET['paged'] )
        ? max( 1, absint( $_GET['paged'] ) )
        : 1;

    $offset = ( $pagina_atual - 1 ) * $por_pagina;

    $sql_registros = "SELECT *
        FROM {$tabela}
        WHERE {$where_sql}
        ORDER BY data_clique DESC
        LIMIT %d OFFSET %d";

    $args_registros   = $args;
    $args_registros[] = $por_pagina;
    $args_registros[] = $offset;

    $sql_registros = $wpdb->prepare(
        $sql_registros,
        $args_registros
    );

    $registros = $wpdb->get_results(
        $sql_registros
    );

    $total_paginas = max(
        1,
        (int) ceil( $total / $por_pagina )
    );


    // =====================================================
    // IMOBILIÁRIAS / AUTORES PARA O FILTRO
    // =====================================================

    $autores_ids = $wpdb->get_col(
        "SELECT DISTINCT autor_id
         FROM {$tabela}
         ORDER BY autor_id ASC"
    );


    // =====================================================
    // IPS BLOQUEADOS
    // =====================================================

    $ips_bloqueados = imu_whatsapp_get_ips_bloqueados();


    // =====================================================
    // URL ATUAL PARA RETORNAR APÓS AÇÃO
    // =====================================================

    $return_url = admin_url(
        'edit.php?post_type=imoveis&page=imu-whatsapp-cliques'
    );

    $filtros_retorno = [];

    if ( $filtro_autor_ativo ) {
        $filtros_retorno['autor_id'] = $filtro_autor_raw;
    }

    if ( $filtro_status ) {
        $filtros_retorno['status'] = $filtro_status;
    }

    if ( $data_inicio ) {
        $filtros_retorno['data_inicio'] = $data_inicio;
    }

    if ( $data_fim ) {
        $filtros_retorno['data_fim'] = $data_fim;
    }

    if ( $pagina_atual > 1 ) {
        $filtros_retorno['paged'] = $pagina_atual;
    }

    if ( $filtros_retorno ) {
        $return_url = add_query_arg(
            $filtros_retorno,
            $return_url
        );
    }

    ?>

    <div class="wrap">

        <h1>Cliques no WhatsApp</h1>

        <?php
        $notice = isset( $_GET['imu_notice'] )
            ? sanitize_key( wp_unslash( $_GET['imu_notice'] ) )
            : '';

        if ( 'excluido' === $notice ) :
        ?>
            <div class="notice notice-success is-dismissible"><p>Registro excluído.</p></div>
        <?php elseif ( 'ip_bloqueado' === $notice ) : ?>
            <div class="notice notice-success is-dismissible"><p>IP bloqueado no módulo de WhatsApp.</p></div>
        <?php elseif ( 'ip_desbloqueado' === $notice ) : ?>
            <div class="notice notice-success is-dismissible"><p>IP desbloqueado.</p></div>
        <?php endif; ?>


        <!-- =================================================
        FILTROS
        ================================================== -->

        <form method="get" style="background:#fff;border:1px solid #dcdcde;padding:14px;margin:20px 0;display:flex;gap:12px;align-items:end;flex-wrap:wrap;">

            <input type="hidden" name="post_type" value="imoveis">
            <input type="hidden" name="page" value="imu-whatsapp-cliques">

            <div>
                <label for="imu-autor"><strong>Imobiliária / Autor</strong></label><br>
                <select id="imu-autor" name="autor_id" style="min-width:210px;">
                    <option value="">Todas</option>

                    <?php foreach ( $autores_ids as $autor_id_option ) : ?>
                        <?php
                        $autor_id_option = (int) $autor_id_option;
                        $usuario         = get_userdata( $autor_id_option );

                        $label_autor = $usuario
                            ? $usuario->display_name
                            : 'Autor #' . $autor_id_option;
                        ?>

                        <option
                            value="<?php echo esc_attr( $autor_id_option ); ?>"
                            <?php selected( $filtro_autor_ativo && $filtro_autor === $autor_id_option ); ?>
                        >
                            <?php echo esc_html( $label_autor . ' (ID ' . $autor_id_option . ')' ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="imu-status"><strong>Status</strong></label><br>
                <select id="imu-status" name="status">
                    <option value="" <?php selected( $filtro_status, '' ); ?>>Todos</option>
                    <option value="clique" <?php selected( $filtro_status, 'clique' ); ?>>Clique</option>
                    <option value="lead" <?php selected( $filtro_status, 'lead' ); ?>>Lead</option>
                </select>
            </div>

            <div>
                <label for="imu-data-inicio"><strong>De</strong></label><br>
                <input
                    id="imu-data-inicio"
                    type="date"
                    name="data_inicio"
                    value="<?php echo esc_attr( $data_inicio ); ?>"
                >
            </div>

            <div>
                <label for="imu-data-fim"><strong>Até</strong></label><br>
                <input
                    id="imu-data-fim"
                    type="date"
                    name="data_fim"
                    value="<?php echo esc_attr( $data_fim ); ?>"
                >
            </div>

            <div>
                <button type="submit" class="button button-primary">Filtrar</button>

                <a
                    class="button"
                    href="<?php echo esc_url( admin_url( 'edit.php?post_type=imoveis&page=imu-whatsapp-cliques' ) ); ?>"
                >
                    Limpar
                </a>
            </div>

        </form>


        <!-- =================================================
        INDICADORES
        ================================================== -->

        <p style="margin-bottom:8px;color:#646970;">
            Os indicadores abaixo respeitam a imobiliária e o período selecionados.
        </p>

        <div style="display:flex;flex-wrap:wrap;gap:12px;margin:0 0 20px;">

            <div style="background:#fff;border:1px solid #dcdcde;padding:14px 18px;min-width:160px;">
                <div style="color:#646970;">Registros no período</div>
                <strong style="font-size:24px;"><?php echo number_format_i18n( $total ); ?></strong>
            </div>

            <div style="background:#fff;border:1px solid #dcdcde;padding:14px 18px;min-width:160px;">
                <div style="color:#646970;">Leads preenchidos</div>
                <strong style="font-size:24px;"><?php echo number_format_i18n( $total_leads ); ?></strong>
            </div>

            <div style="background:#fff;border:1px solid #dcdcde;padding:14px 18px;min-width:160px;">
                <div style="color:#646970;">Não preencheram</div>
                <strong style="font-size:24px;"><?php echo number_format_i18n( $total_so_cliques ); ?></strong>
            </div>

            <div style="background:#fff;border:1px solid #dcdcde;padding:14px 18px;min-width:160px;">
                <div style="color:#646970;">Conversão</div>
                <strong style="font-size:24px;"><?php echo esc_html( number_format_i18n( $conversao, 1 ) ); ?>%</strong>
            </div>

        </div>


        <!-- =================================================
        TABELA
        ================================================== -->

        <div style="overflow-x:auto;">

            <table class="wp-list-table widefat striped" style="min-width:1650px;">

                <thead>
                    <tr>
                        <th style="width:65px;">ID</th>
                        <th style="width:220px;">Imóvel</th>
                        <th style="width:155px;">Data</th>
                        <th style="width:150px;">Nome</th>
                        <th style="width:190px;">E-mail</th>
                        <th style="width:150px;">WhatsApp</th>
                        <th style="width:120px;">Status</th>
                        <th style="width:150px;">IP</th>
                        <th style="width:170px;">Autor</th>
                        <th style="width:170px;">Categoria</th>
                        <th style="width:210px;">Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ( ! empty( $registros ) ) : ?>

                    <?php foreach ( $registros as $registro ) : ?>

                        <?php
                        $titulo_imovel = get_the_title( $registro->imovel_id );

                        if ( ! $titulo_imovel ) {
                            $titulo_imovel = 'Imóvel #' . $registro->imovel_id;
                        }

                        $link_imovel = get_permalink( $registro->imovel_id );

                        $autor = get_userdata( $registro->autor_id );

                        $nome_autor = $autor
                            ? $autor->display_name
                            : 'Autor #' . $registro->autor_id;

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

                        $ip_bloqueado = imu_whatsapp_ip_bloqueado(
                            $registro->ip
                        );
                        ?>

                        <tr>

                            <td><?php echo esc_html( $registro->id ); ?></td>

                            <td>
                                <?php if ( $link_imovel ) : ?>
                                    <a href="<?php echo esc_url( $link_imovel ); ?>" target="_blank" rel="noopener noreferrer">
                                        <strong><?php echo esc_html( $titulo_imovel ); ?></strong>
                                    </a>
                                <?php else : ?>
                                    <?php echo esc_html( $titulo_imovel ); ?>
                                <?php endif; ?>
                                <br>
                                <small>ID: <?php echo esc_html( $registro->imovel_id ); ?></small>
                            </td>

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

                            <td>
                                <?php if ( $nome_visitante ) : ?>
                                    <strong><?php echo esc_html( $nome_visitante ); ?></strong>
                                <?php else : ?>
                                    <span style="color:#999;">—</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ( $email_visitante ) : ?>
                                    <a href="mailto:<?php echo esc_attr( $email_visitante ); ?>">
                                        <?php echo esc_html( $email_visitante ); ?>
                                    </a>
                                <?php else : ?>
                                    <span style="color:#999;">—</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ( $whatsapp_visitante ) : ?>
                                    <?php echo esc_html( $whatsapp_visitante ); ?>
                                <?php else : ?>
                                    <span style="color:#999;">—</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ( 'lead' === $status ) : ?>
                                    <span style="display:inline-block;background:#d7f5df;color:#176b2c;padding:4px 8px;border-radius:4px;font-weight:600;">Lead</span>
                                <?php else : ?>
                                    <span style="display:inline-block;background:#fff3cd;color:#7a5d00;padding:4px 8px;border-radius:4px;font-weight:600;">Clique</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php echo esc_html( $registro->ip ); ?>

                                <?php if ( $ip_bloqueado ) : ?>
                                    <br><small style="color:#b32d2e;font-weight:600;">Bloqueado</small>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php echo esc_html( $nome_autor ); ?>
                                <br>
                                <small>ID: <?php echo esc_html( $registro->autor_id ); ?></small>
                            </td>

                            <td><?php echo esc_html( $registro->categoria ); ?></td>

                            <td>

                                <div style="display:flex;gap:6px;flex-wrap:wrap;">

                                    <form method="post" onsubmit="return confirm('Excluir este registro?');">
                                        <?php wp_nonce_field( 'imu_whatsapp_admin_action', 'imu_whatsapp_admin_nonce' ); ?>
                                        <input type="hidden" name="imu_whatsapp_admin_action" value="excluir">
                                        <input type="hidden" name="registro_id" value="<?php echo esc_attr( $registro->id ); ?>">
                                        <input type="hidden" name="return_url" value="<?php echo esc_attr( $return_url ); ?>">
                                        <button type="submit" class="button button-small">Excluir</button>
                                    </form>

                                    <?php if ( $registro->ip ) : ?>

                                        <?php if ( ! $ip_bloqueado ) : ?>

                                            <form method="post" onsubmit="return confirm('Bloquear o IP <?php echo esc_js( $registro->ip ); ?> para novos cliques e leads?');">
                                                <?php wp_nonce_field( 'imu_whatsapp_admin_action', 'imu_whatsapp_admin_nonce' ); ?>
                                                <input type="hidden" name="imu_whatsapp_admin_action" value="bloquear_ip">
                                                <input type="hidden" name="ip" value="<?php echo esc_attr( $registro->ip ); ?>">
                                                <input type="hidden" name="return_url" value="<?php echo esc_attr( $return_url ); ?>">
                                                <button type="submit" class="button button-small">Bloquear IP</button>
                                            </form>

                                        <?php else : ?>

                                            <form method="post">
                                                <?php wp_nonce_field( 'imu_whatsapp_admin_action', 'imu_whatsapp_admin_nonce' ); ?>
                                                <input type="hidden" name="imu_whatsapp_admin_action" value="desbloquear_ip">
                                                <input type="hidden" name="ip" value="<?php echo esc_attr( $registro->ip ); ?>">
                                                <input type="hidden" name="return_url" value="<?php echo esc_attr( $return_url ); ?>">
                                                <button type="submit" class="button button-small">Desbloquear IP</button>
                                            </form>

                                        <?php endif; ?>

                                    <?php endif; ?>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else : ?>

                    <tr>
                        <td colspan="11">Nenhum registro encontrado para os filtros selecionados.</td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>


        <!-- =================================================
        PAGINAÇÃO
        ================================================== -->

        <?php if ( $total_paginas > 1 ) : ?>

            <div style="margin-top:20px;">

                <?php
                echo paginate_links(
                    [
                        'base' => add_query_arg(
                            'paged',
                            '%#%',
                            remove_query_arg( 'paged' )
                        ),
                        'format'    => '',
                        'current'   => $pagina_atual,
                        'total'     => $total_paginas,
                        'prev_text' => '« Anterior',
                        'next_text' => 'Próxima »',
                    ]
                );
                ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
        IPS BLOQUEADOS
        ================================================== -->

        <details style="margin-top:24px;background:#fff;border:1px solid #dcdcde;padding:14px;">

            <summary style="cursor:pointer;font-weight:600;">
                IPs bloqueados (<?php echo number_format_i18n( count( $ips_bloqueados ) ); ?>)
            </summary>

            <?php if ( $ips_bloqueados ) : ?>

                <table class="widefat striped" style="margin-top:14px;max-width:700px;">
                    <thead>
                        <tr>
                            <th>IP</th>
                            <th>Bloqueado em</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ( $ips_bloqueados as $ip => $info ) : ?>
                            <tr>
                                <td><?php echo esc_html( $ip ); ?></td>
                                <td>
                                    <?php
                                    echo ! empty( $info['data'] )
                                        ? esc_html( mysql2date( 'd/m/Y H:i:s', $info['data'] ) )
                                        : '—';
                                    ?>
                                </td>
                                <td>
                                    <form method="post">
                                        <?php wp_nonce_field( 'imu_whatsapp_admin_action', 'imu_whatsapp_admin_nonce' ); ?>
                                        <input type="hidden" name="imu_whatsapp_admin_action" value="desbloquear_ip">
                                        <input type="hidden" name="ip" value="<?php echo esc_attr( $ip ); ?>">
                                        <input type="hidden" name="return_url" value="<?php echo esc_attr( $return_url ); ?>">
                                        <button type="submit" class="button button-small">Desbloquear</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>

            <?php else : ?>

                <p>Nenhum IP bloqueado.</p>

            <?php endif; ?>

        </details>

    </div>

    <?php
}
