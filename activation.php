<?php
    include('functions.php');

    //connect to database
    $con = connect_to_database();

    $cle = $_GET['cle'];

    $sql = "UPDATE users SET confirmed = 1 WHERE cle = '$cle';";
    $res = mysqli_query($con, $sql);

    header("Location: index.php?err=4");
?>
