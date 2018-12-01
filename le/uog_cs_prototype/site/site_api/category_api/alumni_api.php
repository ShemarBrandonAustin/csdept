<?php
// required headers
header( "Access-Control-Allow-Origin: *" );
header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: GET" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

// include database and object files
require_once "../../../config/database.php";
require_once "../../../object_full/alumni_obj_full.php";

// initialisation
$db = new Database();
$db_conn = $db->getConnection();
$alumni_member = new AlumniMember( $db_conn );

// retrieve the submitted query data (if any)
$alumni_member->id = ( isset( $_GET[ "id" ] ) ? $_GET[ "id" ] : "" );
$bSetErrRespCodes = ( isset( $_GET[ "set_error_resp_codes" ] ) ? (bool) $_GET[ "set_error_resp_codes" ] : true );
$bGetAll = empty( $alumni_member->id );

// retrieve the requested data from the database, in PDO format
if ( $bGetAll ) {
    $result_data_pdo = $alumni_member->read();
    
} else {
    $alumni_member->readOne();
}

// convert that retrieved [PDO] data into JSON format
if ( $bGetAll ) {
    $num = $result_data_pdo->rowCount();
    
    // check if any record(s) found
    if ( $num > 0 ) {
        
        // Alumni members array
        $alumni_members_arr = array();
        $alumni_members_arr[ "records" ] = array();
        
        // retrieve our table contents
        // [NOTE: fetch() is faster than fetchAll()
        //  (<http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop>)]
        while ( $row = $result_data_pdo->fetch( PDO::FETCH_ASSOC ) ) {
            // extract row
            // this will make $row[ '«field»' ] into just $«field» only
            extract( $row );
            
            $alumni_member = array(
                "id" => $id, 
                "first_name" => $first_name, 
                "last_name" => $last_name, 
                "age" => $age, 
                "photo_pathname" => $photo_pathname, 
                "research_pathname" => $research_pathname, 
                "category_id" => $category_id, 
                "category_name" => $category_name 
            );
            
            array_push( $alumni_members_arr[ "records" ], $alumni_member );
        }
        
        // set response code - 200 OK
        http_response_code( 200 );
        
        // make Alumni members data into json format
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( $alumni_members_arr );
        
    } else {
        
        // set response code - 404 Not found, unless otherwise requested
        http_response_code( ( $bSetErrRespCodes ? 404 : 200 ) );
        
        // no products found
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( array( "error_message" => "No Alumni members found." ) );
    }
    
} else {  // there's just one item
    if ( $alumni_member->first_name != null ) {
        // create array
        $alumni_member_arr = array(
            "id" =>  $alumni_member->id, 
            "first_name" => $alumni_member->first_name, 
            "last_name" => $alumni_member->last_name, 
            "age" => $alumni_member->age, 
            "photo_pathname" => $alumni_member->photo_pathname, 
            "research_pathname" => $alumni_member->research_pathname, 
            "category_id" => $alumni_member->category_id, 
            "category_name" => $alumni_member->category_name 
        );
        
        // set response code - 200 OK
        http_response_code( 200 );
        
        // make it json format
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( $alumni_member_arr );
        
    } else {
        // set response code - 404 Not found, unless otherwise requested
        http_response_code( ( $bSetErrRespCodes ? 404 : 200 ) );
        
        // Alumni member does not exist
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( array( "error_message" => "Sorry, the Alumni member does not exist!" ) );
    }
}

// tell the user
echo $result_data_json;
?>
