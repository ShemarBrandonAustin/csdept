<?php
// include core file
require_once "../config/core.php";

// Verify that an Admin user is logged in
if ( ! isset( $_COOKIE[ $auth_user_id__cookie_name ] ) ) {
    die( "<h3>You MUST log in to be able to perform Site Maintenance!</h3>" );
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>UoG CS Department Maintenance</title>
        <meta lang="utf-8">
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    </head>
    <body>
        <div class="header">
            <h1>Welcome to UoG CS Department Maintenance!</h1>
        </div>

        <br/>

        <div class="links">    
            <a href="category_maint/staff_maint.php">Staff Maintenance</a>
            <a href="category_maint/club_maint.php">Clubs Maintenance</a>
            <a href="category_maint/alumni_maint.php">Alumni Maintenance</a>
            <a href="category_maint/course_maint.php">Programmes & Courses Maintenance</a>
            <a href="category_maint/undergrad_research_maint.php">Undergraduate Research Maintenance</a>
            <a href="category_maint/activity_maint.php">Departmental Activities Maintenance</a>
        </div>

    </body>
</html>
