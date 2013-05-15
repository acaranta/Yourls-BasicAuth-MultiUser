<?php
if(strpos($_SERVER["REQUEST_URI"],"form.php") === false) {

	if(YOURLS_PRIVATE === false || !defined( 'YOURLS_ABSPATH' )) {
		die(); // NO DIRECT CALLS IF PUBLIC OR if YOURLS_ABSPATH NOT DEFINED!
	}
	
	echo yourls_notice_box("<b>Log in</b>");
	mu_html_loginForm($error_msg);

} else {

}
?>