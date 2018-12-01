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
$form = file_get_contents( "«category»_maint_form.html" );

// send back that form
echo $form;

// check whether form data was submitted
if ( ! empty( $_POST[ "submit" ] ) ) {
    // include core, database and object files
    require_once "../../config/core.php";
    require_once "../../config/database.php";
    require_once "../../object_full/«category»_obj_full.php";
    
    // initialisation
    $db = new Database();
    $db_conn = $db->getConnection();
    $«catObj» = new «Category»( $db_conn );
    
    // retrieve the submitted form data
    $operation = $_POST[ "operation" ];
    if ( ( $operation == "update" ) || ( $operation == "delete" ) ) {
        if ( empty( $_POST[ "id" ] ) ) {
            die( "Sorry, unable to " . $operation . " «Category». ID is missing." );
        }
        $«catObj»->id = $_POST[ "id" ];
    }
    $«catObj»->«field1» = ( ! empty( $_POST[ "«field1»" ] ) ? $_POST[ "«field1»" ] : "" );
    $«catObj»->«field2» = ( ! empty( $_POST[ "«field2»" ] ) ? $_POST[ "«field2»" ] : "" );
    $«catObj»->«field3» = ( ! empty( $_POST[ "«field3»" ] ) ? $_POST[ "«field3»" ] : "" );
    $«catObj»->«field4» = ( ! empty( $_POST[ "«field4»" ] ) ? $_POST[ "«field4»" ] : "" );
    // … For when rest are *NOT* file input field(s) {being handled separately below} ONLY!!!
    $«catObj»->«field5» = ( ! empty( $_POST[ "«field5»" ] ) ? $_POST[ "«field5»" ] : "" );
    $«catObj»->«field6» = ( ! empty( $_POST[ "«field6»" ] ) ? $_POST[ "«field6»" ] : "" );
    $«catObj»->category_id = ( ! empty( $_POST[ "category_id" ] ) ? $_POST[ "category_id" ] : "" );
    
    // Uncomment for processing file input field(s)
    /*
    // check for any uploaded file(s)
    if ( ( $operation == "create" ) || ( $operation == "update" ) ) {
        $fileUploadFields = array( '«formField5»', '«formField6»' );
        
        // handle any uploaded file(s)
        foreach ( $fileUploadFields as $thisField ) {
            $thisUploadDir = '../../uploads/«category»/' . $thisField . 
                                 ( ( $thisField == «formField5» ) ? 's' : '' ) . '/';
            $thisFieldPathnameVarName = $thisField . '_pathname';
            $«catObj»->$thisFieldPathnameVarName = "";
            
            // For Debug ONLY!!!
            //echo "<br/>"; var_dump( $_FILES ); echo "<br/>";
            
            // handle this uploaded file, if supplied
            if ( $_FILES[ $thisField ][ 'size' ] > 0 ) {
                // dispatch on the upload status
                switch ( $_FILES[ $thisField ][ 'error' ] ) {
                    case UPLOAD_ERR_OK:
                        $$thisFieldPathnameVarName = $thisUploadDir . basename( $_FILES[ $thisField ][ 'name' ] );
                        
                        // Move the temp file into the uploads dir
                        if ( move_uploaded_file( $_FILES[ $thisField ][ 'tmp_name' ], 
                                                 $$thisFieldPathnameVarName ) ) {
                            $«catObj»->$thisFieldPathnameVarName = $$thisFieldPathnameVarName;
                            
                        } else {
                            echo '<pre>';
                            echo "Sorry, could NOT relocate the uploaded " . $thisField . " file!<br/>";
                            echo '</pre>';
                        }
                        break;
                        
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        echo '<pre>';
                        echo "Sorry, the uploaded " . $thisField . " file exceeds the allowed size(s)!<br/>";
                        echo '</pre>';
                        break;
                        
                    case UPLOAD_ERR_NO_TMP_DIR:
                    case UPLOAD_ERR_EXTENSION:
                        echo '<pre>';
                        echo "Sorry, the uploading of the " . $thisField . " file is MISconfigured!<br/>";
                        echo '</pre>';
                        break;
                       
                    case UPLOAD_ERR_PARTIAL:
                    case UPLOAD_ERR_CANT_WRITE:
                        echo '<pre>';
                        echo "Sorry, the uploaded " . $thisField . " file could NOT be [completely] written!<br/>";
                        echo '</pre>';
                        break;
                        
                    default:
                        echo '<pre>';
                        echo "Sorry, an unknown error occurred with the uploaded " . $thisField . " file!<br/>";
                        echo '</pre>';
                        break;
                } // dispatch on the upload status
            } // handle this uploaded file, if supplied
        } // handle any uploaded file(s)
    }  // check for any uploaded file(s)
    */
    
    // do preliminary validation
    if ( $operation == "create" ) {
        // [??? For a create op, also treat the file input field(s) as mandatory ???]
        if ( empty( $«catObj»->«field1» ) || empty( $«catObj»->«field2» ) || empty( $«catObj»->«field3» ) || 
             empty( $«catObj»->«field4» ) || empty( $«catObj»->«field5» || empty( $«catObj»->«field6» ) || 
             empty( $«catObj»->category_id ) ) {
            die( "Sorry, unable to create «Category». Data is incomplete." );
        }
    }
    
    // store that data into, or remove it from, the database
    switch ( $operation ) {
        case "create":
            $«catObj»->created = date( 'Y-m-d H:i:s' );
            $action_result = $«catObj»->create();
            break;
            
        case "update":
            $action_result = $«catObj»->update();
            break;
            
        case "delete":
            $action_result = $«catObj»->delete();
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
        echo "<h3>Sorry, «Category» already exists!</h3>";
        
    } else if ( $action_result ) {
        // set a success response code
        http_response_code( 200 ); // OK
        
        // tell the user
        echo "<h3>«Category» was " . ( $operation . "d" ) . ".</h3>";
        
    } else {
        // set a server-error response code
        http_response_code( 503 ); // Service unavailable
        
        // tell the user
        echo "<h3>Sorry, unable to " . ( $operation . "d" ) . " «Category»!</h3>";
    }
}
?>
