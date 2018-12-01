<?php
// show error reporting
ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

// home page path & url
// [NOTE: currently, only '$home_server' is actually used, since this project does not yet fully support paging]
$home_server = "localhost";
$home_path = "/le/uog_cs_prototype/";
$home_url = "http://" . $home_server . $home_path;

// authenticated user cookie
// [NOTE: the cookie's domain path will be built dynamically]
$auth_user_id__cookie_name = "MY_UOG_CS_AUTH_USER_ID";
$auth_user_id__cookie_domain = $home_server;

// page given in URL parameter, default page is one
$page = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : 1;

// set number of records per page
$records_per_page = 5;

// calculate for the query LIMIT clause
$from_record_num = ( $records_per_page * $page ) - $records_per_page;
?>