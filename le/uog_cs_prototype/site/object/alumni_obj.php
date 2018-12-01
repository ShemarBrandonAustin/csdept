<?php

// include core file
require_once "../../config/core.php";

class AlumniMemberViaAPI {
    
    // service api url
    private $service_api_url;
    
    // object properties
    public $id;
    
    // constructor
    public function __construct() {
        global $home_server;
        
        // get the grandparent dir of our *top-level includer* PHP script ('alumni.php')
        $grandparentDir = dirname( dirname( $_SERVER[ 'PHP_SELF' ] ) ) . "/";
        
        // build our service api url
        $this->service_api_url = "http://" . $home_server . $grandparentDir . 
                                     "site_api/category_api/alumni_api.php";
        
        // For Debug ONLY!!!
        //var_dump( $this->service_api_url );
    }
    
    
    // methods:-
    // -------
    
    // read all Alumni members
    function read() {
        
        // retrieve the data from the database via the Service API, in JSON format
        $query_part__url_enc = http_build_query( array( "set_error_resp_codes" => false ) );
        $data_json = file_get_contents( ( $this->service_api_url . "?" . $query_part__url_enc ) );
        
        return $data_json;
    }
    
    // used when reading data for just one Alumni entry
    function readOne() {
        
        // retrieve the data from the database via the Service API, in JSON format
        $query_part__url_enc = http_build_query( array( "id" => $this->id, 
                                                        "set_error_resp_codes" => false ) );
        $data_json = file_get_contents( ( $this->service_api_url . "?" . $query_part__url_enc ) );
        
        return $data_json;
    }
    
    // !!! NOTE: The methods below are NOT yet actually implemented in our Service API !!!
    /*
    // search Alumni members
    function search( $keywords ) {
        
        // retrieve the data from the database via the Service API, in JSON format
        $query_part__url_enc = http_build_query( array( "op" => "search", 
                                                        "s" => $keywords, 
                                                        "set_error_resp_codes" => false ) );
        $data_json = file_get_contents( ( $this->service_api_url . "?" . $query_part__url_enc ) );
        
        return $data_json;
    }
    
    // read Alumni members with pagination
    public function readPaging( $from_record_num, $records_per_page ) {
        
        // retrieve the data from the database via the Service API, in JSON format
        $query_part__url_enc = http_build_query( array( "op" => "read_paging", 
                                                        "start_at" => $from_record_num, 
                                                        "batch_count" => $records_per_page, 
                                                        "set_error_resp_codes" => false ) ) );
        $data_json = file_get_contents( ( $this->service_api_url . "?" . $query_part__url_enc ) );
        
        return $data_json;
    }
    
    // used for paging Alumni members
    public function count() {
        
        // retrieve the data from the database via the Service API, in JSON format
        $query_part__url_enc = http_build_query( array( "op" => "count", 
                                                        "set_error_resp_codes" => false ) );
        $data_json = file_get_contents( ( $this->service_api_url . "?" . $query_part__url_enc ) );
        
        return $data_json;
    }
    */
} // class AlumniMemberViaAPI
?>
