<?php
// required headers
header( "Access-Control-Allow-Origin: *" );
header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: GET" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

// include database and object files
require_once "../../../config/database.php";
require_once "../../../object_full/course_obj_full.php";

// initialisation
$db = new Database();
$db_conn = $db->getConnection();
$course = new Course( $db_conn );

// retrieve the submitted query data (if any)
$course->id = ( isset( $_GET[ "id" ] ) ? $_GET[ "id" ] : "" );
$bSetErrRespCodes = ( isset( $_GET[ "set_error_resp_codes" ] ) ? (bool) $_GET[ "set_error_resp_codes" ] : true );
$bGetAll = empty( $course->id );

// retrieve the requested data from the database, in PDO format
if ( $bGetAll ) {
    $result_data_pdo = $course->read();
    
} else {
    $course->readOne();
}

// convert that retrieved [PDO] data into JSON format
if ( $bGetAll ) {
    $num = $result_data_pdo->rowCount();
    
    // check if any record(s) found
    if ( $num > 0 ) {
        
        // Staff members array
        $courses_arr = array();
        $courses_arr[ "records" ] = array();
        
        // retrieve our table contents
        // [NOTE: fetch() is faster than fetchAll()
        //  (<http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop>)]
        while ( $row = $result_data_pdo->fetch( PDO::FETCH_ASSOC ) ) {
            // extract row
            // this will make $row[ '«field»' ] into just $«field» only
            extract( $row );
            
            $course = array(
                "id" => $id, 
                "number" => $number, 
                "name" => $name, 
                "description" => $description, 
                "programme_id" => $programme_id, 
                "programme_name" => $programme_name, 
                "category_id" => $category_id, 
                "category_name" => $category_name 
            );
            
            array_push( $courses_arr[ "records" ], $course );
        }
        
        // set response code - 200 OK
        http_response_code( 200 );
        
        // make Staff members data into json format
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( $courses_arr );
        
    } else {
        
        // set response code - 404 Not found, unless otherwise requested
        http_response_code( ( $bSetErrRespCodes ? 404 : 200 ) );
        
        // no products found
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( array( "error_message" => "No Courses found." ) );
    }
    
} else {  // there's just one item
    if ( $course->number != null ) {
        // create array
        $course_arr = array(
            "id" =>  $course->id, 
            "number" => $course->number, 
            "name" => $course->name, 
            "description" => $course->description, 
            "programme_id" => $course->programme_id, 
            "programme_name" => $course->programme_name, 
            "category_id" => $course->category_id, 
            "category_name" => $course->category_name 
        );
        
        // set response code - 200 OK
        http_response_code( 200 );
        
        // make it json format
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( $course_arr );
        
    } else {
        // set response code - 404 Not found, unless otherwise requested
        http_response_code( ( $bSetErrRespCodes ? 404 : 200 ) );
        
        // Staff member does not exist
        // [NOTE: PHP's JSON encoder will convert any associative array into an object]
        $result_data_json = json_encode( array( "error_message" => "Sorry, the Course does not exist!" ) );
    }
}

// tell the user
echo $result_data_json;
?>
