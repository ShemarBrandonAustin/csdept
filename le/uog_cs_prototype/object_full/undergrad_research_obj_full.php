<?php

// include error file
if ( file_exists( "../../config/my_error.php" ) ) {  // We're in Maintenance subsystem
    require_once "../../config/my_error.php";
} else {  // We're in Retrieval subsystem's Service API layer (via "standalone" HTTP invocation)
    require_once "../../../config/my_error.php";
}

class UndergradResearchItem {
    
    // database connection and table name
    private $db_conn;
    private $table_name = "undergrad_research";
    
    // object properties
    public $id;
    public $researchers;
    public $research_abstract;
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
    
    // read all UndergradResearchItems
    function read() {
        
        // select all query
        $query = "SELECT
                    c.name as category_name, ur.id, ur.researchers, ur.research_abstract, 
                    ur.research_pathname, ur.category_id, ur.created
                FROM
                    " . $this->table_name . " ur
                    LEFT JOIN
                        categories c
                            ON ur.category_id = c.id
                ORDER BY
                    ur.created DESC";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // create UndergradResearchItem
    function create() {
        global $k_MyErr_DuplicateItem;
        
        // query to check whether record already exists
        $pre_check_query = "SELECT * FROM " . $this->table_name . 
                           " WHERE ( ( researchers = :researchers ) AND ( research_abstract = :research_abstract ) )" . 
                           " LIMIT 0,1";
        
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    researchers=:researchers, research_abstract=:research_abstract, 
                    research_pathname=:research_pathname, 
                    category_id=:category_id, created=:created";
        
        // prepare query
        $stmt = $this->db_conn->prepare( $query );
        
        // sanitize
        $this->researchers = htmlspecialchars( strip_tags( $this->researchers ) );
        $this->research_abstract = htmlspecialchars( strip_tags( $this->research_abstract ) );
        $this->research_pathname = htmlspecialchars( strip_tags( $this->research_pathname ) );
        $this->category_id = htmlspecialchars( strip_tags( $this->category_id ) );
        $this->created = htmlspecialchars( strip_tags( $this->created ) );
        
        // Check whether record already exists
        $pre_check = $this->db_conn->prepare( $pre_check_query );
        $pre_check->bindParam( ":researchers", $this->researchers );
        $pre_check->bindParam( ":research_abstract", $this->research_abstract );
        $pre_check->execute();
        $row = $pre_check->fetch( PDO::FETCH_ASSOC );
        if ( $row[ 'researchers' ] != null ) {
            // For Debug ONLY!!!
            //echo "Duplicate!<br/>";
            
            return $k_MyErr_DuplicateItem;
        }
        
        // bind values
        $stmt->bindParam( ":researchers", $this->researchers );
        $stmt->bindParam( ":research_abstract", $this->research_abstract );
        $stmt->bindParam( ":research_pathname", $this->research_pathname );
        $stmt->bindParam( ":category_id", $this->category_id );
        $stmt->bindParam( ":created", $this->created );
        
        // execute query
        if ( $stmt->execute() ) {
            return true;
        }
        
        return false;
    }
    
    // used when filling up the update UndergradResearchItem form
    function readOne() {
        
        // query to read single record
        $query = "SELECT
                    c.name as category_name, ur.id, ur.researchers, ur.research_abstract, 
                    ur.research_pathname, ur.category_id, ur.created
                FROM
                    " . $this->table_name . " ur
                    LEFT JOIN
                        categories c
                            ON ur.category_id = c.id
                WHERE
                    ur.id = ?
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
        $this->researchers = $row[ 'researchers' ];
        $this->research_abstract = $row[ 'research_abstract' ];
        $this->research_pathname = $row[ 'research_pathname' ];
        $this->category_id = $row[ 'category_id' ];
        $this->category_name = $row[ 'category_name' ];
    }
    
    // update the UndergradResearchItem
    function update() {
        
        // sanitize
        $this->researchers = htmlspecialchars(strip_tags( $this->researchers ) );
        $this->research_abstract = htmlspecialchars(strip_tags( $this->research_abstract ) );
        $this->research_pathname = htmlspecialchars(strip_tags( $this->research_pathname ) );
        $this->category_id = htmlspecialchars(strip_tags( $this->category_id ) );
        $this->id = htmlspecialchars(strip_tags( $this->id ) );
        
        // (non-)emptiness tests
        $bResearchersSupplied = ! empty( $this->researchers );
        $bResearchAbstractSupplied = ! empty( $this->research_abstract );
        $bResearchPathnameSupplied = ! empty( $this->research_pathname );
        $bCatIDSupplied = ! empty( $this->category_id );
        
        // Verify that at least one non-'id' field was supplied
        if ( ! ( $bResearchersSupplied || $bResearchAbstractSupplied || $bResearchPathnameSupplied || 
                 $bCatIDSupplied ) ) {
            return false;
        }
        
        // update query
        $query = "UPDATE
                    " . $this->table_name . "
                SET
                   " . ( $bResearchersSupplied 
                         ? ( "researchers = :researchers" . 
                             ( ( $bResearchAbstractSupplied || $bResearchPathnameSupplied || 
                                 $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bResearchAbstractSupplied 
                         ? ( "research_abstract = :research_abstract" . 
                             ( ( $bResearchPathnameSupplied || $bCatIDSupplied ) ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bResearchPathnameSupplied 
                         ? ( "research_pathname = :research_pathname" . 
                             ( $bCatIDSupplied ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bCatIDSupplied ? "category_id = :category_id" : "" ) . "
                WHERE
                    id = :id";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // bind new values
        if ( $bResearchersSupplied ) { $stmt->bindParam(':researchers', $this->researchers ); }
        if ( $bResearchAbstractSupplied ) { $stmt->bindParam(':research_abstract', $this->research_abstract ); }
        if ( $bResearchPathnameSupplied ) { $stmt->bindParam(':research_pathname', $this->research_pathname ); }
        if ( $bCatIDSupplied ) { $stmt->bindParam(':category_id', $this->category_id ); }
        $stmt->bindParam( ':id', $this->id );
        
        // execute the query
        if ( $stmt->execute() ) {
            return true;
        }
        
        return false;
    }
    
    // delete the UndergradResearchItem
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
    
    // search UndergradResearchItems
    function search( $keywords ) {
     
        // select all query
        $query = "SELECT
                    c.name as category_name, ur.id, ur.researchers, ur.research_abstract, 
                    ur.research_pathname, ur.category_id, ur.created
                FROM
                    " . $this->table_name . " ur
                    LEFT JOIN
                        categories c
                            ON ur.category_id = c.id
                WHERE
                    ur.researchers LIKE ? OR ur.research_abstract LIKE ? OR c.name LIKE ?
                ORDER BY
                    ur.created DESC";
        
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
    
    // read UndergradResearchItems with pagination
    public function readPaging( $from_record_num, $records_per_page ) {
        
        // select query
        $query = "SELECT
                    c.name as category_name, ur.id, ur.researchers, ur.research_abstract, 
                    ur.research_pathname, ur.category_id, ur.created
                FROM
                    " . $this->table_name . " ur
                    LEFT JOIN
                        categories c
                            ON ur.category_id = c.id
                ORDER BY ur.created DESC
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
    
    // used for paging UndergradResearchItems
    public function count() {
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . "";
        
        $stmt = $this->db_conn->prepare( $query );
        $stmt->execute();
        $row = $stmt->fetch( PDO::FETCH_ASSOC );
        
        return $row[ 'total_rows' ];
    }
} // class UndergradResearchItem
?>
