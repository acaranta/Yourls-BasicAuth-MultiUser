<?php
session_start();
define('YOURLS_AJAX', true);
include "../../../includes/load-yourls.php";

// This file will output a JSON string
header('Content-type: application/json');

if( !isset( $_REQUEST['action'] ) || !isLogged())
die("NO USER OR NO ACTION");

// Pick action
$action = $_REQUEST['action'];
switch( $action ) {

	case 'add':
		yourls_verify_nonce( 'add_url', $_REQUEST['nonce'], false, 'omg error' );
		$return = yourls_add_new_link( $_REQUEST['url'], $_REQUEST['keyword'] );
		echo json_encode($return);
		break;

	case 'edit_display':
		yourls_verify_nonce( 'edit-link_'.$_REQUEST['id'], $_REQUEST['nonce'], false, 'omg error' );
		$row = yourls_table_edit_row ( $_REQUEST['keyword'] );
		echo json_encode( array('html' => $row) );
		break;

	case 'edit_save':
		yourls_verify_nonce( 'edit-save_'.$_REQUEST['id'], $_REQUEST['nonce'], false, 'omg error' );
		$user = $_SESSION["user"];
		if(verifyUrlOwner(yourls_sanitize_keyword($_REQUEST['keyword']), $user["id"])) {
			$return = yourls_edit_link( $_REQUEST['url'], $_REQUEST['keyword'], $_REQUEST['newkeyword'], $_REQUEST['title'] );
			echo json_encode($return);
		} else {
			// TODO: SHOW ERROR!
			$keyword = $_REQUEST['keyword'];
			die("THE $keyword url does not seems to be from " . $user["id"]);
		}
		break;

	case 'delete':
		yourls_verify_nonce( 'delete-link_'.$_REQUEST['id'], $_REQUEST['nonce'], false, 'omg error' );
		$user = $_SESSION["user"];
		if(verifyUrlOwner(yourls_sanitize_keyword($_REQUEST['keyword']), $user["id"])) {
			$query = yourls_delete_link_by_keyword( $_REQUEST['keyword'] );
			echo json_encode(array('success'=>$query));
		} else {
			// TODO: SHOW ERROR!
			die();
		}
		break;

	case 'logout':
		// unused for the moment
		yourls_logout();
		break;

	default:
		yourls_do_action( 'yourls_ajax_'.$action );

}

die();
