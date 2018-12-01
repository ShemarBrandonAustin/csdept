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
$form = file_get_contents( "alumni_maint_form.html" );

// send back that form
echo $form;

// check whether form data was submitted
if ( ! empty( $_POST[ "submit" ] ) ) {
    // include core, database and object files
    require_once "../../config/core.php";
    require_once "../../config/database.php";
    require_once "../../object_full/alumni_obj_full.php";
    
    // initialisation
    $db = new Database();
    $db_conn = $db->getConnection();
    $alumni_member = new AlumniMember( $db_conn );
    
    // retrieve the submitted form data
    $operation = $_POST[ "operation" ];
    if ( ( $operation == "update" ) || ( $operation == "delete" ) ) {
        $alumni_member->id = $_POST[ "id" ];
    }
    $alumni_member->first_name = ( ! empty( $_POST[ "first_name" ] ) ? $_POST[ "first_name" ] : "" );
    $alumni_member->last_name = ( ! empty( $_POST[ "last_name" ] ) ? $_POST[ "last_name" ] : "" );
    $alumni_member->age = ( ! empty( $_POST[ "age" ] ) ? $_POST[ "age" ] : "" );
    $alumni_member->category_id = ( ! empty( $_POST[ "category_id" ] ) ? $_POST[ "category_id" ] : "" );
    
    // check for any uploaded file(s)
    if ( ( $operation == "create" ) || ( $operation == "update" ) ) {
        $fileUploadFields = array( 'photo', 'research' );
        
        // handle any uploaded file(s)
        foreach ( $fileUploadFields as $thisField ) {
            $thisUploadDir = '../../uploads/alumni/' . $thisField . ( ( $thisField == 'photo' ) ? 's' : '' ) . '/';
            $thisFieldPathnameVarName = $thisField . '_pathname';
            $alumni_member->$thisFieldPathnameVarName = "";
            
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
                            $alumni_member->$thisFieldPathnameVarName = $$thisFieldPathnameVarName;
                            
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
    
    // do preliminary validation
    if ( $operation == "create" ) {
        // [??? For a create op, also treat the file input field(s) as mandatory ???]
        if ( empty( $alumni_member->first_name ) || empty( $alumni_member->last_name ) || 
             empty( $alumni_member->age ) || empty( $alumni_member->category_id ) ) {
            die( "Sorry, unable to create Alumni member. Data is incomplete." );
        }
    }
    
    // store that data into, or remove it from, the database
    switch ( $operation ) {
        case "create":
            $alumni_member->created = date( 'Y-m-d H:i:s' );
            $action_result = $alumni_member->create();
            break;
            
        case "update":
            $action_result = $alumni_member->update();
            break;
            
        case "delete":
            $action_result = $alumni_member->delete();
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
        echo "<h3>Sorry, alumni member already exists!</h3>";
        
    } else if ( $action_result ) {
        // set a success response code
        http_response_code( ( $operation == "create" ) ? 201 : 200 ); // Created, or OK
        
        // tell the user
        echo "<h3>Alumni member was " . ( $operation . "d" ) . ".</h3>";
        
    } else {
        // set a server-error response code
        http_response_code( 503 ); // Service unavailable
        
        // tell the user
        echo "<h3>Sorry, unable to " . $operation . " Alumni member!</h3>";
    }
}
?>
