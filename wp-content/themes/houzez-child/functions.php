<?php

/*Esconder campos desnecessários no painel do usuário*/
add_action('wp_footer','hide');
function hide(){
    if(is_page('my-profile')){?>
        <script type="text/javascript">
            document.querySelector("input[name='telegram']").parentNode.parentNode.style.display='none'
            document.querySelector("input[name='line_id']").parentNode.parentNode.style.display='none'
            document.querySelector("input[name='fax_number']").parentNode.parentNode.style.display='none'
            document.querySelector("input[name='userlangs']").parentNode.parentNode.style.display='none' 
            document.querySelector("input[name='specialties']").parentNode.parentNode.style.display='none'
            document.querySelector("input[name='service_areas']").parentNode.parentNode.style.display='none'
            //document.querySelector("input[name='usermobile']").parentNode.parentNode.style.display='none'
            //document.querySelector("input[name='title']").parentNode.parentNode.style.display='none'
            document.querySelector('[name=display_name]').parentNode.parentNode.style.display='none'

            document.querySelector("input[name='facebook']").parentNode.parentNode.style.display='none'
            document.querySelector("input[name='linkedin']").parentNode.parentNode.style.display='none'
            document.querySelector("input[name='googleplus']").parentNode.parentNode.style.display='none'
            document.querySelector("input[name='tiktok']").parentNode.parentNode.style.display='none' 
            document.querySelector("input[name='twitter']").parentNode.parentNode.style.display='none'
            document.querySelector("input[name='pinterest']").parentNode.parentNode.style.display='none'
            document.querySelector("input[name='zillow']").parentNode.parentNode.style.display='none'
            document.querySelector("input[name='userskype']").parentNode.parentNode.style.display='none' 
            document.querySelector("input[name='youtube']").parentNode.parentNode.style.display='none'
            document.querySelector("input[name='vimeo']").parentNode.parentNode.style.display='none'
            document.querySelector("input[name='zillow']").parentNode.parentNode.style.display='none'
            document.querySelector("input[name='realtor_com']").parentNode.parentNode.style.display='none'
            
            document.querySelector('[name=houzez-agency-package-security]').parentNode.parentNode.parentNode.parentNode.parentNode.style.display='none'
            
            document.querySelectorAll('h2')[4].parentNode.parentNode.parentNode.style.display='none'
        </script>
    <?php }
}


add_action('wp_head', 'ga');
function ga(){?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-YSCEVGGKC3"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-YSCEVGGKC3');
    </script>
<?php }

add_action('wp_head', 'gadc');
function gadc(){?>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-0370842058394618"
    crossorigin="anonymous"></script>
<?php }

/*add_action('wp_head', 'hiltop_ads');
function hiltop_ads(){?>
    <meta name="352f73b99be1013e8252dcd9c84b82303813a2af" content="352f73b99be1013e8252dcd9c84b82303813a2af" />
<?php }*/

//Disable emojis in WordPress
add_action( 'init', 'smartwp_disable_emojis' );

function smartwp_disable_emojis() {
  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
  remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
  remove_action( 'wp_print_styles', 'print_emoji_styles' );
  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
  remove_action( 'admin_print_styles', 'print_emoji_styles' );
  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
  remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
  add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
}

function disable_emojis_tinymce( $plugins ) {
  if ( is_array( $plugins ) ) {
    return array_diff( $plugins, array( 'wpemoji' ) );
  } else {
    return array();
  }
}
