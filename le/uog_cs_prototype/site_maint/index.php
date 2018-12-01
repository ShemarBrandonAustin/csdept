<?php
// include core, database and object files
require_once "../config/core.php";

// Check whether an Admin user is already logged in
if ( isset( $_COOKIE[ $auth_user_id__cookie_name ] ) ) {
    // redirect to Site Maintenance
    header( "Location: index_maint.php" );
    
} else {  
    // redirect to the Login page
    header( "Location: login/login.php" );
}
?>
