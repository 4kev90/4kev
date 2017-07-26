<?php

session_start();

include('functions.php');

//connect to database
$con = connect_to_database();

//get board name
if($_GET['board']) {
    $boardName = $_GET['board'];
    if( $boardName == "test"         ||
        $boardName == "technology"   ||
        $boardName == "television"   ||
        $boardName == "science"      ||
        $boardName == "philosophy"   ||
        $boardName == "international"||
        $boardName == "art"          ||
        $boardName == "literature"   ||
        $boardName == "videogames"   ||
        $boardName == "music"        ||
        $boardName == "politics"     ||
        $boardName == "fitness"      ||
        $boardName == "politics"     ||
        $boardName == "anime"        ||
        $boardName == "random"       ||
        $boardName == "feels"        ||
        $boardName == "meta"         ||
        $boardName == "cyberpunk")
        $boardExists = 1;
    if($boardExists != 1)
        header('Location: http://4kev.org');
}
else
    header('Location: http://4kev.org');

//check if user is a mod
if( $_SESSION['ID'] == x || $_SESSION['ID'] == x || $_SESSION['ID'] == x) 
    $isMod = 1;

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
if($_POST['delete'] && $isMod == 1) {
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

    if(uploadOk == 1) {
        $sql = "INSERT INTO posts (name, options, subject, commento, dateTime, ipAddress, bump, board, image, loggedIn) VALUES ('$name', '$options', '$subj', '$comm', '$date', '$ipAddr', '$newBump', '$boardName', '$newName', '$loggedIn')";
        mysqli_query($con, $sql);   
    }

//redirect to same page
header('Location: ' . $_SERVER['PHP_SELF'] . '?board=' . $boardName);
//die;
}
?>

<HTML>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="/style.css?v=<?=time();?>">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="myjs.js?v=<?=time();?>" ></script>

<div class="bgImage">

<?php //print a message if a post has been reported
    if($_POST['report'])
        echo "<script> alert('Reported'); </script>";
?>

<?php boardList(); ?>

<!--BANNER-->
<center>
<?php
$banner = "<A href = 'http://4kev.org/'><img src = 'banners/" . rand(0, 38) . ".gif' /></A>";
echo $banner;
?>

<br><br>
<table><td><center>
<p style="font-size:30px"><b><?php echo $boardName; ?></b></p>
<?php echo $top_message; ?>
</center><td></table>
<br>

<!--LOGIN BAR-->
<?php 
if(!isset($_SESSION['ID']))
    echo '<!--LOGIN BUTTON-->
    <button id="showLogin" style="width:100px; text-align:center; height:30px;" onclick="showLogin()">Login</button>
    <div id="login" style="display:none"><table>
    <form action= "../login.php?board=' . $boardName . '&x=' . $_SERVER['PHP_SELF'] . '" method="post" onsubmit="myButton.disabled = true; return true;">
    <td><p>Email</p></td><td><input type="text" name="email" /></td>
    <td><p>Password</p></td><td><input type="password" name="pwd" /></td>
    <td><button type="submit" name="myButton">Log In</button></td>
    </form></table><br></div>';
else {
    $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
        $res = mysqli_query($con, $sql);
        while($row = mysqli_fetch_assoc( $res ))
             echo "
    <table><td><p style='display:inline'>Logged in as <b>" . $row['name'] . "</b></p></td>";
    echo '
    <form action= "../logout.php?board=' . $boardName . '&x=' . $_SERVER['PHP_SELF'] . '" method="post">
    <td><button>Log Out</button></td>
    </form></table><br>';
}
?>

<!--POST THREAD BUTTON-->
<button id="showForm" style="text-align:center; height:30px;" onclick="showForm()">Start a New Thread</button>

<!--submission form-->
<div id="form" style="display:none">
<form action= "#" method="post" enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">
<table>
<tr><td><p>Name</p></td><td>
<?php
    if(isset($_SESSION['ID'])) {
        $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
        $res = mysqli_query($con, $sql);
            while($row = mysqli_fetch_assoc( $res ))
                echo "<p style='color:lawngreen'><b>" . $row['name'] . "</b></p></td></tr>";
    }
    else
        echo '<textarea rows="1" cols="30" input type="text" name="name" />' . $_COOKIE["keepName"] . '</textarea></td></tr>';
?>
    <tr><td><p>Options</p></td><td><textarea rows="1" cols="30" input type="text" name="options" /><?php echo $_COOKIE['keepOptions']; ?></textarea></td></tr>
    <tr><td ><p>Subject</p></td><td><textarea rows="1" cols="30" style="vertical-align:middle" input type="text" name="subject" /></textarea><input type="submit" value="Post" name="myButton" /></td></tr>
    <tr><td><p>Comment</p></td><td><textarea style="resize:both;" rows="4" cols="40" input type="text" name="comment" /></textarea>             </td></tr>  
    <tr><td><p>File</p></td><td><input type="file" name="fileToUpload" id="fileToUpload"></td></tr>
</table>
</form>
</div>
<hr>
</div>

</center>

<?php

//check table existance
$selectSQL = "SELECT * FROM posts ORDER BY bump DESC;";
if($selectRes = mysqli_query($con, $selectSQL)) {
    //check if rows are present in the table
    if(mysqli_num_rows($selectRes)!=0) {
    $cont = 0;
    while(  ($row = mysqli_fetch_assoc( $selectRes ))) {
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

//display post
echo nl2br("<table style='margin-bottom:5px; display:inline-table;'><tr>");

//show picture if present
if($row['image'])
    echo nl2br("<td style='vertical-align:top'><img class='smallpic' id=$imageID src=$rowImage onclick='resizepic(this.id)'></td>");

//print subject
echo nl2br("<td style='vertical-align:top'><p style='display:inline;' class='grey'><b class='yellow'>{$rowSubject}</b>");

if($row['loggedIn'] == 1)
    echo nl2br(" <font color='orange'><b style='cursor:pointer;' title='Registered User'>&#9733</b></font> ");

//select name color
if($row['options'] == 'xxxx')
    echo nl2br("<font color='red'><b> ");
else if($row['options'] == 'xxxx')
    echo nl2br("<font color='orange'><b> ");
else
    echo nl2br("<font color='lawngreen'><b> ");

//print anonymous if name is not present
if(!$row['name'])
    echo("Anonymous");

//print name, date, time, post number, number of replies and link to thread
$hiddenButton = makeFileName();
echo nl2br("$rowName</b></font> {$row['dateTime']} No.{$row['ID']} Replies:$q [<A href=thread.php?op=".$id.">Reply</A>] <a class='blue' onclick='showButton($hiddenButton)'>▶</a></p>");

//show delete button if user is a mod, else show report button
        if($isMod == 1)
            echo "  <form id='$hiddenButton' style='display:none' action='#' method='post'>
                    <button type='submit' name='delete' value='{$row['ID']}'>Delete</button>
                    </form>";
        else
            echo "  <form id='$hiddenButton' style='display:none' action='#' method='post'>
                    <button type='submit' name='report' value='{$row['ID']}'>Report</button>
                    </form>";

//check if post is banned and echo message
$sql2 = "SELECT * FROM bannedPosts";
$res2 = mysqli_query($con, $sql2);
while($row2 = mysqli_fetch_assoc($res2))
    if($row['ID'] == $row2['post']) {
        echo "<p style='color:red'><b>(User was banned for this post)</b></p>";
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
                echo nl2br("<p><font color='red'>");
            else 
                echo nl2br("<p><font>");
 
        echo $space;
    
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
                    echo "$word "; 
            }
        echo nl2br("</font></p>");
        }
echo "</td></tr></table><br><hr>";
}
        }
      else {
        if (($row['replyTo']) == 0 && ($row['board'] == $boardName)) {

        //delete thread from database
        $deleteSQL = "DELETE FROM posts WHERE replyTo = {$row['ID']} OR ID = {$row['ID']}";
        mysqli_query($con, $deleteSQL);   
      } 
    }
  }
}
}

?>

<?php boardList(); ?>

</html>