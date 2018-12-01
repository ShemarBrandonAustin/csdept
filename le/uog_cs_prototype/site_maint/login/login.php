<?php
// required headers
header( "Access-Control-Allow-Origin: *" );
header( "Content-Type: text/html; charset=UTF-8" );
header( "Access-Control-Allow-Methods: GET, POST" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

// check whether it's the first-time load
if ( empty( $_POST[ "submit" ] ) ) {
    // read the corresponding web form
    $form = file_get_contents( "login_form.html" );
    
    // send back that form
    echo $form;
    
} else { // form data was submitted
    // include core, database and object files
    require_once "../../config/core.php";
    require_once "../../config/database.php";
    require_once "login_obj.php";
    
    // initialisation
    $db = new Database();
    $db_conn = $db->getConnection();
    $account = new Account( $db_conn );
    
    // retrieve the submitted form data
    $operation = $_POST[ "operation" ];
    if ( ( $operation == "update" ) || ( $operation == "delete" ) ) {
        $account->id = $_POST[ "id" ];
    }
    $account->user_name = ( ! empty( $_POST[ "user_name" ] ) ? $_POST[ "user_name" ] : "" );
    $password = ( ! empty( $_POST[ "password" ] ) ? $_POST[ "password" ] : "" );
    
    // do preliminary validation
    if ( $operation == "create" ) {
        if ( empty( $account->user_name ) || empty( $password ) ) {
            die( "Sorry, unable to create Account. Data is incomplete." );
        }
    }
    
    // store that data into, remove it from, or check it against, the database
    switch ( $operation ) {
        case "create":
            $account->created = date( 'Y-m-d H:i:s' );
            $action_result = $account->create( $password );
            break;
            
        case "update":
            $action_result = $account->update();
            break;
            
        case "delete":
            $action_result = $account->delete();
            break;
            
        case "verify":
            $action_result = $account->verify( $password );
            break;
            
        default:
            $action_result = $k_MyErr_UnknownOp;
            break;
    }
    
    // For Debug ONLY!!!
    //var_dump( $action_result );
    
    // Check whether to first send back the corresponding web form
    if ( ! ( ( $operation == "verify" ) && 
             ( ( is_bool( $action_result ) ) && ( $action_result ) ) ) ) {  // It's *not* a successful Login
        // read the corresponding web form
        $form = file_get_contents( "login_form.html" );
        
        // send back that form
        echo $form;
    }
    
    // send back a status, or redirect appropriately
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
        echo "<h3>Sorry, Account already exists!</h3>";
        
    } else if ( ( is_int( $action_result ) ) && ( $action_result == $k_MyErr_IllegalPassword ) ) {
        // set a client-error response code
        http_response_code( 400 ); // Bad request
        
        // tell the user
        echo "<h3>Sorry, requested password is illegal (e.g.: too long, etc.)!</h3>";
        
    } else if ( $action_result ) {
        // set a success response code
        http_response_code( ( $operation == "create" ) ? 201 : 200 ); // Created, or OK
        
        // dispatch on the operation
        if ( $operation == "verify" ) {
            // get the grandparent dir of *this script itself* (it is redirected-to, *not* included)
            // [NOTE: this will be the 'site_maint' subfolder, without a trailing slash]
            $grandparentDir = dirname( dirname( $_SERVER[ 'PHP_SELF' ] ) );
            
            // local initialisation
            $k_Cookie_ExpirationSpec_SessionEnd = 0;
            $auth_user_id__cookie_domain_path = $grandparentDir;
            
            // set an "authenticated" cookie
            if ( setcookie( $auth_user_id__cookie_name, (string) $account->id, 
                            $k_Cookie_ExpirationSpec_SessionEnd, 
                            $auth_user_id__cookie_domain_path, $auth_user_id__cookie_domain ) ) {
                // redirect to the master Site Maintenance page
                header( "Location: ../index_maint.php" );
                
            } else {
                // (re-)set a server-error response code
                http_response_code( 500 ); // Internal server error
                
                // tell the user
                echo "<h3>Sorry, could NOT set the required Authentication cookie!</h3>";
            }
            
        } else {
            // tell the user
            echo "<h3>Account was " . ( $operation . "d" ) . ".</h3>";
        }
        
    } else {
        // set a server-error response code
        http_response_code( 503 ); // Service unavailable
        
        // dispatch on the operation
        if ( $operation == "verify" ) {
            // tell the user
            echo "<h3>Sorry, the Account credentials are incorrect!</h3>";
            
        } else {
            // tell the user
            echo "<h3>Sorry, unable to " . $operation . " Account!</h3>";
        }
    }
}
?>
