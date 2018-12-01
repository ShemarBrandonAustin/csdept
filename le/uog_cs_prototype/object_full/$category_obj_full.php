<?php

// include error file
if ( file_exists( "../../config/my_error.php" ) ) {  // We're in Maintenance subsystem
    require_once "../../config/my_error.php";
} else {  // We're in Retrieval subsystem's Service API layer (via "standalone" HTTP invocation)
    require_once "../../../config/my_error.php";
}

class «Category» {
    
    // database connection and table name
    private $db_conn;
    private $table_name = "«db_category»";
    
    // object properties
    public $id;
    public $«field1»;
    public $«field2»;
    public $«field3»;
    public $«field4»;
    public $«field5»;
    public $«field6»;
    public $category_id;
    public $category_name;
    public $created;
    
    // constructor with supplied database-connection
    public function __construct( $db_conn ) {
        $this->db_conn = $db_conn;
    }
    
    
    // methods:-
    // -------
    
    // read all «Category»s
    function read() {
        
        // select all query
        $query = "SELECT
                    c.name as category_name, «cat».id, «cat».«field1», «cat».«field2», «cat».«field3», 
                    «cat».«field4», «cat».«field5», «cat».«field6», «cat».category_id, «cat».created
                FROM
                    " . $this->table_name . " «cat»
                    LEFT JOIN
                        categories c
                            ON «cat».category_id = c.id
                ORDER BY
                    «cat».created DESC";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // create «Category»
    function create() {
        global $k_MyErr_DuplicateItem;
        
        // query to check whether record already exists
        $pre_check_query = "SELECT * FROM " . $this->table_name . 
                           " WHERE ( ( «field1» = :«field1» ) AND ( «field2» = :«field2» ) )" . 
                           " LIMIT 0,1";
        
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    «field1»=:«field1», «field2»=:«field2», «field3»=:«field3», 
                    «field4»=:«field4», «field5»=:«field5», «field6»=:«field6», 
                    category_id=:category_id, created=:created";
        
        // prepare query
        $stmt = $this->db_conn->prepare( $query );
        
        // sanitize
        $this->«field1» = htmlspecialchars( strip_tags( $this->«field1» ) );
        $this->«field2» = htmlspecialchars( strip_tags( $this->«field2» ) );
        $this->«field3» = htmlspecialchars( strip_tags( $this->«field3» ) );
        $this->«field4» = htmlspecialchars( strip_tags( $this->«field4» ) );
        $this->«field5» = htmlspecialchars( strip_tags( $this->«field5» ) );
        $this->«field6» = htmlspecialchars( strip_tags( $this->«field6» ) );
        $this->category_id = htmlspecialchars( strip_tags( $this->category_id ) );
        $this->created = htmlspecialchars( strip_tags( $this->created ) );
        
        // Check whether record already exists
        $pre_check = $this->db_conn->prepare( $pre_check_query );
        $pre_check->bindParam( ":«field1»", $this->«field1» );
        $pre_check->bindParam( ":«field2»", $this->«field2» );
        $pre_check->execute();
        $row = $pre_check->fetch( PDO::FETCH_ASSOC );
        if ( $row[ '«field1»' ] != null ) {
            // For Debug ONLY!!!
            //echo "Duplicate!<br/>";
            
            return $k_MyErr_DuplicateItem;
        }
        
        // bind values
        $stmt->bindParam( ":«field1»", $this->«field1» );
        $stmt->bindParam( ":«field2»", $this->«field2» );
        $stmt->bindParam( ":«field3»", $this->«field3» );
        $stmt->bindParam( ":«field4»", $this->«field4» );
        $stmt->bindParam( ":«field5»", $this->«field5» );
        $stmt->bindParam( ":«field6»", $this->«field6» );
        $stmt->bindParam( ":category_id", $this->category_id );
        $stmt->bindParam( ":created", $this->created );
        
        // execute query
        if ( $stmt->execute() ) {
            return true;
        }
        
        return false;
    }
    
    // used when filling up the update «Category» form
    function readOne() {
        
        // query to read single record
        $query = "SELECT
                    c.name as category_name, «cat».id, «cat».«field1», «cat».«field2», «cat».«field3», 
                        «cat».«field4», «cat».«field5», «cat».«field6», «cat».category_id, «cat».created
                FROM
                    " . $this->table_name . " «cat»
                    LEFT JOIN
                        categories c
                            ON «cat».category_id = c.id
                WHERE
                    «cat».id = ?
                LIMIT
                    0,1";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // bind id of product to be updated
        $stmt->bindParam( 1, $this->id );
        
        // execute query
        $stmt->execute();
        
        // get retrieved row
        $row = $stmt->fetch( PDO::FETCH_ASSOC );
        
        // set values to object properties
        $this->«field1» = $row[ '«field1»' ];
        $this->«field2» = $row[ '«field2»' ];
        $this->«field3» = $row[ '«field3»' ];
        $this->«field4» = $row[ '«field4»' ];
        $this->«field5» = $row[ '«field5»' ];
        $this->«field6» = $row[ '«field6»' ];
        $this->category_id = $row[ 'category_id' ];
        $this->category_name = $row[ 'category_name' ];
    }
    
    // update the «Category»
    function update() {
        
        // sanitize
        $this->«field1» = htmlspecialchars(strip_tags( $this->«field1» ) );
        $this->«field2» = htmlspecialchars(strip_tags( $this->«field2» ) );
        $this->«field3» = htmlspecialchars(strip_tags( $this->«field3» ) );
        $this->«field4» = htmlspecialchars(strip_tags( $this->«field4» ) );
        $this->«field5» = htmlspecialchars(strip_tags( $this->«field5» ) );
        $this->«field6» = htmlspecialchars(strip_tags( $this->«field6» ) );
        $this->category_id = htmlspecialchars(strip_tags( $this->category_id ) );
        $this->id = htmlspecialchars(strip_tags( $this->id ) );
        
        // (non-)emptiness tests
        $b«field1»Supplied = ! empty( $this->«field1» );
        $b«field2»Supplied = ! empty( $this->«field2» );
        $b«field3»Supplied = ! empty( $this->«field3» );
        $b«field4»Supplied = ! empty( $this->«field4» );
        $b«field5»Supplied = ! empty( $this->«field5» );
        $b«field6»Supplied = ! empty( $this->«field6» );
        $bCatIDSupplied = ! empty( $this->category_id );
        
        // Verify that at least one non-'id' field was supplied
        if ( ! ( $b«field1»Supplied || $b«field2»Supplied || $b«field3»Supplied || 
                 $b«field4»Supplied || $b«field5»Supplied || $b«field6»Supplied || 
                 $bCatIDSupplied ) ) {
            return false;
        }
        
        // update query
        $query = "UPDATE
                    " . $this->table_name . "
                SET
                   " . ( $b«field1»Supplied 
                         ? ( "«field1» = :«field1»" . 
                             ( ( $b«field2»Supplied || $b«field3»Supplied || $b«field4»Supplied || 
                                 $b«field5»Supplied || $b«field6»Supplied || $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $b«field2»Supplied 
                         ? ( "«field2» = :«field2»" . 
                             ( ( $b«field3»Supplied || $b«field4»Supplied || $b«field5»Supplied || 
                                 $b«field6»Supplied || $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $b«field3»Supplied 
                         ? ( "«field3» = :«field3»" . 
                             ( ( $b«field4»Supplied || $b«field5»Supplied || $b«field6»Supplied || 
                                 $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $b«field4»Supplied 
                         ? ( "«field4» = :«field4»" . 
                             ( ( $b«field5»Supplied || $b«field6»Supplied || $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $b«field5»Supplied 
                         ? ( "«field5» = :«field5»" . 
                             ( ( $b«field6»Supplied || $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $b«field6»Supplied 
                         ? ( "«field6» = :«field6»" . 
                             ( $bCatIDSupplied ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bCatIDSupplied ? "category_id = :category_id" : "" ) . "
                WHERE
                    id = :id";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // bind new values
        if ( $b«field1»Supplied ) { $stmt->bindParam( ':«field1»', $this->«field1» ); }
        if ( $b«field2»Supplied ) { $stmt->bindParam( ':«field2»', $this->«field2» ); }
        if ( $b«field3»Supplied ) { $stmt->bindParam( ':«field3»', $this->«field3» ); }
        if ( $b«field4»Supplied ) { $stmt->bindParam( ':«field4»', $this->«field4» ); }
        if ( $b«field5»Supplied ) { $stmt->bindParam( ':«field5»', $this->«field5» ); }
        if ( $b«field6»Supplied ) { $stmt->bindParam( ':«field6»', $this->«field6» ); }
        if ( $bCatIDSupplied ) { $stmt->bindParam( ':category_id', $this->category_id ); }
        $stmt->bindParam( ':id', $this->id );
        
        // execute the query
        if ( $stmt->execute() ) {
            return true;
        }
        
        return false;
    }
    
    // delete the «Category»
    function delete() {
        
        // delete query
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        // prepare query
        $stmt = $this->db_conn->prepare( $query );
        
        // sanitize
        $this->id = htmlspecialchars( strip_tags( $this->id ) );
        
        // bind id of record to delete
        $stmt->bindParam( 1, $this->id );
        
        // execute query
        if ( $stmt->execute() ) {
            return true;
        }
        
        return false;
    }
    
    // search «Category»s
    function search( $keywords ) {
     
        // select all query
        $query = "SELECT
                    c.name as category_name, «cat».id, «cat».«field1», «cat».«field2», «cat».«field3», 
                    «cat».«field4», «cat».«field5», «cat».«field6», «cat».category_id, «cat».created
                FROM
                    " . $this->table_name . " «cat»
                    LEFT JOIN
                        categories c
                            ON «cat».category_id = c.id
                WHERE
                    «cat».«field1» LIKE ? OR «cat».«field2» LIKE ? OR c.name LIKE ?
                ORDER BY
                    «cat».created DESC";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // sanitize
        $keywords = htmlspecialchars( strip_tags( $keywords ) );
        $keywords = "%{$keywords}%";
        
        // bind
        $stmt->bindParam( 1, $keywords );
        $stmt->bindParam( 2, $keywords );
        $stmt->bindParam( 3, $keywords );
        
        // execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // read «Category»s with pagination
    public function readPaging( $from_record_num, $records_per_page ) {
        
        // select query
        $query = "SELECT
                    c.name as category_name, «cat».id, «cat».«field1», «cat».«field2», «cat».«field3», 
                    «cat».«field4», «cat».«field5», «cat».«field6», «cat».category_id, «cat».created
                FROM
                    " . $this->table_name . " «cat»
                    LEFT JOIN
                        categories c
                            ON «cat».category_id = c.id
                ORDER BY «cat».created DESC
                LIMIT ?, ?";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // bind variable values
        $stmt->bindParam( 1, $from_record_num, PDO::PARAM_INT );
        $stmt->bindParam( 2, $records_per_page, PDO::PARAM_INT );
        
        // execute query
        $stmt->execute();
        
        // return values from database
        return $stmt;
    }
    
    // used for paging «Category»s
    public function count() {
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . "";
        
        $stmt = $this->db_conn->prepare( $query );
        $stmt->execute();
        $row = $stmt->fetch( PDO::FETCH_ASSOC );
        
        return $row[ 'total_rows' ];
    }
} // class «Category»
?>
