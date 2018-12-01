<?php

// include error file
require_once "../../config/my_error.php";

class Account {
    
    // database connection and table name
    private $db_conn;
    private $table_name = "accounts";
    
    // object properties
    public $id;
    public $user_name;
    public $password_hashed;
    public $created;
    
    // constructor with supplied database-connection
    public function __construct( $db_conn ) {
        $this->db_conn = $db_conn;
    }
    
    
    // methods:-
    // -------
    
    // verify Account
    function verify( $password ) {
        
        // select password (by user) query
        $query = "SELECT
                    id, password_hashed
                FROM
                    " . $this->table_name . "
                WHERE
                    ( user_name = :user_name )
                LIMIT
                    0,1";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // sanitize
        $this->user_name = htmlspecialchars( strip_tags( $this->user_name ) );
        
        // bind values
        $stmt->bindParam( ":user_name", $this->user_name );
        
        // execute query
        $stmt->execute();
        
        // get retrieved row
        $row = $stmt->fetch( PDO::FETCH_ASSOC );
        
        // verify that account at least exists
        if ( $row[ 'id' ] == null ) {
            return false;
        }
        
        // set values to object properties
        $this->id = $row[ 'id' ];
        $this->password_hashed = $row[ 'password_hashed' ];
        
        // verify password
        if ( password_verify( $password, $this->password_hashed ) ) {
            // password matched
            return true;
        }
        
        return false;
    }
    
    // read all Accounts
    function read() {
        
        // select all query
        $query = "SELECT
                    id, user_name, password_hashed, created
                FROM
                    " . $this->table_name . "
                ORDER BY
                   created DESC";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // create Account
    function create( $password ) {
        global $k_MyErr_DuplicateItem, $k_MyErr_IllegalPassword;
        
        // query to check whether record already exists
        $pre_check_query = "SELECT * FROM " . $this->table_name . 
                           " WHERE ( user_name = :user_name )" . 
                           " LIMIT 0,1";
        
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    user_name=:user_name, password_hashed=:password_hashed, 
                    created=:created";
        
        // prepare query
        $stmt = $this->db_conn->prepare( $query );
        
        // sanitize
        $this->user_name = htmlspecialchars( strip_tags( $this->user_name ) );
        $this->created = htmlspecialchars( strip_tags( $this->created ) );
        
        // Check whether record already exists
        $pre_check = $this->db_conn->prepare( $pre_check_query );
        $pre_check->bindParam( ":user_name", $this->user_name );
        $pre_check->execute();
        $row = $pre_check->fetch( PDO::FETCH_ASSOC );
        if ( $row[ 'user_name' ] != null ) {
            // For Debug ONLY!!!
            //echo "Duplicate!<br/>";
            
            return $k_MyErr_DuplicateItem;
        }
        
        // make password's hashed edition
        $this->password_hashed = password_hash( $password, PASSWORD_DEFAULT );
        if ( is_bool( $this->password_hashed ) && ( ! $this->password_hashed ) ) {
            // password is likely too long, etc.
            return $k_MyErr_IllegalPassword;
        }
        
        // bind values
        $stmt->bindParam( ":user_name", $this->user_name );
        $stmt->bindParam( ":password_hashed", $this->password_hashed );
        $stmt->bindParam( ":created", $this->created );
        
        // For Debug ONLY!!!
        //echo "\$this->password_hashed = " . $this->password_hashed . "<br/>";
        
        // execute query
        if ( $stmt->execute() ) {
            return true;
        }
        
        return false;
    }
    
    // used when filling up the update Account form
    function readOne() {
        
        // query to read single record
        $query = "SELECT
                    id, user_name, password_hashed, created
                FROM
                    " . $this->table_name . "
                WHERE
                    id = ?
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
        $this->user_name = $row[ 'user_name' ];
        $this->password_hashed = $row[ 'password_hashed' ];
    }
    
    // update the Account
    function update( $password ) {
        global $k_MyErr_IllegalPassword;
        
        // sanitize
        $this->user_name = htmlspecialchars(strip_tags( $this->user_name ) );
        $this->id = htmlspecialchars(strip_tags( $this->id ) );
        
        // (non-)emptiness tests
        $bUserNameSupplied = ! empty( $this->user_name );
        $bPasswordSupplied = ! empty( $password );
        
        // Verify that at least one non-'id' field or parameter was supplied
        if ( ! ( $bUserNameSupplied || $bPasswordSupplied ) ) {
            return false;
        }
        
        // update query
        $query = "UPDATE
                    " . $this->table_name . "
                SET
                   " . ( $bUserNameSupplied 
                         ? ( "user_name = :user_name" . 
                             ( $bPasswordSupplied ? "," : "" ) ) 
                         : "" ) . "
                   " . ( $bPasswordSupplied ? "password_hashed = :password_hashed" : "" ) . "
                WHERE
                    id = :id";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // check whether to make password's hashed edition
        if ( $bPasswordSupplied ) {
            // make password's hashed edition
            $this->password_hashed = password_hash( $password, PASSWORD_DEFAULT );
            if ( is_bool( $this->password_hashed ) && ( ! $this->password_hashed ) ) {
                // password is likely too long, etc.
                return $k_MyErr_IllegalPassword;
            }
        }
        
        // bind new values
        if ( $bUserNameSupplied ) { $stmt->bindParam(':user_name', $this->user_name ); }
        if ( $bPasswordSupplied ) { $stmt->bindParam(':password_hashed', $this->password_hashed ); }
        $stmt->bindParam( ':id', $this->id );
        
        // execute the query
        if ( $stmt->execute() ) {
            return true;
        }
        
        return false;
    }
    
    // delete the Account
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
    
    // search Accounts
    function search( $keywords ) {
     
        // select all query
        $query = "SELECT
                    id, user_name, password_hashed, created
                FROM
                    " . $this->table_name . "
                WHERE
                    user_name LIKE ?
                ORDER BY
                    created DESC";
        
        // prepare query statement
        $stmt = $this->db_conn->prepare( $query );
        
        // sanitize
        $keywords = htmlspecialchars( strip_tags( $keywords ) );
        $keywords = "%{$keywords}%";
        
        // bind
        $stmt->bindParam( 1, $keywords );
        
        // execute query
        $stmt->execute();
        
        return $stmt;
    }
} // class Account
?>
