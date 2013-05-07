<?php
session_start();
include("../../../includes/load-yourls.php");
include("muhtmlfunctions.php");

if(YOURLS_PRIVATE === false) {
	die(); // NO DIRECT CALLS IF PUBLIC!
}

$act = $_GET['act'];
if($act == "logout") {
	$_SESSION['user'] = "";
	unset($_SESSION);
	unset($_SESSION["user"]);
	$error_msg = "Signed off.";
}

$username = $_SERVER['PHP_AUTH_USER'] ;
$password = $_SERVER['PHP_AUTH_PW'] ;

// If login AND password
if(!empty($username) && !empty($password)) {
	$table = YOURLS_DB_TABLE_USERS;
	$results = $ydb->get_results("select user_email from `$table` where `user_email` = '$username'");
	if(!$results) {
		// If user does not already exists ... CREATE IT !!!
		$token = createRandonToken();
		$password = md5('whocares');
		$ydb->query("insert into `$table` (user_email, user_password, user_token) values ('$username', '$password', '$token')");
		$results = $ydb->get_results("select user_token from `$table` where `user_email` = '$username'");
		if (!empty($results)) {
			$token = $results[0]->user_token;
		} 
	}
	// If user already exists then LOG IN !!!
	$token = getUserTokenByEmail($username);
	$id = getUserIdByToken($token);
	$_SESSION['user'] = array("id" => $id, "user" => $username, "token" => $token);
	//			yourls_redirect("index.php") ;
}

include("admin.php");
//yourls_html_footer();
