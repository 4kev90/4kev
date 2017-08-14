<?php

session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();

//get thread number
if($_GET['op']) {
    $op = $_GET['op'];
    $sql = $con->prepare("SELECT * FROM posts WHERE ID = ?");
    $sql->bind_param('i', $op);
    if($sql->execute()) {
        $result = $sql->get_result();
        while ($row = $result->fetch_assoc()) {
            if(!$row['replyTo'])
                $threadExists = 1;
        }
        if($threadExists != 1)
            header('Location: http://4kev.org');
    }
}
else
    header('Location: http://4kev.org');

//get name of the board
$aa = "SELECT * FROM posts WHERE ID = $op";
$bb = (mysqli_query($con, $aa) );
        while($row = mysqli_fetch_assoc( $bb ))
            $boardName = $row['board'];

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
$comm = mysqli_real_escape_string($con, $_POST['comment']);
date_default_timezone_set('Europe/Paris');
$date = date('d/m/Y H:i:s', time());
$ipAddr = $_SERVER['REMOTE_ADDR'];
$image = basename($_FILES["fileToUpload"]["name"]);

//if someone tries to post a malicious link, redirect him
if (strpos($comm, 'href') !== false) {
    header('Location: ' . $_SERVER['PHP_SELF'] . '?op=' . $op);
    die;
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

//check if bump limit is reached
$x = "SELECT COUNT(*) AS replies FROM posts WHERE replyTo = $op";
$y = (mysqli_query($con, $x));
$z = mysqli_fetch_assoc($y);
$q = $z['replies'];
$bumpLimit = 250;
if($q >= $bumpLimit)
    echo "<p><b>Bump limit exceeded. Can't post more replies.</b></p><br>";

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

//insert data into table
if(($comm || $image) && ($q < $bumpLimit)) {

    //variables concerning image upload
    $selectSQL = "SELECT ID FROM posts ORDER BY ID DESC LIMIT 1;";
    $result = (mysqli_query($con, $selectSQL) );
    while($row = mysqli_fetch_assoc( $result ))
        $imageBaseName = $row['ID'];
        $imageBaseName += 1;
    $oldName = basename($_FILES["fileToUpload"]["name"]);
    $imageFileType = pathinfo($oldName,PATHINFO_EXTENSION);
    if($oldName)
        $newName = $imageBaseName . "." . $imageFileType;
    $target_dir = "uploads/";
    $target_file = $target_dir . $newName;
    $uploadOk = 1;

    if($oldName) {
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
        if ($_FILES["fileToUpload"]["size"] > 2000000) {
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
                //echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
            } else {
                //echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    if($uploadOk == 1) {
        $sql = "INSERT INTO posts (name, options, commento, dateTime, replyTo, ipAddress, board, image, loggedIn, isMod) VALUES ('$name', '$options', '$comm', '$date', $op, '$ipAddr', '$boardName', '$newName', '$loggedIn', '$isMod')";
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

//redirect to same page
header('Location: ' . $_SERVER['PHP_SELF'] . '?op=' . $op . '#' . $newBump);
die;
}

?>

<HTML>
<head>
<title><?php echo 'Thread ' . $op; ?></title>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<?php 
    if($_COOKIE["style"]) 
        $style = $_COOKIE["style"];
    else
        $style = 'cyber';
    echo '<link rel="stylesheet" type="text/css" href="themes/' . $style . '.css?v=' . time() . '">'; 
?>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="myjs.js?v=<?=time();?>"></script>
</head>

<div class="bgImage">

    <?php //print a message if a post has been reported
        if($_POST['report'])
            echo "<script> alert('Reported'); </script>";
    ?>

    <?php boardList($con); ?>

    <!--BANNER-->
    <?php banner(); ?>

    <br><br>
    <div class="boardName">
    <p style="font-size:30px"><b><? echo $boardName; ?></b></p>
    <?php echo $top_message; ?>
    </div>
    <br><br>

    <!--LOGIN BAR-->
    <?php loginBar($con, $op); ?>

    <!--POST REPLY BUTTON-->
    <button id="showForm" style="text-align:center; height:30px;" onclick="showForm()">Post a Reply</button>

    <!--submission form-->
    <div class="form" id="form" style="display:none">
        <form style='display:inline;' action="#" method="post" enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">   
            <?php
                if(isset($_SESSION['ID'])) {
                    $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
                    $res = mysqli_query($con, $sql);
                        while($row = mysqli_fetch_assoc( $res ))
                            echo '<strong><p class="userName">' . $row['name'] . "</p></strong>";
                }
                else
                    echo '<textarea rows="1" cols="30" input type="text" name="name" />' . $_COOKIE["keepName"] . '</textarea><br>';
            ?>
            <textarea style="width:300px;" rows="1" cols="30" input type="text" name="options" /><?php echo $_COOKIE['keepOptions']; ?></textarea><br>
            <input style="width:300px;" type="file" name="fileToUpload" id="fileToUpload"><br>
            <textarea style="width:300px; resize:both;" rows="4" cols="40" input type="text" name="comment" /></textarea><br>
            <button style="text-align:center; height:30px; width:300px" type="submit" value="Post" name="myButton">Post</button>
        </form>
    </div>
    <br><hr>
</div>

<!--reply window-->
<div id="draggable" class='replyWindow'>
<p style="cursor:move; text-align:center;"><strong>Post a reply</strong><span class='close'>&times;</span></p>
<form style='display:inline;' action='#' method='post' enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">
<?php
    if(isset($_SESSION['ID'])) {
        $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
        $res = mysqli_query($con, $sql);
            while($row = mysqli_fetch_assoc( $res ))
                echo "<strong><p style='text-align:center;' class='userName'>" . $row['name'] . "</p></strong>";
    }
    else
        echo '<textarea placeholder="Name" rows="1" style="width: 300px" input type="text" name="name" />' . $_COOKIE["keepName"] . '</textarea><br>';
?>
<textarea placeholder="Options" rows="1" style="width: 300px" input type="text" name="options" /><?php echo $_COOKIE['keepOptions']; ?></textarea><br>
<input type="file" style="display:inline" name="fileToUpload" id="fileToUpload"><br>
<textarea id="linky" rows='4' style="width: 300px; resize:both;" input type='text' name='comment'></textarea><br>
<button style="text-align:center; height:30px; width:300px" type="submit" name="myButton">Post</button>
</form></div>

<!--post preview-->
<div class="preview post">
</div>

<?php
//display posts
$selectSQL = "SELECT * FROM posts ORDER BY ID ASC";
$selectRes = mysqli_query($con, $selectSQL);

while( $row = mysqli_fetch_assoc( $selectRes ) ){

    //prepare variables
    $rowImage   = "../uploads/" . htmlspecialchars($row['image']);
    $rowImage   = str_replace("onerror","whatnow", $rowImage);  //protection against xss attack
    $imageID    = 'img' . $row['ID'];
    $rowID      = $row['ID'];
    $rowName    = htmlspecialchars($row['name']);
    $rowSubject = htmlspecialchars($row['subject']);
    $rowComment = htmlspecialchars($row['commento']);

    $space = str_repeat('&nbsp;', 2);  //spaces between picture and text
     
    if($row['ID'] == $op || $row['replyTo'] == $op) {

        echo "<div class='post' id='{$rowID}'>";

        //show picture if present
        if($row['image'])
            echo "<img style='float:left;' class='pic' id=$imageID src=$rowImage onclick='resizepic(this.id)'>";

            //PRINT POST INFO
            echo "<form action='#' method='post' style='vertical-align:top; display: inline-block';>";
            echo "<p style='padding-left:10px;'>";

            //print subject
            echo "<strong><span class='subject'>{$rowSubject}</span></strong>";

            //print user logo
            if($row['isMod'] == 1)
                echo " <span style='cursor:pointer;' title='Admin' class='adminLogo'>☯</span> ";
            else if($row['isMod'] == 2)
                echo " <span style='cursor:pointer;' title='Mod' class='modLogo'>☯</span> ";
            else if($row['loggedIn'] == 1)
                echo " <span style='cursor:pointer;' title='Registered User' class='userLogo'>&#9733</span> ";

            //print name
            echo "<span class='userName'><strong> ";

            if(!$row['name'])
                echo("Anonymous");

            //print link to user profile is name is registered
            if($row['loggedIn'] == 1)
                echo nl2br("<a href='users.php?user=$rowName'>$rowName</a>");
            else
                echo nl2br("$rowName");

            echo "</strong></span>";

            //print date and time
            echo "<span class='info'> {$row['dateTime']}</span>";

            //print post number
            echo " <a class='quickReply'>{$row['ID']}</a>";

            //print blue arrow
            $hiddenButton = (string)$row['ID'] . 'btn';
            echo " <a class='blue' onclick='showButton(\"$hiddenButton\")'>▶</a>";

            //show delete button if user is a mod, else show report button
            if($isMod)
                echo " <button id='$hiddenButton' style='display:none;' type='submit' name='delete' value='{$row['ID']}'>Delete</button>";
            else
                echo " <button id='$hiddenButton' style='display:none;' type='submit' name='report' value='{$row['ID']}'>Report</button>";

            //check if post is banned and echo message
            $sql2 = "SELECT * FROM bannedPosts";
            $res2 = mysqli_query($con, $sql2);
            while($row2 = mysqli_fetch_assoc($res2))
                if($row['ID'] == $row2['post']) {
                    echo "<span style='color:red'><strong>(User was banned for this post)</strong></span>";
                    break;
                } 

            echo "<br><br>";

            //PRINT COMMENT
        //divide comment into lines
        $lines = explode("\n", $rowComment);

        //apply redtext
        foreach ($lines as $line) {
            //check for redtext
            $checkRed = htmlspecialchars_decode($line);
            if($checkRed[0] == '>')
                echo nl2br("<span class='redtext'>");
            else 
                echo nl2br("<span>");
    
            //divide line into words
            $words = explode(" ", $line);
            foreach ($words as $word) {

            $word = checkYoutube($word);
            $word = wordFilter($word);
    
                //if word is a link to a post, show post preview
                $checkLink = htmlspecialchars_decode($word);
                if($checkLink[0] == '>' && $checkLink[1] == '>') {
                    $postLink =  preg_replace("/[^0-9]/","", basename($word)); 
                    $sql="SELECT * FROM posts WHERE ID = $postLink";
                    $res = mysqli_query($con, $sql);
                    while($row = mysqli_fetch_assoc( $res )) 
                        $linkComm = htmlspecialchars(addslashes($row['commento']));
                    $linkComm = htmlspecialchars(preg_replace("/\r\n|\r|\n/",'<br/>',$linkComm));
                    echo nl2br("<A style='text-decoration: underline;' href='#$postLink' id={$postLink} class='postlink'>{$word}</A> ");
                }
               
                //print original word
                else
                    echo "$word ";
            }
        echo nl2br("</span>");
        }
    echo "</p></form></div><br>";
    }
}

?>
<br>
</body>
</html>


