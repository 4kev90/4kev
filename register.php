<?php

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();

//prepare variables to insert into table
$name = mysqli_real_escape_string($con, $_POST['name']);
$email = mysqli_real_escape_string($con, $_POST['email']);
$pwd = mysqli_real_escape_string($con, $_POST['pwd']);
$pwd2 = mysqli_real_escape_string($con, $_POST['pwd2']);
$cle = makePwd();
$msg = 'http://4kev.org/activation.php?cle='.urlencode($cle);

//check if password and confirm password fields correspond
if($pwd != $pwd2) {
    header("Location: index.php?err=5");
    die;
}

//check if password is too short
if(strlen($pwd) < 8) {
    header("Location: index.php?err=6");
    die;
}


//check if name or email is already taken
$aa = "SELECT * FROM users";
$bb = (mysqli_query($con, $aa) );
while($row = mysqli_fetch_assoc( $bb )) {
    if($name == $row['name']) {
        header("Location: index.php?err=2");
        die;
    }
    if($email == $row['email']) {
        header("Location: index.php?err=3");
        die;
    }
}

//insert data into table
if($name && $email && $pwd) {
    $hash = password_hash($pwd, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, hash, cle, confirmed) VALUES ('$name', '$email', '$hash', '$cle', '0')";
    mysqli_query($con, $sql);
    // send email
    mail($email,"4kev.org registration",$msg);

    header("Location: index.php?err=1");
    die;
}

?>




