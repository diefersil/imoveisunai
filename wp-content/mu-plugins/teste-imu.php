<?php

add_action( 'wp_head', function() {
	echo '
	<script>
	console.log(
		"%c MU-PLUGIN IMU CARREGADO ",
		"background:red;color:white;font-size:20px;padding:10px;"
	);
	</script>
	';
});