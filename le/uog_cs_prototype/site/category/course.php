<?php
// required headers
header( "Access-Control-Allow-Origin: *" );
header( "Content-Type: text/html; charset=UTF-8" );
header( "Access-Control-Allow-Methods: GET" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

// include (minimal, API-dependent) object file
require_once "../object/course_obj.php";
require_once "../../utilities/string_utils.php";

// initialisation
$course = new CourseViaAPI();

// retrieve the submitted query data (if any)
$course->id = ( isset( $_GET[ "id" ] ) ? $_GET[ "id" ] : 0 );
$bGetAll = empty( $course->id );

// retrieve the requested data from the database via the Service API, in JSON format
$result_data_json = ( $bGetAll ? $course->read() : $course->readOne() );
// … and, strip off any UTF-8 BOM prefix
$str_utils = new StringUtilities();
$result_data_json = $str_utils->stripUTF8BOMPrefix( $result_data_json );

// convert that data into native (PHP) data
// [NOTE: On the API side, PHP's JSON encoder would have converted any associative array into an object]
$result_data_php = json_decode( $result_data_json );

// For Debug ONLY!!!
//var_dump( $result_data_json ); echo "<br/><br/>";
//var_dump( $result_data_php ); echo "<br/><br/>";

// translate that PHP data into markup language
if ( $bGetAll ) {
    $result_data = "<div class='header'><h1>(Undergraduate) Course List</h1></div>";
    
    // dispatch on processing status
    if ( ( $result_data_php != null ) && 
         ( empty( $result_data_php->error_message ) ) ) {  // success
        $all_records = $result_data_php->records;
        $result_data .= '<div class="container"><table border="1" class="table">';
        $result_data .= "<tbody>";
        foreach ( $all_records as $record ) {
            // For Debug ONLY!!!
            //var_dump( $record ); echo "<br/><br/>";
            
            $result_data .= "<tr>" . 
                "<td>" . "Number:" . "<br/>" . 
                         $record->number . "</td>" . 
                "<td>" . "Name:" . "<br/>" . 
                         $record->name . "</td>" . 
                "<td>" . "Details:" . "<br/>" . 
                         '<a href="course.php?id=' .  $record->id . '"' . 
                             ' target="_blank">Information</a>' . "</td>" . 
                "</tr>";
        }
        $result_data .= "</tbody>";
        $result_data .= "</td>";
        
    } else {  // failure
        $result_data .= 
            '<table border="1">' . 
            "<tbody>" . 
            "<tr>" . 
            "<td>" . "Error Message:" . "<br/>" . 
                     ( ! empty( $result_data_php->error_message ) 
                         ? $result_data_php->error_message : "Unknown error." ) . "</td>" . 
            "</tr>" . 
            "</tbody>" . 
            "</table>";
    }
    
} else {  // there's just one record
    $result_data = "<h1>(Undergraduate) Course Details</h1>";
    
    // dispatch on processing status
    if ( ( $result_data_php != null ) && 
         ( empty( $result_data_php->error_message ) ) ) {  // success
        $record = $result_data_php;
        
        $result_data .= "<div class='container'><table class='table'>";
        $result_data .= "<tbody>";
        $result_data .= "<tr>" . 
            "<td>" . "Number:" . "</td>" . 
            "<td>" . $record->number . "</td>" . 
            "</tr>";
        $result_data .= "<tr>" . 
            "<td>" . "Name:" . "</td>" . 
            "<td>" . $record->name . "</td>" . 
            "</tr>";
        $result_data .= "<tr>" . 
            "<td>" . "Description:" . "</td>" . 
            "<td>" . $record->description . "</td>" . 
            "</tr>";
        $result_data .= "<tr>" . 
            "<td>" . "Programme ID:" . "</td>" . 
            "<td>" . $record->programme_id . "</td>" . 
            "</tr>";
        $result_data .= "<tr>" . 
            "<td>" . "Programme Name:" . "</td>" . 
            "<td>" . $record->programme_name . "</td>" . 
            "</tr>";
        $result_data .= "<tr>" . 
            "<td>" . "Category ID:" . "</td>" . 
            "<td>" . $record->category_id . "</td>" . 
            "</tr>";
        $result_data .= "<tr>" . 
            "<td>" . "Category Name:" . "</td>" . 
            "<td>" . $record->category_name . "</td>" . 
            "</tr>";
        $result_data .= "</tbody>";
        $result_data .= "</table>";
        
    } else {  // failure
        $result_data .= 
            "<table>" . 
            "<tbody>" . 
            "<tr>" . 
            "<td>" . "Error Message:" . "</td>" . 
            "<td>" . ( ! empty( $result_data_php->error_message ) 
                         ? $result_data_php->error_message : "Unknown error." ) . "</td>" . 
            "</tr>" . 
            "</tbody>" . 
            "</table>
            </div>";
    }
}

// tell the user
echo "<!DOCTYPE html>" . 
     "<html>" . 
     "<head>" . 
     "<title>" . ( $bGetAll ? "(Undergraduate) Course List" : "(Undergraduate) Course Details" ) . "</title>" . 
     '<meta charset="utf-8">
     <link rel="stylesheet" type="text/css" href="../style.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">' . 
     "</head>" . 
     "<body>" . 
     $result_data . 
     "</body>" . 
     "</html>";
?>
