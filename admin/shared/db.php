<?php
    require_once(dirname(__FILE__) . '/../config.php');

    function getConnection() {        
        $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($con) {
            $con->set_charset("utf8mb4");
        }
        if (mysqli_connect_errno()) {
            exit('Failed to connect to MySQL: ' . mysqli_connect_error());
        }

        return $con;
    }

    function checkDatabaseConnection() {
        try {
            $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($con) {
                $con->set_charset("utf8mb4");
                mysqli_close($con);
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log('Database connection check failed: ' . $e->getMessage());
            return false;
        }
    }

    function getConnectionSafe() {
        try {
            $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($con) {
                $con->set_charset("utf8mb4");
            }
            return $con; // Returns false if connection failed
        } catch (Exception $e) {
            error_log('Safe database connection failed: ' . $e->getMessage());
            return false;
        }
    }
?>