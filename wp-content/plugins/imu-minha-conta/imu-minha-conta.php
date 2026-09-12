<?php
/**
 * Plugin Name: IMU — Minha Conta
 * Description: Painel frontal de anunciantes para o post type imoveis. Login Google via Nextend Social Login.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Imóveis Unaí
 */
if (!defined('ABSPATH')) exit;
final class IMU_Minha_Conta {
    private static $error = '';
    private static $old = array();
    private static function options() {
        return wp_parse_args(get_option('imu_mc_options', array()), array('page'=>0,'gallery'=>'','date'=>'','tags'=>'tags-imoveis','days'=>30));
    }
    public static function boot() {
        add_shortcode('imu_minha_conta', array(__CLASS__, 'render'));
        add_action('template_redirect', array(__CLASS__, 'request'));
        add_action('admin_menu', array(__CLASS__, 'menu'));
        add_action('admin_post_imu_mc_settings', array(__CLASS__, 'settings_save'));
        add_action('imu_mc_expire', array(__CLASS__, 'expire'));
    }
    public static function activate() {
        add_role('imu_anunciante','Anunciante IMU',array('read'=>true));
        if (!wp_next_scheduled('imu_mc_expire')) wp_schedule_event(time()+300,'hourly','imu_mc_expire');
    }
    public static function deactivate() { wp_clear_scheduled_hook('imu_mc_expire'); }
    private static function url($args=array()) {
        $o=self::options();
        return add_query_arg($args, $o['page'] ? get_permalink($o['page']) : home_url('/minha-conta/'));
    }
    private static function taxes() {
        $o=self::options();
        return array('categoria'=>'Categoria','negociacao'=>'Negociação','estado'=>'Estado','cidade'=>'Cidade',$o['tags']=>'Tags Imóveis');
    }
    private static function allowed($id) {
        $p=get_post($id);
        return $p && $p->post_type==='imoveis' && (int)$p->post_author===get_current_user_id() && in_array($p->post_status,array('publish','pending','draft'),true);
    }
    private static function fail($message) { throw new RuntimeException($message); }
    private static function value($key,$default='') {
        return isset(self::$old[$key]) && is_scalar(self::$old[$key]) ? (string)self::$old[$key] : $default;
    }
    public static function menu() { add_options_page('IMU Minha Conta','IMU Minha Conta','manage_options','imu-minha-conta',array(__CLASS__,'settings')); }
    public static function settings() {
        if (!current_user_can('manage_options')) return;
        $o=self::options(); ?>
        <div class="wrap"><h1>IMU — Minha Conta</h1>
        <p>Crie uma página Minha conta com o shortcode <code>[imu_minha_conta]</code> e selecione-a abaixo.</p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="imu_mc_settings"><?php wp_nonce_field('imu_mc_settings'); ?>
        <table class="form-table"><tr><th>Página do painel</th><td><?php wp_dropdown_pages(array('name'=>'page','selected'=>$o['page'],'show_option_none'=>'Selecione')); ?></td></tr>
        <?php self::setting_select('gallery','Formato do campo galeria',array(''=>'Selecione conforme o JetEngine','ids'=>'IDs separados por vírgula','urls'=>'URLs separadas por vírgula','objects'=>'Array de ID e URL'),$o['gallery']);
        self::setting_select('date','Formato de data_expiracao',array(''=>'Selecione conforme o JetEngine','date'=>'Data: YYYY-MM-DD','timestamp'=>'Timestamp Unix'),$o['date']); ?>
        <tr><th>Slug da taxonomia Tags Imóveis</th><td><input name="tags" value="<?php echo esc_attr($o['tags']); ?>"><p>Confira o identificador registrado: tags-imoveis ou tags-imoveus.</p></td></tr>
        <tr><th>Validade de novos anúncios (dias)</th><td><input type="number" min="1" max="3650" name="days" value="<?php echo esc_attr($o['days']); ?>"></td></tr></table>
        <?php submit_button(); ?></form>
        <h2>Entrar com Google</h2><p>Instale e configure Nextend Social Login, ative o provedor Google e permita o cadastro. Defina Anunciante IMU como função padrão em Configurações → Geral. O painel usa o shortcode do Nextend, sem armazenar credenciais do Google.</p>
        <p>Novos anúncios e edições ficam pendentes de revisão. A expiração é automática para anúncios criados neste painel. Imóveis antigos não recebem nova expiração automaticamente.</p>
        <p>Exclua a página do painel do cache. Confira o formato da galeria e a opção de salvar data como timestamp no JetEngine antes de habilitar o cadastro.</p></div>
        <?php
    }
    private static function setting_select($name,$label,$choices,$value) {
        echo '<tr><th>'.esc_html($label).'</th><td><select name="'.esc_attr($name).'">';
        foreach($choices as $k=>$v) echo '<option value="'.esc_attr($k).'" '.selected($value,$k,false).'>'.esc_html($v).'</option>';
        echo '</select></td></tr>';
    }
    public static function settings_save() {
        if (!current_user_can('manage_options')) wp_die('Acesso negado.',403);
        check_admin_referer('imu_mc_settings');
        $gallery=sanitize_key(wp_unslash($_POST['gallery'] ?? ''));
        $date=sanitize_key(wp_unslash($_POST['date'] ?? ''));
        $page=absint($_POST['page'] ?? 0);
        if ($page && get_post_type($page)!=='page') wp_die('Página inválida.');
        update_option('imu_mc_options',array('page'=>$page,'gallery'=>in_array($gallery,array('ids','urls','objects'),true)?$gallery:'','date'=>in_array($date,array('date','timestamp'),true)?$date:'','tags'=>sanitize_key(wp_unslash($_POST['tags'] ?? 'tags-imoveis')) ?: 'tags-imoveis','days'=>max(1,min(3650,absint($_POST['days'] ?? 30)))));
        wp_safe_redirect(admin_url('options-general.php?page=imu-minha-conta&saved=1')); exit;
    }
    public static function request() {
        $o=self::options();
        $p=get_queried_object();
        $panel=($o['page'] && is_page($o['page'])) || ($p instanceof WP_Post && has_shortcode($p->post_content,'imu_minha_conta'));
        if ($panel) {
            if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE',true);
            nocache_headers();
        }
        if ($_SERVER['REQUEST_METHOD']!=='POST' || !isset($_POST['imu_mc_action'])) return;
        nocache_headers();
        if (!$panel) wp_die('Use a página Minha conta.',400);
        if (!is_user_logged_in() || !current_user_can('read')) wp_die('Entre na sua conta.',403);
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'] ?? '')),'imu_mc')) wp_die('Sessão expirada. Atualize a página e tente novamente.',403);
        self::$old=wp_unslash($_POST);
        try {
            $action=sanitize_key(self::$old['imu_mc_action']);
            if ($action==='profile') {
                $name=sanitize_text_field(self::value('display_name'));
                if (!$name) self::fail('Informe seu nome.');
                $result=wp_update_user(array('ID'=>get_current_user_id(),'display_name'=>$name));
                if (is_wp_error($result)) self::fail($result->get_error_message());
                foreach(array('user_whatsapp','user_fone','user_endereco') as $key) update_user_meta(get_current_user_id(),$key,sanitize_text_field(self::value($key)));
                wp_safe_redirect(self::url(array('aba'=>'perfil','salvo'=>1))); exit;
            }
            if ($action==='property') self::save_property();
            self::fail('Ação inválida.');
        } catch (RuntimeException $e) { self::$error=$e->getMessage(); }
    }
    private static function ready() {
        $o=self::options();
        if (!$o['gallery'] || !$o['date'] || !post_type_exists('imoveis')) return false;
        foreach(self::taxes() as $tax=>$label) if (!taxonomy_exists($tax) || !is_object_in_taxonomy('imoveis',$tax)) return false;
        return true;
    }
    private static function number($value,$label) {
        $value=trim($value);
        if ($value==='') return '';
        if (strpos($value,',')!==false) $value=str_replace(',','.',str_replace('.','',$value));
        if (!preg_match('/^\d+(\.\d{1,2})?$/',$value)) self::fail('Informe '.$label.' com números, por exemplo 1200,00.');
        return $value;
    }
    private static function save_property() {
        if (!self::ready()) self::fail('O cadastro está aguardando configuração do administrador.');
        $id=absint(self::value('imovel_id',0));
        if ($id && !self::allowed($id)) self::fail('Você não pode editar este imóvel.');
        $title=sanitize_text_field(self::value('titulo'));
        $content=wp_kses_post(self::value('descricao'));
        if (!$title || !trim(wp_strip_all_tags($content))) self::fail('Preencha título e descrição.');
        $price=self::number(self::value('preco'),'o preço');
        $area=self::number(self::value('area'),'a área');
        $taxes=array();
        foreach(self::taxes() as $tax=>$label) {
            $raw=self::$old['tax'][$tax] ?? array();
            if (!is_array($raw)) self::fail('Seleção de '.$label.' inválida.');
            $ids=array_values(array_unique(array_filter(array_map('absint',$raw))));
            if ($tax!==self::options()['tags'] && count($ids)!==1) self::fail('Selecione '.$label.'.');
            foreach($ids as $term) if (!term_exists($term,$tax)) self::fail('Termo inválido em '.$label.'.');
            $taxes[$tax]=$ids;
        }
        $files=self::files();
        $keep=$id?self::gallery_items(get_post_meta($id,'galeria',true)):array();
        $remove=self::$old['remove_gallery'] ?? array();
        if (!is_array($remove)) self::fail('Seleção de fotos inválida.');
        foreach(array_map('absint',$remove) as $index) unset($keep[$index]);
        $keep=array_values($keep);
        if (self::options()['gallery']==='ids') {
            foreach($keep as $item) if (!$item['id']) self::fail('Uma foto existente não tem ID na biblioteca de mídia. Confira o formato da galeria com o administrador antes de editar.');
        }
        $new_count=count(array_filter($files,function($f){return $f['field']==='photos';}));
        if ($new_count && count($keep)+$new_count>30) self::fail('Use até 30 fotos na galeria.');
        // Validate uploads before changing the post. Roll back new attachments if upload fails.
        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/media.php';
        require_once ABSPATH.'wp-admin/includes/image.php';
        $uploaded=array(); $cover=0;
        try {
            foreach($files as $file) {
                $field=$file['field']; unset($file['field']);
                $_FILES['imu_single_upload']=$file;
                $aid=media_handle_upload('imu_single_upload',0,array(),array('test_form'=>false,'mimes'=>array('jpg|jpeg|jpe'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp')));
                if (is_wp_error($aid)) self::fail('Não foi possível enviar a imagem: '.$aid->get_error_message());
                $uploaded[]=$aid;
                if ($field==='cover') $cover=$aid;
                else $keep[]=array('id'=>$aid,'url'=>wp_get_attachment_url($aid));
            }
        } catch(RuntimeException $e) {
            foreach($uploaded as $aid) wp_delete_attachment($aid,true);
            throw $e;
        }
        $result=wp_insert_post(array('ID'=>$id,'post_type'=>'imoveis','post_author'=>get_current_user_id(),'post_title'=>$title,'post_content'=>$content,'post_status'=>'pending'),true);
        if (is_wp_error($result)) {
            foreach($uploaded as $aid) wp_delete_attachment($aid,true);
            self::fail($result->get_error_message());
        }
        $new=!$id; $id=$result;
        foreach($uploaded as $aid) wp_update_post(array('ID'=>$aid,'post_parent'=>$id));
        foreach($taxes as $tax=>$ids) wp_set_object_terms($id,$ids,$tax);
        update_post_meta($id,'preco',$price);
        update_post_meta($id,'area',$area);
        update_post_meta($id,'endereco',sanitize_text_field(self::value('endereco')));
        if ($new || $files || $remove) update_post_meta($id,'galeria',self::gallery_encode($keep));
        if ($cover) set_post_thumbnail($id,$cover);
        if ($new) {
            $o=self::options();
            $expires=current_datetime()->modify('+'.$o['days'].' days')->setTime(23,59,59);
            update_post_meta($id,'data_expiracao',$o['date']==='timestamp'?$expires->getTimestamp():$expires->format('Y-m-d'));
            update_post_meta($id,'_imu_mc_expires_at',$expires->getTimestamp());
        }
        wp_safe_redirect(self::url(array('salvo'=>1))); exit;
    }
    private static function files() {
        $out=array();
        foreach(array('cover','photos') as $field) {
            if (!isset($_FILES[$field])) continue;
            $f=$_FILES[$field];
            $names=is_array($f['name'])?$f['name']:array($f['name']);
            foreach($names as $i=>$name) {
                $file=array('field'=>$field);
                foreach(array('name','type','tmp_name','error','size') as $key) $file[$key]=is_array($f[$key])?($f[$key][$i] ?? null):$f[$key];
                if ((int)$file['error']===UPLOAD_ERR_NO_FILE) continue;
                if ((int)$file['error']!==UPLOAD_ERR_OK) self::fail('Falha no envio de uma foto. Confira o limite de tamanho do servidor.');
                if ($file['size']>min(8*1024*1024,wp_max_upload_size())) self::fail('Cada foto deve respeitar o limite de 8 MB e do servidor.');
                $mime=wp_get_image_mime($file['tmp_name']);
                if (!in_array($mime,array('image/jpeg','image/png','image/webp'),true)) self::fail('Envie somente imagens JPG, PNG ou WebP.');
                $out[]=$file;
            }
        }
        if (count($out)>31) self::fail('Envie até 30 fotos e uma capa por vez.');
        return $out;
    }
    private static function gallery_items($raw) {
        if (!$raw) return array();
        if (is_string($raw)) {
            $json=json_decode($raw,true);
            $raw=is_array($json)?$json:explode(',',$raw);
        }
        if (!is_array($raw)) return array();
        if (isset($raw['id']) || isset($raw['url'])) $raw=array($raw);
        $out=array();
        foreach($raw as $item) {
            $id=0; $url='';
            if (is_array($item)) { $id=absint($item['id'] ?? 0); $url=$item['url'] ?? ''; }
            elseif (is_numeric($item)) $id=absint($item);
            elseif (is_string($item)) $url=trim($item);
            if (!$url && $id) $url=wp_get_attachment_url($id);
            if (!$id && $url) $id=attachment_url_to_postid($url);
            if ($id || $url) $out[]=array('id'=>$id,'url'=>$url);
        }
        return $out;
    }
    private static function gallery_encode($items) {
        $mode=self::options()['gallery'];
        if ($mode==='objects') return array_values($items);
        if ($mode==='urls') return implode(',',array_column($items,'url'));
        return implode(',',array_column($items,'id'));
    }
    public static function expire() {
        $q=new WP_Query(array('post_type'=>'imoveis','post_status'=>array('publish','pending'),'posts_per_page'=>100,'fields'=>'ids','meta_query'=>array(array('key'=>'_imu_mc_expires_at','value'=>time(),'compare'=>'<=','type'=>'NUMERIC'))));
        foreach($q->posts as $id) {
            // Respect administrator edits to the visible JetEngine expiration field.
            $raw=get_post_meta($id,'data_expiracao',true);
            if (!$raw) continue;
            if (is_numeric($raw)) $expiry=(int)$raw;
            else { try { $expiry=(new DateTimeImmutable($raw,wp_timezone()))->setTime(23,59,59)->getTimestamp(); } catch(Exception $e) { continue; } }
            if ($expiry>time()) update_post_meta($id,'_imu_mc_expires_at',$expiry);
            else wp_update_post(array('ID'=>$id,'post_status'=>'draft'));
        }
    }
    private static function input($name,$label,$value='',$type='text',$required=false) {
        echo '<label>'.esc_html($label).'<input type="'.esc_attr($type).'" name="'.esc_attr($name).'" value="'.esc_attr(self::value($name,$value)).'" '.($required?'required':'').'></label>';
    }
    public static function render() {
        wp_enqueue_style('imu-mc',plugins_url('painel.css',__FILE__),array(),'1.0.0');
        ob_start();
        echo '<section class="imu-mc"><header><span>IMÓVEIS UNAÍ</span><h1>Minha conta</h1></header>';
        if (!is_user_logged_in()) {
            echo '<div class="imu-box"><h2>Seu próximo anúncio começa aqui</h2><p>Entre com sua conta Google para cadastrar imóveis e acompanhar seus anúncios.</p>';
            if (shortcode_exists('nextend_social_login')) echo do_shortcode('[nextend_social_login provider="google" redirect="'.esc_url(self::url()).'"]');
            else echo '<p>O acesso com Google está em configuração. Tente novamente em breve.</p>';
            echo '</div></section>'; return ob_get_clean();
        }
        if (!current_user_can('read')) { echo '<p>Sua conta não tem acesso ao painel.</p></section>'; return ob_get_clean(); }
        $tab=sanitize_key($_GET['aba'] ?? 'imoveis');
        echo '<nav>';
        foreach(array('imoveis'=>'Meus imóveis','novo'=>'Cadastrar imóvel','perfil'=>'Meu perfil') as $key=>$label) echo '<a '.($tab===$key?'class="active"':'').' href="'.esc_url(self::url(array('aba'=>$key))).'">'.esc_html($label).'</a>';
        echo '<a href="'.esc_url(wp_logout_url(self::url())).'">Sair</a></nav>';
        if(self::$error) echo '<div role="alert" class="imu-error">'.esc_html(self::$error).' Se enviou fotos, selecione os arquivos novamente.</div>';
        elseif(isset($_GET['salvo'])) echo '<div role="status" class="imu-success">Dados salvos.'.($tab==='perfil'?'':' O imóvel foi enviado para revisão.').'</div>';
        if ($tab==='perfil') self::profile();
        elseif(in_array($tab,array('novo','editar'),true)) self::property_form($tab);
        else self::listing();
        echo '</section>'; return ob_get_clean();
    }
    private static function profile() {
        $u=wp_get_current_user();
        echo '<form method="post" class="imu-box"><h2>Meu perfil</h2>';
        wp_nonce_field('imu_mc'); echo '<input type="hidden" name="imu_mc_action" value="profile">';
        self::input('display_name','Nome',$u->display_name,'text',true);
        echo '<label>E-mail da conta<input type="email" value="'.esc_attr($u->user_email).'" readonly></label>';
        foreach(array('user_whatsapp'=>'WhatsApp','user_fone'=>'Telefone','user_endereco'=>'Endereço') as $key=>$label) self::input($key,$label,get_user_meta($u->ID,$key,true),$key==='user_endereco'?'text':'tel');
        echo '<button>Salvar perfil</button></form>';
    }
    private static function property_form($tab) {
        if (!self::ready()) { echo '<div class="imu-box">O cadastro está sendo configurado. Em breve você poderá anunciar por aqui.</div>'; return; }
        $id=$tab==='editar'?absint($_GET['imovel'] ?? 0):0;
        if ($tab==='editar' && !self::allowed($id)) { echo '<p>Imóvel indisponível para edição.</p>'; return; }
        $p=$id?get_post($id):null;
        echo '<form method="post" enctype="multipart/form-data" class="imu-box"><h2>'.($id?'Editar imóvel':'Cadastrar imóvel').'</h2><p>O anúncio será revisado antes de aparecer no site. Ao editar um anúncio publicado, ele ficará fora do site até nova aprovação.</p>';
        wp_nonce_field('imu_mc');
        echo '<input type="hidden" name="imu_mc_action" value="property"><input type="hidden" name="imovel_id" value="'.esc_attr($id).'">';
        self::input('titulo','Título',$p?$p->post_title:'','text',true);
        echo '<label>Descrição<textarea name="descricao" rows="7" required>'.esc_textarea(self::value('descricao',$p?$p->post_content:'')).'</textarea></label><div class="imu-grid">';
        foreach(array('preco'=>'Preço (R$)','area'=>'Área (m²)') as $key=>$label) self::input($key,$label,$id?get_post_meta($id,$key,true):'');
        echo '</div>';
        self::input('endereco','Endereço do imóvel',$id?get_post_meta($id,'endereco',true):'');
        echo '<div class="imu-grid">';
        foreach(self::taxes() as $tax=>$label) {
            $multi=$tax===self::options()['tags'];
            $selected=$id?wp_get_object_terms($id,$tax,array('fields'=>'ids')):array();
            if(is_wp_error($selected)) $selected=array();
            if(isset(self::$old['tax'][$tax]) && is_array(self::$old['tax'][$tax])) $selected=array_map('absint',self::$old['tax'][$tax]);
            $terms=get_terms(array('taxonomy'=>$tax,'hide_empty'=>false));
            echo '<label>'.esc_html($label).'<select name="tax['.esc_attr($tax).'][]" '.($multi?'multiple size="5"':'required').'><option value="">'.($multi?'Sem tags':'Selecione').'</option>';
            if(!is_wp_error($terms)) foreach($terms as $term) echo '<option value="'.esc_attr($term->term_id).'" '.(in_array((int)$term->term_id,$selected,true)?'selected':'').'>'.esc_html($term->name).'</option>';
            echo '</select></label>';
        }
        echo '</div><h3>Fotos</h3><p>JPG, PNG ou WebP. Até 30 fotos na galeria e 8 MB por arquivo, sujeito ao limite do servidor.</p>';
        if ($id && has_post_thumbnail($id)) echo get_the_post_thumbnail($id,'thumbnail',array('class'=>'imu-cover'));
        echo '<label>Imagem principal<input type="file" name="cover" accept="image/jpeg,image/png,image/webp"></label><label>Adicionar fotos à galeria<input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple></label>';
        if ($id) {
            echo '<div class="imu-photos">';
            foreach(self::gallery_items(get_post_meta($id,'galeria',true)) as $i=>$item) echo '<label><img loading="lazy" src="'.esc_url($item['url']).'" alt="Foto do imóvel"><span><input type="checkbox" name="remove_gallery[]" value="'.esc_attr($i).'"> Remover da galeria</span></label>';
            echo '</div>';
            $expiry=get_post_meta($id,'data_expiracao',true);
            if($expiry) echo '<p>Expiração: '.esc_html(is_numeric($expiry)?wp_date('d/m/Y',(int)$expiry):$expiry).'</p>';
        } else echo '<p>Validade do anúncio: '.esc_html(self::options()['days']).' dias a partir do cadastro.</p>';
        echo '<button>Salvar e enviar para revisão</button></form>';
    }
    private static function listing() {
        $page=max(1,absint($_GET['pagina'] ?? 1));
        $q=new WP_Query(array('post_type'=>'imoveis','post_status'=>array('publish','pending','draft'),'author'=>get_current_user_id(),'posts_per_page'=>12,'paged'=>$page));
        echo '<div class="imu-heading"><h2>Meus imóveis</h2><span>'.esc_html($q->found_posts).' anúncios</span></div><div class="imu-cards">';
        foreach($q->posts as $p) {
            $status=array('publish'=>'Publicado','pending'=>'Em revisão','draft'=>'Rascunho / inativo');
            echo '<article class="imu-card">';
            if(has_post_thumbnail($p)) echo get_the_post_thumbnail($p,'medium');
            echo '<div><small>'.esc_html($status[$p->post_status]).'</small><h3>'.esc_html($p->post_title).'</h3><p>'.esc_html(get_post_meta($p->ID,'preco',true)?'R$ '.number_format_i18n((float)get_post_meta($p->ID,'preco',true),2):'Preço a consultar').'</p><a href="'.esc_url(self::url(array('aba'=>'editar','imovel'=>$p->ID))).'">Editar imóvel</a>';
            if($p->post_status==='publish') echo ' · <a href="'.esc_url(get_permalink($p)).'">Ver anúncio</a>';
            echo '</div></article>';
        }
        echo '</div>';
        if(!$q->posts) echo '<div class="imu-box"><p>Você ainda não tem imóveis nesta lista.</p><a href="'.esc_url(self::url(array('aba'=>'novo'))).'">Cadastrar meu primeiro imóvel</a></div>';
        if($q->max_num_pages>1) echo '<div class="imu-pages">'.wp_kses_post(paginate_links(array('base'=>self::url(array('pagina'=>'%#%')),'format'=>'','current'=>$page,'total'=>$q->max_num_pages))).'</div>';
    }
}
IMU_Minha_Conta::boot();
register_activation_hook(__FILE__,array('IMU_Minha_Conta','activate'));
register_deactivation_hook(__FILE__,array('IMU_Minha_Conta','deactivate'));
