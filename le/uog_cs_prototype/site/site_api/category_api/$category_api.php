<?php
// required headers
header( "Access-Control-Allow-Origin: *" );
header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: GET" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

// include database and object files
require_once "../../../config/database.php";
require_once "../../../object_full/«category»_obj_full.php";

// initialisation
$db = new Database();
$db_conn = $db->getConnection();
$«catObj» = new «Category»( $db_conn );

// retrieve the submitted query data (if any)
$«catObj»->id = ( isset( $_GET[ "id" ] ) ? $_GET[ "id" ] : "" );
$bSetErrRespCodes = ( isset( $_GET[ "set_error_resp_codes" ] ) ? (bool) $_GET[ "set_error_resp_codes" ] : true );
$bGetAll = empty( $«catObj»->id );

// retrieve the requested data from the database, in PDO format
if ( $bGetAll ) {
    $result_data_pdo = $«catObj»->read();
    
} else {
    $«catObj»->readOne();
}

// convert that retrieved [PDO] data into JSON format
if ( $bGetAll ) {
    $num = $result_data_pdo->rowCount();
    
    // check if any record(s) found
    if ( $num > 0 ) {
        
        // Staff members array
        $«catObj»s_arr = array();
        $«catObj»s_arr[ "records" ] = array();
        
        // retrieve our table contents
        // [NOTE: fetch() is faster than fetchAll()
        //  (<http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop>)]
        while ( $row = $result_data_pdo->fetch( PDO::FETCH_ASSOC ) ) {
            // extract row
            // this will make $row[ '«field»' ] into just $«field» only
            extract( $row );
            
            $«catObj» = array(
                "id" => $id, 
                "«field1»" => $«field1», 
                "«field2»" => $«field2», 
                "«field3»" => $«field3», 
                "«field4»" => $«field4», 
                "«field5»" => $«field5», 
                "«field6»" => $«field6», 
                "category_id" => $category_id, 
                "category_name" => $category_name 
            );
            
            array_push( $«catObj»s_arr[ "records" ], $«catObj» );
        }
        
        // set response code - 200 OK
        http_response_code( 200 );
        
        // make Staff members data into json format
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( $«catObj»s_arr );
        
    } else {
        
        // set response code - 404 Not found, unless otherwise requested
        http_response_code( ( $bSetErrRespCodes ? 404 : 200 ) );
        
        // no products found
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( array( "error_message" => "No «Category»s found." ) );
    }
    
} else {  // there's just one item
    if ( $«catObj»->«field1» != null ) {
        // create array
        $«catObj»_arr = array(
            "id" =>  $«catObj»->id, 
            "«field1»" => $«catObj»->«field1», 
            "«field2»" => $«catObj»->«field2», 
            "«field3»" => $«catObj»->«field3», 
            "«field4»" => $«catObj»->«field4», 
            "«field5»" => $«catObj»->«field5», 
            "«field6»" => $«catObj»->«field6», 
            "category_id" => $«catObj»->category_id, 
            "category_name" => $«catObj»->category_name 
        );
        
        // set response code - 200 OK
        http_response_code( 200 );
        
        // make it json format
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( $«catObj»_arr );
        
    } else {
        // set response code - 404 Not found, unless otherwise requested
        http_response_code( ( $bSetErrRespCodes ? 404 : 200 ) );
        
        // Staff member does not exist
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( array( "error_message" => "Sorry, the «Category» does not exist!" ) );
    }
}

// tell the user
echo $result_data_json;
?>
