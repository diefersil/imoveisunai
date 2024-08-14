<?php
add_action('wp_footer','hide');
function hide(){?>
    <script type="text/javascript">
        document.querySelector("input[name='facebook']").parentNode.parentNode.style.display='none'
        document.querySelector("input[name='linkedin']").parentNode.parentNode.style.display='none'
        document.querySelector("input[name='googleplus']").parentNode.parentNode.style.display='none'
        document.querySelector("input[name='tiktok']").parentNode.parentNode.style.display='none' 
    </script>
<? }
