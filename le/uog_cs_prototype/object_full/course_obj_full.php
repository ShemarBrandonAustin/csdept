<?php

// include error file
if ( file_exists( "../../config/my_error.php" ) ) {  // We're in Maintenance subsystem
    require_once "../../config/my_error.php";
} else {  // We're in Retrieval subsystem's Service API layer (via "standalone" HTTP invocation)
    require_once "../../../config/my_error.php";
}

class Course {
    
    // database connection and table name
    private $db_conn;
    private $table_name = "courses";
    
    // object properties
    public $id;
    public $number;
    public $name;
    public $description;
    public $programme_id;
    public $programme_name;
    public $category_id;
    public $category_name;
    public $created;
    
    // constructor with supplied database-connection
    public function __construct( $db_conn ) {
        $this->db_conn = $db_conn;
    }
    
    
    // methods:-
    // -------
    
    // read all Courses
    function read() {
        
        // select all query
        $query = "SELECT
                    c.name as category_name, p.name as programme_name, 
                    co.id, co.number, co.name, co.description, 
                    co.programme_id, co.category_id, co.created
                FROM
                    " . $this->table_name . " co
                    LEFT JOIN
                        categories c
                            ON co.category_id = c.id
                    LEFT JOIN
                        programmes p
                            ON co.programme_id = p.id
                ORDER BY
                    co.created DESC";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // create Course
    function create() {
        global $k_MyErr_DuplicateItem;
        
        // query to check whether record already exists
        $pre_check_query = "SELECT * FROM " . $this->table_name . 
                           " WHERE ( ( number = :number ) OR ( name = :name ) )" . 
                           " LIMIT 0,1";
        
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    number=:number, name=:name, description=:description, 
                    programme_id=:programme_id, 
                    category_id=:category_id, created=:created";
        
        // prepare query
        $stmt = $this->db_conn->prepare( $query );
        
        // sanitize
        $this->number = htmlspecialchars( strip_tags( $this->number ) );
        $this->name = htmlspecialchars( strip_tags( $this->name ) );
        $this->description = htmlspecialchars( strip_tags( $this->description ) );
        $this->programme_id = htmlspecialchars( strip_tags( $this->programme_id ) );
        $this->category_id = htmlspecialchars( strip_tags( $this->category_id ) );
        $this->created = htmlspecialchars( strip_tags( $this->created ) );
        
        // Check whether record already exists
        $pre_check = $this->db_conn->prepare( $pre_check_query );
        $pre_check->bindParam( ":number", $this->number );
        $pre_check->bindParam( ":name", $this->name );
        $pre_check->execute();
        $row = $pre_check->fetch( PDO::FETCH_ASSOC );
        if ( $row[ 'number' ] != null ) {
            // For Debug ONLY!!!
            //echo "Duplicate!<br/>";
            
            return $k_MyErr_DuplicateItem;
        }
        
        // bind values
        $stmt->bindParam( ":number", $this->number );
        $stmt->bindParam( ":name", $this->name );
        $stmt->bindParam( ":description", $this->description );
        $stmt->bindParam( ":programme_id", $this->programme_id );
        $stmt->bindParam( ":category_id", $this->category_id );
        $stmt->bindParam( ":created", $this->created );
        
        // execute query
        if ( $stmt->execute() ) {
            return true;
        }
        
        return false;
    }
    
    // used when filling up the update Course form
    function readOne() {
        
        // query to read single record
        $query = "SELECT
                    c.name as category_name, p.name as programme_name, 
                    co.id, co.number, co.name, co.description, 
                    co.programme_id, co.category_id, co.created
                FROM
                    " . $this->table_name . " co
                    LEFT JOIN
                        categories c
                            ON co.category_id = c.id
                    LEFT JOIN
                        programmes p
                            ON co.programme_id = p.id
                WHERE
                    co.id = ?
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
        $this->number = $row[ 'number' ];
        $this->name = $row[ 'name' ];
        $this->description = $row[ 'description' ];
        $this->programme_id = $row[ 'programme_id' ];
        $this->programme_name = $row[ 'programme_name' ];
        $this->category_id = $row[ 'category_id' ];
        $this->category_name = $row[ 'category_name' ];
    }
    
    // update the Course
    function update() {
        
        // sanitize
        $this->number = htmlspecialchars(strip_tags( $this->number ) );
        $this->name = htmlspecialchars(strip_tags( $this->name ) );
        $this->description = htmlspecialchars(strip_tags( $this->description ) );
        $this->programme_id = htmlspecialchars(strip_tags( $this->programme_id ) );
        $this->category_id = htmlspecialchars(strip_tags( $this->category_id ) );
        $this->id = htmlspecialchars(strip_tags( $this->id ) );
        
        // (non-)emptiness tests
        $bNumberSupplied = ! empty( $this->number );
        $bNameSupplied = ! empty( $this->name );
        $bDescriptionSupplied = ! empty( $this->description );
        $bProgIDSupplied = ! empty( $this->programme_id );
        $bCatIDSupplied = ! empty( $this->category_id );
        
        // Verify that at least one non-'id' field was supplied
        if ( ! ( $bNumberSupplied || $bNameSupplied || $bDescriptionSupplied || 
                 $bProgIDSupplied || $bCatIDSupplied ) ) {
            return false;
        }
        
        // update query
        $query = "UPDATE
                    " . $this->table_name . "
                SET
                   " . ( $bNumberSupplied 
                         ? ( "number = :number" . 
                             ( ( $bNameSupplied || $bDescriptionSupplied || $bProgIDSupplied || 
                                 $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bNameSupplied 
                         ? ( "name = :name" . 
                             ( ( $bDescriptionSupplied || $bProgIDSupplied || 
                                 $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bDescriptionSupplied 
                         ? ( "description = :description" . 
                             ( ( $bProgIDSupplied || $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bProgIDSupplied 
                         ? ( "programme_id = :programme_id" . 
                             ( $bCatIDSupplied ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bCatIDSupplied ? "category_id = :category_id" : "" ) . "
                WHERE
                    id = :id";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // bind new values
        if ( $bNumberSupplied ) { $stmt->bindParam(':number', $this->number ); }
        if ( $bNameSupplied ) { $stmt->bindParam(':name', $this->name ); }
        if ( $bDescriptionSupplied ) { $stmt->bindParam(':description', $this->description ); }
        if ( $bProgIDSupplied ) { $stmt->bindParam(':programme_id', $this->programme_id ); }
        if ( $bCatIDSupplied ) { $stmt->bindParam(':category_id', $this->category_id ); }
        $stmt->bindParam( ':id', $this->id );
        
        // execute the query
        if ( $stmt->execute() ) {
            return true;
        }
        
        return false;
    }
    
    // delete the Course
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
    
    // search Courses
    function search( $keywords ) {
     
        // select all query
        $query = "SELECT
                    c.name as category_name, p.name as programme_name, 
                    co.id, co.number, co.name, co.description, 
                    co.programme_id, co.category_id, co.created
                FROM
                    " . $this->table_name . " co
                    LEFT JOIN
                        categories c
                            ON co.category_id = c.id
                    LEFT JOIN
                        programmes p
                            ON co.programme_id = p.id
                WHERE
                    co.number LIKE ? OR co.name LIKE ? OR c.name LIKE ?
                ORDER BY
                    co.created DESC";
        
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
    
    // read Courses with pagination
    public function readPaging( $from_record_num, $records_per_page ) {
        
        // select query
        $query = "SELECT
                    c.name as category_name, p.name as programme_name, 
                    co.id, co.number, co.name, co.description, 
                    co.programme_id, co.category_id, co.created
                FROM
                    " . $this->table_name . " co
                    LEFT JOIN
                        categories c
                            ON co.category_id = c.id
                    LEFT JOIN
                        programmes p
                            ON co.programme_id = p.id
                ORDER BY co.created DESC
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
    
    // used for paging Courses
    public function count() {
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . "";
        
        $stmt = $this->db_conn->prepare( $query );
        $stmt->execute();
        $row = $stmt->fetch( PDO::FETCH_ASSOC );
        
        return $row[ 'total_rows' ];
    }
} // class Course
?>
