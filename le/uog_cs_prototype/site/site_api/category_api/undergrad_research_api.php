<?php
// required headers
header( "Access-Control-Allow-Origin: *" );
header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: GET" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

// include database and object files
require_once "../../../config/database.php";
require_once "../../../object_full/undergrad_research_obj_full.php";

// initialisation
$db = new Database();
$db_conn = $db->getConnection();
$undergrad_research_item = new UndergradResearchItem( $db_conn );

// retrieve the submitted query data (if any)
$undergrad_research_item->id = ( isset( $_GET[ "id" ] ) ? $_GET[ "id" ] : "" );
$bSetErrRespCodes = ( isset( $_GET[ "set_error_resp_codes" ] ) ? (bool) $_GET[ "set_error_resp_codes" ] : true );
$bGetAll = empty( $undergrad_research_item->id );

// retrieve the requested data from the database, in PDO format
if ( $bGetAll ) {
    $result_data_pdo = $undergrad_research_item->read();
    
} else {
    $undergrad_research_item->readOne();
}

// convert that retrieved [PDO] data into JSON format
if ( $bGetAll ) {
    $num = $result_data_pdo->rowCount();
    
    // check if any record(s) found
    if ( $num > 0 ) {
        
        // Staff members array
        $undergrad_research_items_arr = array();
        $undergrad_research_items_arr[ "records" ] = array();
        
        // retrieve our table contents
        // [NOTE: fetch() is faster than fetchAll()
        //  (<http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop>)]
        while ( $row = $result_data_pdo->fetch( PDO::FETCH_ASSOC ) ) {
            // extract row
            // this will make $row[ '«field»' ] into just $«field» only
            extract( $row );
            
            $undergrad_research_item = array(
                "id" => $id, 
                "researchers" => $researchers, 
                "research_abstract" => $research_abstract, 
                "research_pathname" => $research_pathname, 
                "category_id" => $category_id, 
                "category_name" => $category_name 
            );
            
            array_push( $undergrad_research_items_arr[ "records" ], $undergrad_research_item );
        }
        
        // set response code - 200 OK
        http_response_code( 200 );
        
        // make Staff members data into json format
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( $undergrad_research_items_arr );
        
    } else {
        
        // set response code - 404 Not found, unless otherwise requested
        http_response_code( ( $bSetErrRespCodes ? 404 : 200 ) );
        
        // no products found
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( array( "error_message" => "No Undergrad Research Items found." ) );
    }
    
} else {  // there's just one item
    if ( $undergrad_research_item->researchers != null ) {
        // create array
        $undergrad_research_item_arr = array(
            "id" =>  $undergrad_research_item->id, 
            "researchers" => $undergrad_research_item->researchers, 
            "research_abstract" => $undergrad_research_item->research_abstract, 
            "research_pathname" => $undergrad_research_item->research_pathname, 
            "category_id" => $undergrad_research_item->category_id, 
            "category_name" => $undergrad_research_item->category_name 
        );
        
        // set response code - 200 OK
        http_response_code( 200 );
        
        // make it json format
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( $undergrad_research_item_arr );
        
    } else {
        // set response code - 404 Not found, unless otherwise requested
        http_response_code( ( $bSetErrRespCodes ? 404 : 200 ) );
        
        // Staff member does not exist
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( array( "error_message" => 
                                                "Sorry, the Undergrad Research Item does not exist!" ) );
    }
}

// tell the user
echo $result_data_json;
?>
