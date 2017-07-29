<?php

session_start();

$redirect = $_GET['x'];
$redirect2 = $_GET['op'];
$redirect3 = $_GET['board'];

include('functions.php');
include('connectToDatabase.php');
 
//connect to database
$con = connect_to_database();
 
//prepare variables to insert into table
$email = mysqli_real_escape_string($con, $_POST['email']);
$pwd = mysqli_real_escape_string($con, $_POST['pwd']);
 

 
//search user in database
if($email && $pwd) {
    $sql = "SELECT * FROM users WHERE email = '$email' AND pwd = '$pwd' AND confirmed = 1;";
    $res = mysqli_query($con, $sql);
    if(!$row = mysqli_fetch_assoc( $res )) {
        echo "<script> alert('Invalid email or password'); </script>";
        
    }
    else {
        $_SESSION['ID'] = $row['ID'];
    }
}
header("Location: $redirect" . "?op=" . $redirect2 . "&board=" . $redirect3);
die;
?>