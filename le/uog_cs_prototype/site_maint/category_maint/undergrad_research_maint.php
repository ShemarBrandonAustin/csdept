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
$form = file_get_contents( "undergrad_research_maint_form.html" );

// send back that form
echo $form;

// check whether form data was submitted
if ( ! empty( $_POST[ "submit" ] ) ) {
    // include core, database and object files
    require_once "../../config/core.php";
    require_once "../../config/database.php";
    require_once "../../object_full/undergrad_research_obj_full.php";
    
    // initialisation
    $db = new Database();
    $db_conn = $db->getConnection();
    $undergrad_research_item = new UndergradResearchItem( $db_conn );
    
    // retrieve the submitted form data
    $operation = $_POST[ "operation" ];
    if ( ( $operation == "update" ) || ( $operation == "delete" ) ) {
        if ( empty( $_POST[ "id" ] ) ) {
            die( "Sorry, unable to " . $operation . " Undergrad Research Item. ID is missing." );
        }
        $undergrad_research_item->id = $_POST[ "id" ];
    }
    $undergrad_research_item->researchers = ( ! empty( $_POST[ "researchers" ] ) ? $_POST[ "researchers" ] : "" );
    $undergrad_research_item->research_abstract = ( ! empty( $_POST[ "research_abstract" ] ) 
                                                      ? $_POST[ "research_abstract" ] : "" );
    $undergrad_research_item->category_id = ( ! empty( $_POST[ "category_id" ] ) ? $_POST[ "category_id" ] : "" );
    
    // check for any uploaded file(s)
    if ( ( $operation == "create" ) || ( $operation == "update" ) ) {
        $fileUploadFields = array( 'research' );
        
        // handle any uploaded file(s)
        foreach ( $fileUploadFields as $thisField ) {
            $thisUploadDir = '../../uploads/undergrads/' . $thisField . '/';
            $thisFieldPathnameVarName = $thisField . '_pathname';
            $undergrad_research_item->$thisFieldPathnameVarName = "";
            
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
                            $undergrad_research_item->$thisFieldPathnameVarName = $$thisFieldPathnameVarName;
                            
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
        if ( empty( $undergrad_research_item->researchers ) || 
             empty( $undergrad_research_item->research_abstract ) || 
             empty( $undergrad_research_item->category_id ) ) {
            die( "Sorry, unable to create Undergrad Research Item. Data is incomplete." );
        }
    }
    
    // store that data into, or remove it from, the database
    switch ( $operation ) {
        case "create":
            $undergrad_research_item->created = date( 'Y-m-d H:i:s' );
            $action_result = $undergrad_research_item->create();
            break;
            
        case "update":
            $action_result = $undergrad_research_item->update();
            break;
            
        case "delete":
            $action_result = $undergrad_research_item->delete();
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
        echo "<h3>Sorry, Undergrad Research Item already exists!</h3>";
        
    } else if ( $action_result ) {
        // set a success response code
        http_response_code( 200 ); // OK
        
        // tell the user
        echo "<h3>Undergrad Research Item was " . ( $operation . "d" ) . ".</h3>";
        
    } else {
        // set a server-error response code
        http_response_code( 503 ); // Service unavailable
        
        // tell the user
        echo "<h3>Sorry, unable to " . ( $operation . "d" ) . " Undergrad Research Item!</h3>";
    }
}
?>
