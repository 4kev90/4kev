<?php

session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();

//get board name
if($_GET['board']) {
    $boardName = $_GET['board'];
    $sql='SELECT boardName FROM boards';
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res))
        if($boardName == $row['boardName'])
            $boardExists = true;
    if(!$boardExists)
        header('Location: http://4kev.org');
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

//prepare variables to insert into table
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
$ipAddr = $_SERVER['REMOTE_ADDR'];
date_default_timezone_set('Europe/Paris');
$date = date('d/m/Y H:i:s', time());
$image = basename($_FILES["fileToUpload"]["name"]);

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

//if someone tries to post a malicious link, redirect him
if (strpos($comm, 'href') !== false) {
    header('Location: '.$_SERVER['PHP_SELF']);
    die;
}

if(isset($_SESSION['ID'])) 
  $loggedIn = 1;

setcookie('keepName', $name, time()+3600, '/');
setcookie('keepOptions', $options, time()+3600, '/');

//bump thread
$selectSQL = "SELECT ID FROM posts ORDER BY ID DESC LIMIT 1;";
$result = (mysqli_query($con, $selectSQL) );
while($row = mysqli_fetch_assoc( $result )) 
    $newBump = $row['ID'] + 1;

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
if($comm) {

//variables concerning image upload

    $oldName = basename($_FILES["fileToUpload"]["name"]);
    $imageFileType = pathinfo($oldName,PATHINFO_EXTENSION);
    if($oldName)
        $newName = $newBump . "." . $imageFileType;
    $target_dir = "uploads/";
    $target_file = $target_dir . $newName;
    $uploadOk = 1;


    if($oldName) {
        //insert image into database
        // Check if image file is a actual image or fake image
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
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
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
        $sql = "INSERT INTO posts (name, options, subject, commento, dateTime, ipAddress, bump, board, image, loggedIn, isMod) VALUES ('$name', '$options', '$subj', '$comm', '$date', '$ipAddr', '$newBump', '$boardName', '$newName', '$loggedIn', '$isMod')";
        mysqli_query($con, $sql);  
    } 
    

//redirect to same page
header('Location: ' . $_SERVER['PHP_SELF'] . '?board=' . $boardName);
//die;
}
?>

<HTML>
<head>
<title><?php echo $boardName; ?></title>
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
<script type="text/javascript" src="myjs.js?v=<?=time();?>" ></script>
</head>
<body>

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
    <p style="font-size:30px"><strong><?php echo $boardName; ?></strong></p>
    <?php echo $top_message; ?>
    </div>
    <br><br>

    <!--LOGIN BAR-->
    <?php loginBar($con, $boardName); ?>

    <!--POST THREAD BUTTON-->
    <button id="showForm" style="text-align:center; height:30px;" onclick="showForm()">Start a New Thread</button>

    <!--submission form-->
    <div class="form" id="form" style="display:none; margin: 0 auto;">
        <form style='display:inline;' action= "#" method="post" enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">
        
            <?php
            if(isset($_SESSION['ID'])) {
                $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
                $res = mysqli_query($con, $sql);
                    while($row = mysqli_fetch_assoc( $res ))
                        echo "<p class='userName'><strong>" . $row['name'] . "</strong></p>";
            }
            else
                echo '<textarea style="width:300px;" placeholder="name" rows="1" cols="30" input type="text" name="name" />' . $_COOKIE["keepName"] . '</textarea><br>';
            ?>
            <textarea style="width:300px;" placeholder="Options" rows="1" cols="30" input type="text" name="options" /><?php echo $_COOKIE['keepOptions']; ?></textarea><br>
            <textarea style="width:300px;" placeholder="Subject" rows="1" cols="30" input type="text" name="subject" /></textarea><br>
            <input style="width:300px;" type="file" name="fileToUpload" id="fileToUpload"><br>
            <textarea placeholder="Comment" style="resize:both; width:300px;" rows="4" cols="40" input type="text" name="comment" /></textarea><br>
            <button style="text-align:center; height:30px; width:300px" type="submit" value="Post" name="myButton">Post</button>
            
        
        </form>
    </div>
    <br><hr>
</div>

<?php

$selectSQL = "SELECT * FROM posts ORDER BY bump DESC;";
$selectRes = mysqli_query($con, $selectSQL);
$cont = 0;
while($row = mysqli_fetch_assoc( $selectRes )) {
    //if counter is less than the maximum allowed threads
    if($cont < 50) {
        if(($row['replyTo'] == 0) && ($row['board'] == $boardName)) {
            $cont = $cont+1;  
            //prepare variables
            $rowImage = "uploads/" . htmlspecialchars($row['image']);
            $rowImage = str_replace("onerror","whatnow", $rowImage);  //protection against xss attack
            $imageID = 'img' . $row['ID'];
            $rowName = htmlspecialchars($row['name']);
            $rowSubject = htmlspecialchars($row['subject']);
            $rowComment = htmlspecialchars($row['commento']);
            $id = $row['ID'];
            $space = str_repeat('&nbsp;', 2);  //spaces between picture and text

            //get number of replies in the thread
            $x = "SELECT COUNT(*) AS replies FROM posts WHERE replyTo = $id";
            $y = (mysqli_query($con, $x));
            $z = mysqli_fetch_assoc($y);
            $q = $z['replies'];

            //display posts
            echo '<div class="post">';

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
            echo "<span class='info'> {$row['dateTime']}";

            //print post number, number of replies and link to thread
            echo " No.{$row['ID']}";

            //print number of replies
            echo " Replies:$q</span>";

            //print link to thread
            echo " [<a href=threads.php?op=".$id.">Reply</a>]";

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
            //echo "<p style='vertical-align:top; display: inline-block'>";
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
                        echo nl2br("<A onMouseOver=\"post_preview('$linkComm')\" onMouseOut='hide_preview()'>{$word} </A> ");
                    }
                   
                    //print original word
                    else
                        echo nl2br("$word "); 
                }
                echo nl2br("</span>");
            }
            echo '</p></form></div><br>';
        }
    }
    else if (($row['replyTo']) == 0 && ($row['board'] == $boardName)) {

        //delete thread from database
        $deleteSQL = "DELETE FROM posts WHERE replyTo = {$row['ID']} OR ID = {$row['ID']}";
        mysqli_query($con, $deleteSQL);  

        //delete old images
        unlink('uploads/' . $row['image']); 
    }
}

?>
<br>
</body>
</html>