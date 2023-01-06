<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/workouts/admin/config.php');

    function getConnection() {        
        $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $con->set_charset("utf8mb4");
        if (mysqli_connect_errno()) {
            exit('Failed to connect to MySQL: ' . mysqli_connect_error());
        }

        return $con;
    }
?>