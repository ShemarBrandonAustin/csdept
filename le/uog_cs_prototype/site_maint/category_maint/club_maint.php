<?php
// required headers
header( "Access-Control-Allow-Origin: *" );
header( "Content-Type: text/html; charset=UTF-8" );
header( "Access-Control-Allow-Methods: GET, POST" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

// include core file
require_once "../../config/core.php";

// Verify that an Admin user is logged in
if ( ! isset( $_COOKIE[ $auth_user_id__cookie_name ] ) ) {
    die( "<h3>You MUST log in to be able to perform Site Maintenance!</h3>" );
}

// read the corresponding web form
$form = file_get_contents( "club_maint_form.html" );

// send back that form
echo $form;

// check whether form data was submitted
if ( ! empty( $_POST[ "submit" ] ) ) {
    // include core, database and object files
    require_once "../../config/core.php";
    require_once "../../config/database.php";
    require_once "../../object_full/club_obj_full.php";
    
    // initialisation
    $db = new Database();
    $db_conn = $db->getConnection();
    $club = new Club( $db_conn );
    
    // retrieve the submitted form data
    $operation = $_POST[ "operation" ];
    if ( ( $operation == "update" ) || ( $operation == "delete" ) ) {
        if ( empty( $_POST[ "id" ] ) ) {
            die( "Sorry, unable to " . $operation . " Club. ID is missing." );
        }
        $club->id = $_POST[ "id" ];
    }
    $club->name = ( ! empty( $_POST[ "name" ] ) ? $_POST[ "name" ] : "" );
    $club->description = ( ! empty( $_POST[ "description" ] ) ? $_POST[ "description" ] : "" );
    $club->category_id = ( ! empty( $_POST[ "category_id" ] ) ? $_POST[ "category_id" ] : "" );
    
    // do preliminary validation
    if ( $operation == "create" ) {
        if ( empty( $club->name ) || empty( $club->description ) || empty( $club->category_id ) ) {
            die( "Sorry, unable to create Club. Data is incomplete." );
        }
    }
    
    // store that data into, or remove it from, the database
    switch ( $operation ) {
        case "create":
            $club->created = date( 'Y-m-d H:i:s' );
            $action_result = $club->create();
            break;
            
        case "update":
            $action_result = $club->update();
            break;
            
        case "delete":
            $action_result = $club->delete();
            break;
            
        default:
            $action_result = $k_MyErr_UnknownOp;
            break;
    }
    
    // For Debug ONLY!!!
    //var_dump( $action_result );
    
    // send back a status
    echo "<br/><hr><br/>";
    if ( ( is_int( $action_result ) ) && ( $action_result == $k_MyErr_UnknownOp ) ) {
        // set a client-error response code
        http_response_code( 400 ); // Bad request
        
        // tell the user
        echo "<h3>Sorry, unknown operation requested!</h3>";
        
    } else if ( ( is_int( $action_result ) ) && ( $action_result == $k_MyErr_DuplicateItem ) ) {
        // set a client-error response code
        http_response_code( 400 ); // Bad request
        
        // tell the user
        echo "<h3>Sorry, Club already exists!</h3>";
        
    } else if ( $action_result ) {
        // set a success response code
        http_response_code( 200 ); // OK
        
        // tell the user
        echo "<h3>Club was " . ( $operation . "d" ) . ".</h3>";
        
    } else {
        // set a server-error response code
        http_response_code( 503 ); // Service unavailable
        
        // tell the user
        echo "<h3>Sorry, unable to " . ( $operation . "d" ) . " Club!</h3>";
    }
}
?>
