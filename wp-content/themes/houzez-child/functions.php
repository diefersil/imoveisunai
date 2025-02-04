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
