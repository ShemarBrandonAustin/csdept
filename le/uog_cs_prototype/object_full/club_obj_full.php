<?php

// include error file
if ( file_exists( "../../config/my_error.php" ) ) {  // We're in Maintenance subsystem
    require_once "../../config/my_error.php";
} else {  // We're in Retrieval subsystem's Service API layer (via "standalone" HTTP invocation)
    require_once "../../../config/my_error.php";
}

class Club {
    
    // database connection and table name
    private $db_conn;
    private $table_name = "clubs";
    
    // object properties
    public $id;
    public $name;
    public $description;
    public $category_id;
    public $category_name;
    public $created;
    
    // constructor with supplied database-connection
    public function __construct( $db_conn ) {
        $this->db_conn = $db_conn;
    }
    
    
    // methods:-
    // -------
    
    // read all Clubs
    function read() {
        
        // select all query
        $query = "SELECT
                    c.name as category_name, cl.id, cl.name, cl.description, 
                    cl.category_id, cl.created
                FROM
                    " . $this->table_name . " cl
                    LEFT JOIN
                        categories c
                            ON cl.category_id = c.id
                ORDER BY
                    cl.created DESC";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // create Club
    function create() {
        global $k_MyErr_DuplicateItem;
        
        // query to check whether record already exists
        $pre_check_query = "SELECT * FROM " . $this->table_name . 
                           " WHERE ( name = :name )" . 
                           " LIMIT 0,1";
        
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    name=:name, description=:description, 
                    category_id=:category_id, created=:created";
        
        // prepare query
        $stmt = $this->db_conn->prepare( $query );
        
        // sanitize
        $this->name = htmlspecialchars( strip_tags( $this->name ) );
        $this->description = htmlspecialchars( strip_tags( $this->description ) );
        $this->category_id = htmlspecialchars( strip_tags( $this->category_id ) );
        $this->created = htmlspecialchars( strip_tags( $this->created ) );
        
        // Check whether record already exists
        $pre_check = $this->db_conn->prepare( $pre_check_query );
        $pre_check->bindParam( ":name", $this->name );
        $pre_check->execute();
        $row = $pre_check->fetch( PDO::FETCH_ASSOC );
        if ( $row[ 'name' ] != null ) {
            // For Debug ONLY!!!
            //echo "Duplicate!<br/>";
            
            return $k_MyErr_DuplicateItem;
        }
        
        // bind values
        $stmt->bindParam( ":name", $this->name );
        $stmt->bindParam( ":description", $this->description );
        $stmt->bindParam( ":category_id", $this->category_id );
        $stmt->bindParam( ":created", $this->created );
        
        // execute query
        if ( $stmt->execute() ) {
            return true;
        }
        
        return false;
    }
    
    // used when filling up the update Club form
    function readOne() {
        
        // query to read single record
        $query = "SELECT
                    c.name as category_name, cl.id, cl.name, cl.description, 
                    cl.category_id, cl.created
                FROM
                    " . $this->table_name . " cl
                    LEFT JOIN
                        categories c
                            ON cl.category_id = c.id
                WHERE
                    cl.id = ?
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
        $this->name = $row[ 'name' ];
        $this->description = $row[ 'description' ];
        $this->category_id = $row[ 'category_id' ];
        $this->category_name = $row[ 'category_name' ];
    }
    
    // update the Club
    function update() {
        
        // sanitize
        $this->name = htmlspecialchars(strip_tags( $this->name ) );
        $this->description = htmlspecialchars(strip_tags( $this->description ) );
        $this->category_id = htmlspecialchars(strip_tags( $this->category_id ) );
        $this->id = htmlspecialchars(strip_tags( $this->id ) );
        
        // (non-)emptiness tests
        $bNameSupplied = ! empty( $this->name );
        $bDescriptionSupplied = ! empty( $this->description );
        $bCatIDSupplied = ! empty( $this->category_id );
        
        // Verify that at least one non-'id' field was supplied
        if ( ! ( $bNameSupplied || $bDescriptionSupplied || $bCatIDSupplied ) ) {
            return false;
        }
        
        // update query
        $query = "UPDATE
                    " . $this->table_name . "
                SET
                   " . ( $bNameSupplied 
                         ? ( "name = :name" . 
                             ( ( $bDescriptionSupplied || $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bDescriptionSupplied 
                         ? ( "description = :description" . 
                             ( $bCatIDSupplied ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bCatIDSupplied ? "category_id = :category_id" : "" ) . "
                WHERE
                    id = :id";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // bind new values
        if ( $bNameSupplied ) { $stmt->bindParam(':name', $this->name ); }
        if ( $bDescriptionSupplied ) { $stmt->bindParam(':description', $this->description ); }
        if ( $bCatIDSupplied ) { $stmt->bindParam(':category_id', $this->category_id ); }
        $stmt->bindParam( ':id', $this->id );
        
        // execute the query
        if ( $stmt->execute() ) {
            return true;
        }
        
        return false;
    }
    
    // delete the Club
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
    
    // search Clubs
    function search( $keywords ) {
     
        // select all query
        $query = "SELECT
                    c.name as category_name, cl.id, cl.name, cl.description, 
                    cl.category_id, cl.created
                FROM
                    " . $this->table_name . " cl
                    LEFT JOIN
                        categories c
                            ON cl.category_id = c.id
                WHERE
                    cl.name LIKE ? OR cl.description LIKE ? OR c.name LIKE ?
                ORDER BY
                    cl.created DESC";
        
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
    
    // read Clubs with pagination
    public function readPaging( $from_record_num, $records_per_page ) {
        
        // select query
        $query = "SELECT
                    c.name as category_name, cl.id, cl.name, cl.description, 
                    cl.category_id, cl.created
                FROM
                    " . $this->table_name . " cl
                    LEFT JOIN
                        categories c
                            ON cl.category_id = c.id
                ORDER BY cl.created DESC
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
    
    // used for paging Clubs
    public function count() {
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . "";
        
        $stmt = $this->db_conn->prepare( $query );
        $stmt->execute();
        $row = $stmt->fetch( PDO::FETCH_ASSOC );
        
        return $row[ 'total_rows' ];
    }
} // class Club
?>
