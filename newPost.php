<?php

session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();

//get board name or thread number
if($_GET['board']) {
    $boardName = $_GET['board'];
    $sql='SELECT boardName FROM boards';
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res))
        if($boardName == $row['boardName'])
            $boardExists = true;
    if(!$boardExists) {
        header('Location: http://4kev.org');
        die();
    }
    
    //get page
    if($_GET['page'])
        $page = $_GET['page'];
    else
        $page = 1;
}
else if($_GET['op']) {
    $op = $_GET['op'];
    $sql = $con->prepare("SELECT * FROM posts WHERE ID = ?");
    $sql->bind_param('i', $op);
    if($sql->execute()) {
        $result = $sql->get_result();
        while ($row = $result->fetch_assoc()) {
            if(!$row['replyTo'])
                $threadExists = 1;
        }
        if($threadExists != 1) {
            header('Location: http://4kev.org');
            die();
        }
    }
  
    //get name of the board
    $aa = "SELECT * FROM posts WHERE ID = $op";
    $bb = (mysqli_query($con, $aa) );
        while($row = mysqli_fetch_assoc( $bb ))
            $boardName = $row['board'];
}
else
    header('Location: http://4kev.org');



//check if user is a mod
$sessionID = $_SESSION['ID'];
$sql = "SELECT * FROM users WHERE ID = $sessionID";
$res = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($res)) {
    if($row['isMod'] == 1)
        $isMod = 1;
    else if($row['isMod'] == 2)
        $isMod = 2;
}

//prepare variables to insert
//retrieve username
if(isset($_SESSION['ID'])) {
    $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
    $res = mysqli_query($con, $sql);
        while($row = mysqli_fetch_assoc( $res ))
            $name = mysqli_real_escape_string($con, $row['name']);
    }
else {
    $name = mysqli_real_escape_string($con, $_POST['name']);
}
$options = mysqli_real_escape_string($con, $_POST['options']);
$subj = mysqli_real_escape_string($con, $_POST['subject']);
$comm = mysqli_real_escape_string($con, $_POST['comment']);
date_default_timezone_set('Europe/Paris');
$date = date('d/m/Y H:i:s', time());
$ipAddr = $_SERVER['REMOTE_ADDR'];
$url = mysqli_real_escape_string($con, $_POST['url']);
$image = basename($_FILES["fileToUpload"]["name"]);
if($options == 'fortune')
    $fortune = rand(0,12);
  

//you must wait 2 minutes before starting a new thread
//you must wait 30 seconds before posting a new reply
if(!$isMod) {
    if($op)
      $delay = 30;
    else
      $delay = 120;

    $sql = 'SELECT * FROM posts WHERE ipAddress = "'.$ipAddr.'" ORDER BY ID DESC LIMIT 1';
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res)) {
        $lastThread = $row['dateTime'];

        if(compareDates($lastThread, $date) < $delay) {
            header('Location: ' . $_SERVER['HTTP_REFERER'] . '&message=wait');
            //header('Location: ' . $_SERVER['PHP_SELF'] . '?board=' . $boardName . '&message='.compareDates($lastThread, $date));
            die;
        }
    }
}


//delete post
if($_POST['delete'] && $isMod) {
    $postDel = $_POST['delete'];
    $postDel = str_replace("'", "", $postDel);
    $sql = "DELETE FROM posts WHERE ID = $postDel OR replyTo = $postDel";
    mysqli_query($con, $sql);
    //register action in actionMod
    $sql = "INSERT INTO actionMod (name, dateTime, ipAddress, action) VALUES ('$name','$date','$ipAddr','delete')";
    mysqli_query($con,$sql);
}

//report post
if($_POST['report']) {
    $report = $_POST['report'];
    $report = str_replace("'", "", $report);
    $sql = "INSERT INTO reports (post, ipAddress) VALUES ('$report','$ipAddr')";
    mysqli_query($con, $sql);
}

if(isset($_SESSION['ID']))
  $loggedIn = 1;

setcookie('keepName', $name, time()+3600, '/');
setcookie('keepOptions', $options, time()+3600, '/');

if(!$op) {
    //bump thread
    $selectSQL = "SELECT ID FROM posts ORDER BY ID DESC LIMIT 1;";
    $result = (mysqli_query($con, $selectSQL) );
    while($row = mysqli_fetch_assoc( $result ))
        $newBump = $row['ID'] + 1;
}

//check if user is banned
if($comm) {
    $sql = "SELECT * FROM bannedUsers ";
    $result = (mysqli_query($con, $sql));
    while($row = mysqli_fetch_assoc( $result )) {
        if($row['ipAddress'] == $ipAddr) {
            //check if ban is expired
            $actualDate = (float)date('YmdHis', time());
            if($row['expire'] >= $actualDate) {
                //if user is still banned, send him to ban page with query string with info about ban
                $reason = $row['reason'];
                $date1 = $row['date1'];
                $date2 = $row['date2'];
                header('Location: http://4kev.org/banned.php?reason=' . $reason . '&date1=' . $date1 . '&date2=' . $date2);
                die;
            }
            
        }
    }
}
  
//if the new post is a reply, upload only if bump limit is not reached
$bumpLimitOk = true;
if($op) {
  //check if bump limit is reached
  $x = "SELECT COUNT(*) AS replies FROM posts WHERE replyTo = $op";
  $y = (mysqli_query($con, $x));
  $z = mysqli_fetch_assoc($y);
  $q = $z['replies'];
  $bumpLimit = 250;
  if($q >= $bumpLimit)
    $bumpLimitOk = false;
}

//insert data into table
if(($comm || $image || $url) && $bumpLimitOk) {

//variables concerning image upload
    $selectSQL = "SELECT ID FROM posts ORDER BY ID DESC LIMIT 1;";
    $result = (mysqli_query($con, $selectSQL) );
    while($row = mysqli_fetch_assoc( $result ))
        $imageBaseName = $row['ID'];
        $imageBaseName += 1;
    $oldName = basename($_FILES["fileToUpload"]["name"]);
    $oldName = mysqli_real_escape_string($con, $oldName);
    $imageFileType = pathinfo($oldName,PATHINFO_EXTENSION);
    if($oldName)
        $newName = $imageBaseName . "." . $imageFileType;
    $target_dir = "uploads/";
    $target_file = $target_dir . $newName;
    $uploadOk = 1;

    // VIP BOARD - only logged in users are allowed to post
    if($boardName == 'vip' && !$loggedIn)
        $uploadOk = 0;

    if($oldName) {
        // pdf
        if(strtolower($imageFileType) == 'pdf') {
            // Check file size
            if ($_FILES["fileToUpload"]["size"] > 100000000) {
                echo "Maximum pdf size: 100MB";
                $uploadOk = 0;
            }
            if($uploadOk)
                move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
        }
        // WEBM & mp3
        else if($imageFileType == 'webm' || $imageFileType == 'WEBM' || $imageFileType == 'mp3' || $imageFileType == 'MP3') {
            // Check file size
            if ($_FILES["fileToUpload"]["size"] > 10000000) {
                echo "Sorry, your file is too large.";
                $uploadOk = 0;
            }
            if($uploadOk)
                move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
        }
        else {
            //insert image into database
            // Check if image file is an actual image or fake image
            if(isset($_POST["submit"])) {
                $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                if($check !== false) {
                    echo "File is an image - " . $check["mime"] . ".";
                    $uploadOk = 1;
                } else {
                    echo "File is not an image.";
                    $uploadOk = 0;
                }
            }
            // Check file size
            if ($_FILES["fileToUpload"]["size"] > 10000000) {
                echo "Sorry, your file is too large.";
                $uploadOk = 0;
            }
            // Allow certain file formats
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif"  && $imageFileType != "JPG"  && $imageFileType != "PNG"  && $imageFileType != "JPEG"  && $imageFileType != "GIF") {
                echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $uploadOk = 0;
            }
            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                echo "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                
                    //create thumbnail

                    if($imageFileType == "gif" || $imageFileType == "GIF") {

                        // File and new size
                        $filename = 'uploads/' . $newName;

                        // Content type
                        header('Content-Type: image/gif');

                        // Get new sizes
                        list($width, $height) = getimagesize($filename);
                        if($width > 170 || $height > 170) {
                            if($height >= $width) {
                                $new_width = $width * 170 / $height;
                                $new_height = 170;
                            }
                            else {
                                $new_height = $height * 170 / $width;
                                $new_width = 170;
                            }
                        }

                        // Resample
                        $image_p = imagecreatetruecolor($new_width, $new_height);
                        $image = imagecreatefromgif($filename);
                        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

                        // Output
                        imagejpeg($image_p, 'thumbnails/' . $newName, 100);
                    }

                    else {

                        $image = new Imagick();
                        $image_filehandle = fopen('uploads/' . $newName, 'a+');
                        $image->readImageFile($image_filehandle);

                        $height = $image->getImageHeight();
                        $width  = $image->getImageWidth();


                        if($width > 170 || $height > 170) {
                            if($height >= $width) {
                                $width *= 170 / $height;
                                $height = 170;
                            }
                            else {
                                $height *= 170 / $width;
                                $width = 170;
                            }
                        }

                        $image->scaleImage($width,$height,FALSE);

                        $image_icon_filehandle = fopen('thumbnails/' . $newName, 'w');
                        if($image->writeImageFile($image_icon_filehandle)) {}
                    }
    
                    //echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
                } else {
                    //echo "Sorry, there was an error uploading your file.";
                }
            }
        }
    }

    //posts must be at least x characters long (if they don't contain an image)
    if(strlen($comm) < 20 && !$image) {
        echo "Posts must be at least 20 characters long";
        $uploadOk = 0;
    }

    if($uploadOk == 1 && !$op) {
        $sql = "INSERT INTO posts (name, options, subject, commento, dateTime, ipAddress, bump, board, imageUrl, image, fileName, loggedIn, isMod, fortune) VALUES ('$name', '$options', '$subj', '$comm', '$date', '$ipAddr', '$newBump', '$boardName', '$url', '$newName', '$oldName', '$loggedIn', '$isMod', '$fortune')";
        mysqli_query($con, $sql);  
    }
    if($uploadOk == 1 && $op) {
        $sql = "INSERT INTO posts (name, options, commento, dateTime, replyTo, ipAddress, board, imageUrl, image, fileName, loggedIn, isMod, fortune) VALUES ('$name', '$options', '$comm', '$date', $op, '$ipAddr', '$boardName', '$url', '$newName', '$oldName', '$loggedIn', '$isMod', '$fortune')";
        mysqli_query($con, $sql);

        //bump thread
        if(strtolower($options) != "sage") {
            $selectSQL = "SELECT ID FROM posts ORDER BY ID DESC LIMIT 1;";
            $result = (mysqli_query($con, $selectSQL) );
            while($row = mysqli_fetch_assoc( $result ))
                $newBump = $row['ID'];
            $updateSQL = "UPDATE posts SET bump=$newBump WHERE ID=$op;";
            $result = (mysqli_query($con, $updateSQL) );
        }
    }
    
//if post is a reply, redirect to bottom of page
if($op) {
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '#pageBottom');
    die;
}

    
//redirect to same page
header('Location: ' . $_SERVER['HTTP_REFERER']);
die;
}
?>
