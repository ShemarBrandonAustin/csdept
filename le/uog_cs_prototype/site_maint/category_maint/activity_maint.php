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
$form = file_get_contents( "activity_maint_form.html" );

// send back that form
echo $form;

// check whether form data was submitted
if ( ! empty( $_POST[ "submit" ] ) ) {
    // include core, database and object files
    require_once "../../config/core.php";
    require_once "../../config/database.php";
    require_once "../../object_full/activity_obj_full.php";
    
    // initialisation
    $db = new Database();
    $db_conn = $db->getConnection();
    $activity = new Activity( $db_conn );
    
    // retrieve the submitted form data
    $operation = $_POST[ "operation" ];
    if ( ( $operation == "update" ) || ( $operation == "delete" ) ) {
        if ( empty( $_POST[ "id" ] ) ) {
            die( "Sorry, unable to " . $operation . " Activity. ID is missing." );
        }
        $activity->id = $_POST[ "id" ];
    }
    $activity->name = ( ! empty( $_POST[ "name" ] ) ? $_POST[ "name" ] : "" );
    $activity->description = ( ! empty( $_POST[ "description" ] ) ? $_POST[ "description" ] : "" );
    $activity->category_id = ( ! empty( $_POST[ "category_id" ] ) ? $_POST[ "category_id" ] : "" );
    
    // do preliminary validation
    if ( $operation == "create" ) {
        if ( empty( $activity->name ) || empty( $activity->description ) || empty( $activity->category_id ) ) {
            die( "Sorry, unable to create Activity. Data is incomplete." );
        }
    }
    
    // store that data into, or remove it from, the database
    switch ( $operation ) {
        case "create":
            $activity->created = date( 'Y-m-d H:i:s' );
            $action_result = $activity->create();
            break;
            
        case "update":
            $action_result = $activity->update();
            break;
            
        case "delete":
            $action_result = $activity->delete();
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
        echo "<h3>Sorry, Activity already exists!</h3>";
        
    } else if ( $action_result ) {
        // set a success response code
        http_response_code( 200 ); // OK
        
        // tell the user
        echo "<h3>Activity was " . ( $operation . "d" ) . ".</h3>";
        
    } else {
        // set a server-error response code
        http_response_code( 503 ); // Service unavailable
        
        // tell the user
        echo "<h3>Sorry, unable to " . ( $operation . "d" ) . " Activity!</h3>";
    }
}
?>
