<?php

// include error file
if ( file_exists( "../../config/my_error.php" ) ) {  // We're in Maintenance subsystem
    require_once "../../config/my_error.php";
} else {  // We're in Retrieval subsystem's Service API layer (via "standalone" HTTP invocation)
    require_once "../../../config/my_error.php";
}

class AlumniMember {
    
    // database connection and table name
    private $db_conn;
    private $table_name = "alumni";
    
    // object properties
    public $id;
    public $first_name;
    public $last_name;
    public $age;
    public $photo_pathname;
    public $research_pathname;
    public $category_id;
    public $category_name;
    public $created;
    
    // constructor with supplied database-connection
    public function __construct( $db_conn ) {
        $this->db_conn = $db_conn;
    }
    
    
    // methods:-
    // -------
    
    // read all Alumni members
    function read() {
        
        // select all query
        $query = "SELECT
                    c.name as category_name, a.id, a.first_name, a.last_name, a.age, 
                    a.photo_pathname, a.research_pathname, a.category_id, a.created
                FROM
                    " . $this->table_name . " a
                    LEFT JOIN
                        categories c
                            ON a.category_id = c.id
                ORDER BY
                    a.created DESC";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // create Alumni member
    function create() {
        global $k_MyErr_DuplicateItem;
        
        // query to check whether record already exists
        $pre_check_query = "SELECT * FROM " . $this->table_name . 
                           " WHERE ( ( first_name = :first_name ) AND ( last_name = :last_name ) )" . 
                           " LIMIT 0,1";
        
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    first_name=:first_name, last_name=:last_name, age=:age, photo_pathname=:photo_pathname, 
                    research_pathname=:research_pathname, category_id=:category_id, created=:created";
        
        // prepare query
        $stmt = $this->db_conn->prepare( $query );
        
        // sanitize
        $this->first_name = htmlspecialchars( strip_tags( $this->first_name ) );
        $this->last_name = htmlspecialchars( strip_tags( $this->last_name ) );
        $this->age = htmlspecialchars( strip_tags( $this->age ) );
        $this->photo_pathname = htmlspecialchars( strip_tags( $this->photo_pathname ) );
        $this->research_pathname = htmlspecialchars( strip_tags( $this->research_pathname ) );
        $this->category_id = htmlspecialchars( strip_tags( $this->category_id ) );
        $this->created = htmlspecialchars( strip_tags( $this->created ) );
        
        // Check whether record already exists
        $pre_check = $this->db_conn->prepare( $pre_check_query );
        $pre_check->bindParam( ":first_name", $this->first_name );
        $pre_check->bindParam( ":last_name", $this->last_name );
        $pre_check->execute();
        $row = $pre_check->fetch( PDO::FETCH_ASSOC );
        if ( $row[ 'first_name' ] != null ) {
            //echo "Duplicate!<br/>";
            return $k_MyErr_DuplicateItem;
        }
        
        // bind values
        $stmt->bindParam( ":first_name", $this->first_name );
        $stmt->bindParam( ":last_name", $this->last_name );
        $stmt->bindParam( ":age", $this->age );
        $stmt->bindParam( ":photo_pathname", $this->photo_pathname );
        $stmt->bindParam( ":research_pathname", $this->research_pathname );
        $stmt->bindParam( ":category_id", $this->category_id );
        $stmt->bindParam( ":created", $this->created );
        
        // execute query
        if ( $stmt->execute() ) {
            return true;
        }
        
        return false;
    }
    
    // used when filling up the update Alumni member form
    function readOne() {
        
        // query to read single record
        $query = "SELECT
                    c.name as category_name, a.id, a.first_name, a.last_name, a.age, 
                    a.photo_pathname, a.research_pathname, a.category_id, a.created
                FROM
                    " . $this->table_name . " a
                    LEFT JOIN
                        categories c
                            ON a.category_id = c.id
                WHERE
                    a.id = ?
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
        $this->first_name = $row[ 'first_name' ];
        $this->last_name = $row[ 'last_name' ];
        $this->age = $row[ 'age' ];
        $this->photo_pathname = $row[ 'photo_pathname' ];
        $this->research_pathname = $row[ 'research_pathname' ];
        $this->category_id = $row[ 'category_id' ];
        $this->category_name = $row[ 'category_name' ];
    }
    
    // update the Alumni member
    function update() {
        
        // sanitize
        $this->first_name = htmlspecialchars(strip_tags( $this->first_name ) );
        $this->last_name = htmlspecialchars(strip_tags( $this->last_name ) );
        $this->age = htmlspecialchars(strip_tags( $this->age ) );
        $this->photo_pathname = htmlspecialchars(strip_tags( $this->photo_pathname ) );
        $this->research_pathname = htmlspecialchars(strip_tags( $this->research_pathname ) );
        $this->category_id = htmlspecialchars(strip_tags( $this->category_id ) );
        $this->id = htmlspecialchars(strip_tags( $this->id ) );
        
        // (non-)emptiness tests
        $bFirstNameSupplied = ! empty( $this->first_name );
        $bLastNameSupplied = ! empty( $this->last_name );
        $bAgeSupplied = ! empty( $this->age );
        $bPhotoSupplied = ! empty( $this->photo_pathname );
        $bResearchSupplied = ! empty( $this->research_pathname );
        $bCatIDSupplied = ! empty( $this->category_id );
        
        // Verify that at least one non-'id' field was supplied
        if ( ! ( $bFirstNameSupplied || $bLastNameSupplied || $bAgeSupplied || 
                 $bPhotoSupplied || $bResearchSupplied || $bCatIDSupplied ) ) {
            return false;
        }
        
        // update query
        $query = "UPDATE
                    " . $this->table_name . "
                SET
                   " . ( $bFirstNameSupplied 
                         ? ( "first_name = :first_name" . 
                             ( ( $bLastNameSupplied || $bAgeSupplied || $bPhotoSupplied || 
                                 $bResearchSupplied || $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bLastNameSupplied 
                         ? ( "last_name = :last_name" . 
                             ( ( $bAgeSupplied || $bPhotoSupplied || $bResearchSupplied || 
                                 $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bAgeSupplied 
                         ? ( "age = :age" . 
                             ( ( $bPhotoSupplied || $bResearchSupplied || $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bPhotoSupplied 
                         ? ( "photo_pathname = :photo_pathname" . 
                             ( ( $bResearchSupplied || $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bResearchSupplied 
                         ? ( "research_pathname = :research_pathname" . 
                             ( $bCatIDSupplied ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bCatIDSupplied ? "category_id = :category_id" : "" ) . "
                WHERE
                    id = :id";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // bind new values
        if ( $bFirstNameSupplied ) { $stmt->bindParam(':first_name', $this->first_name); }
        if ( $bLastNameSupplied ) { $stmt->bindParam(':last_name', $this->last_name); }
        if ( $bAgeSupplied ) { $stmt->bindParam(':age', $this->age); }
        if ( $bPhotoSupplied ) { $stmt->bindParam(':photo_pathname', $this->photo_pathname); }
        if ( $bResearchSupplied ) { $stmt->bindParam(':research_pathname', $this->research_pathname); }
        if ( $bCatIDSupplied ) { $stmt->bindParam(':category_id', $this->category_id); }
        $stmt->bindParam( ':id', $this->id );
        
        // execute the query
        if ( $stmt->execute() ) {
            return true;
        }
        
        return false;
    }
    
    // delete the Alumni member
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
    
    // search Alumni members
    function search( $keywords ) {
        
        // select all query
        $query = "SELECT
                    c.name as category_name, a.id, a.first_name, a.last_name, a.age, 
                    a.photo_pathname, a.research_pathname, a.category_id, a.created
                FROM
                    " . $this->table_name . " a
                    LEFT JOIN
                        categories c
                            ON a.category_id = c.id
                WHERE
                    a.first_name LIKE ? OR a.last_name LIKE ? OR c.name LIKE ?
                ORDER BY
                    a.created DESC";
        
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
    
    // read Alumni members with pagination
    public function readPaging( $from_record_num, $records_per_page ) {
        
        // select query
        $query = "SELECT
                    c.name as category_name, a.id, a.first_name, a.last_name, a.age, 
                    a.photo_pathname, a.research_pathname, a.category_id, a.created
                FROM
                    " . $this->table_name . " a
                    LEFT JOIN
                        categories c
                            ON a.category_id = c.id
                ORDER BY a.created DESC
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
    
    // used for paging Alumni members
    public function count() {
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . "";
        
        $stmt = $this->db_conn->prepare( $query );
        $stmt->execute();
        $row = $stmt->fetch( PDO::FETCH_ASSOC );
        
        return $row[ 'total_rows' ];
    }
    
} // class AlumniMember
?>
