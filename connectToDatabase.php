<?php

function connect_to_database() {
    $servername = "serverName";
    $username = "userName";
    $password = "password";
    $mydb = "databaseName";
    return mysqli_connect($servername, $username, $password, $mydb);
    }

?>