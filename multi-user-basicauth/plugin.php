<?php
session_start();
include("mufunctions.php");

/*
 Plugin Name: Multi User with Apache Auth
 Plugin URI: https://github.com/acaranta/Yourls-BasicAuth-MultiUser
 Description: Multi User Support based on apache credentials. Please Read the README (on the github repo), then to login, go to http://yourYourlsBaseURL/user/plugins/multi-user-basicauth
 Version: 1.0beta
 Author: Arthur Caranta - arthur@caranta.com
 Author URI: http://twitter.com/arthur_caranta
 */

/* This plugin is heavyly based on the work of matheusbrat@gmail.com (http://matbra.com) */

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

if(YOURLS_PRIVATE === true) {
	// Add Event Handlers
	yourls_add_action( 'api', 'trapApi' );
	yourls_add_action('post_add_new_link', "updateLinkInsert");
	yourls_add_action( 'load_template_infos', "trapLoadTemplateInfos" );
	yourls_add_action( 'pre_edit_link', "updateLinkEdit");
	yourls_add_action( 'delete_link', "updateLinkDelete");
	yourls_add_action( 'activated_multi-user-basicauth/plugin.php', 'tryToInstall' );
	yourls_add_action( 'pre_add_new_link', "preAddLink");
	yourls_add_filter( 'is_valid_user', "muIsValidUser");

}

function muIsValidUser($str) { 
	if(yourls_is_API()) {
		return true;
	}
	return $str;
}

function preAddLink($args) {
	if (YOURLS_MULTIUSER_ANONYMOUS === true) {
		return;
	} else {
		$token = ( isset( $_REQUEST['token'] ) ? yourls_sanitize_string($_REQUEST['token']) : '' );
		$user = getUserIdByToken($token);
		if($user == false) {
			$u = $_SESSION["user"];
			$user = getUserIdByToken($u["token"]);
		}
		if ($user == false) {
			echo yourls_notice_box("<b>You need to be logged to short a link.</b>");
			die();
		} else {
			return;
		}
	}
}
function tryToInstall() {
	global $ydb;
	global $yourls_user_passwords;
	$tableuser = YOURLS_DB_TABLE_USERS;
	$results = $ydb->get_results("show tables like '$tableuser'; ");
	if(empty($results)) {
		$ydb->query("CREATE TABLE `$tableuser` (
		 `user_id` int(11) NOT NULL auto_increment,
		 `user_email` varchar(200) NOT NULL,
		 `user_password` varchar(32) NOT NULL,
		 `user_token` varchar(50) NOT NULL,
		 PRIMARY KEY  (`user_id`),
		 UNIQUE KEY `user_email_UNIQUE` (`user_email`),
		 UNIQUE KEY `user_hash_UNIQUE` (`user_token`)
		 );");
		$create_success = $ydb->query("SHOW TABLES LIKE '$tableuser'");
		if(!$create_success) {
			// Problems on creation.
		} else {
			foreach ($yourls_user_passwords as $username => $pass) {
				$token = createRandonToken();
				$password = md5($pass);
				$ydb->query("insert into `$tableuser` (user_email, user_password, user_token) values ('$username', '$password', '$token')");
			}
		}
	}
	$table = YOURLS_DB_TABLE_URL_TO_USER;
	$results = $ydb->get_results("show tables like '$table'; ");
	if(empty($results)) {
		$ydb->query("CREATE TABLE `$table` (
			`url_keyword` varchar(200) character set latin1 collate latin1_bin NOT NULL,
			`users_user_id` int(11) NOT NULL,
			PRIMARY KEY  (`url_keyword`,`users_user_id`)
			);");
		$create_success = $ydb->query("SHOW TABLES LIKE '$table'");
		if(!$create_success) {
			// Problems on creation.
		}
	}

}

function updateLinkEdit($args) {
	global $ydb;
	if($args[4]) {
		$table = YOURLS_DB_TABLE_URL_TO_USER;
		$keyword = $args[1];
		$newkeyword = $args[2];
		$update_url = $ydb->query("UPDATE `$table` SET `url_keyword` = '$newkeyword' WHERE `url_keyword` = '$keyword';");
	}
	return;
}

function updateLinkDelete($args) {
	global $ydb;
	$keyword = $args[0];
	$table = YOURLS_DB_TABLE_URL_TO_USER;
	$delete = $ydb->query("DELETE FROM `$table` WHERE `url_keyword` = '$keyword';");
}


// Event to verify if user can see stats or no
function trapLoadTemplateInfos($args) {
	$keyword = $args[0];
	// If not protected anybody can see stats from all links
	// If it is admin can see stats of all links
	if(yourls_is_valid_user() === true) {
		return;
	} else {
		include("infos.php");
	}
}

// Event to update Owner when post_add_new_link
function updateLinkInsert($args) {
	global $ydb;
	$url = $args[0];
	$keyword = $args[1];
	$title = $args[2];
	$token = ( isset( $_REQUEST['token'] ) ? yourls_sanitize_string($_REQUEST['token']) : '' );
	$user = getUserIdByToken($token);
	if($user == false) {
		$u = $_SESSION["user"];
		$user = getUserIdByToken($u["token"]);
	}
	$table = YOURLS_DB_TABLE_URL_TO_USER;
	if($user != false && !empty($keyword)) {
		$ydb->query("insert into `$table` (url_keyword, users_user_id) values ('$keyword', '$user')");
	}

}

function trapApi($args) {
	$action = $args[0];

	$admin =  yourls_is_valid_user(); // Uses this name but REFERS to ADMIN!
	if($admin === true || $action == "expand") {
		return;
	}

	if((YOURLS_MULTUSER_PROTECTED === false) && ($action == "stats" || $action == "db-stats" || $action == 'url-stats')) {
		return;
	}

	switch( $action ) {
		case "shorturl":
			if (YOURLS_MULTIUSER_ANONYMOUS === true) {
				return;
			} else {
				$token = ( isset( $_REQUEST['token'] ) ? yourls_sanitize_string($_REQUEST['token']) : '' );
				$user = getUserIdByToken($token);
				if($user == false) {
					$u = $_SESSION["user"];
					$user = getUserIdByToken($u["token"]);
				}
				if ($user == false) {
					$return = array(
				 'simple' => 'You can\'t be anonymous',
				 'message' => 'You can\'t be anonymous',
				 'errorCode' => 403,
					);
				} else {
					return;
				}
			}
			break;
			// Stats for a shorturl
		case 'url-stats':
			$token = ( isset( $_REQUEST['token'] ) ? yourls_sanitize_string($_REQUEST['token']) : '' );
			$user = getUserIdByToken($token);
			if($user == false) {
				$u = $_SESSION["user"];
				$user = getUserIdByToken($u["token"]);
			}
			if ($user == false) {
				$return = array(
				 'simple' => 'Invalid username or password',
				 'message' => 'Invalid username or password',
				 'errorCode' => 403,
				);
			} else {
				if(verifyUrlOwner($keyword, $user)) {
					$shorturl = ( isset( $_REQUEST['shorturl'] ) ? $_REQUEST['shorturl'] : '' );
					$return = yourls_api_url_stats( $shorturl );
				} else {
					$return = array(
								 'simple' => 'Invalid username or password',
								 'message' => 'Invalid username or password',
								 'errorCode' => 403,
					);
				}
			}
			break;

		default:
			$return = array(
			'errorCode' => 400,
			'message'   => 'Unknown or missing or forbidden "action" parameter',
			'simple'    => 'Unknown or missing or forbidden "action" parameter',
			);

	}

	$format = ( isset( $_REQUEST['format'] ) ? $_REQUEST['format'] : 'xml' );

	yourls_api_output( $format, $return );

	die();
}
