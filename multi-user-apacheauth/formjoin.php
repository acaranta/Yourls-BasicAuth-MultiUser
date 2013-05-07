<?php
if(strpos($_SERVER["REQUEST_URI"],"formjoin.php") === false) {

	if(YOURLS_PRIVATE === false || !defined( 'YOURLS_ABSPATH' )) {
		die(); // NO DIRECT CALLS IF PUBLIC OR if YOURLS_ABSPATH NOT DEFINED!
	}

	echo yourls_notice_box("<b>Sign in</b>");
	mu_html_signupForm($error_msg);
} else {

}
?>