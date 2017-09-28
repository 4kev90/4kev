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

//get page
if($_GET['page']) 
    $page = $_GET['page'];
else
    $page = 1;

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
if($options == 'fortune')
    $fortune = rand(0,12);

//you must wait 2 minutes before starting a new thread
if($comm) {
    $sql = 'SELECT * FROM posts WHERE ipAddress = "'.$ipAddr.'" ORDER BY ID DESC LIMIT 1';
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res)) {
        $lastThread = $row['dateTime'];

        if(compareDates($lastThread, $date) < 120) {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?board=' . $boardName . '&message='.compareDates($lastThread, $date));
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
                //create thumbnail
                $image = new Imagick();
                $image_filehandle = fopen('uploads/' . $newName, 'a+');
                $image->readImageFile($image_filehandle);

                $height = $image->getImageHeight();
                $width  = $image->getImageWidth();

                if($height >= $width) {
                    $width *= 250 / $height;
                    $height = 250;
                }
                else {
                    $height *= 250 / $width;
                    $width = 250;
                }

                $image->scaleImage($width,$height,FALSE);

                $image_icon_filehandle = fopen('thumbnails/' . $newName, 'w');
                if($image->writeImageFile($image_icon_filehandle)) {}
    
                //echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
            } else {
                //echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    if($uploadOk == 1) {
        $sql = "INSERT INTO posts (name, options, subject, commento, dateTime, ipAddress, bump, board, image, loggedIn, isMod, fortune) VALUES ('$name', '$options', '$subj', '$comm', '$date', '$ipAddr', '$newBump', '$boardName', '$newName', '$loggedIn', '$isMod', '$fortune')";
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
        $style = $defaultTheme;
    echo '<link rel="stylesheet" type="text/css" href="/themes/' . $style . '.css?v=' . time() . '">'; 
?>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="/myjs.js?v=<?=time();?>" ></script>

</head>
<body>

<?php loginForm($con, $boardName); ?>

<div class="bgImage">

    <?php

        //print a message if a post has been reported
        if($_POST['report'])
            echo "<script> alert('Reported'); </script>";

        //you must wait 2 minutes before posting a new thread
        if($_GET['message'])
            echo '<script> alert("You must wait two minutes before starting a new thread."); </script>';
    ?>

    <?php boardList($con, $boardName); ?>

    <br>
    <div id="boardName">
    <!--BANNER-->
    <?php banner(); ?>
    <p style="font-size:30px"><strong><?php echo ucfirst($boardName); ?></strong></p>
    <?php echo $top_message; ?>
    </div>
    <br><br>

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
                        echo "<strong><p class='userName'>" . $row['name'] . "</p></strong>";
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
    if($cont < 150) {
        if(($row['replyTo'] == 0) && ($row['board'] == $boardName)) {
            $cont = $cont+1; 
            if($cont > (($page-1)*15) && $cont <= ($page*15)) { 
            
            //prepare variables
            $rowImage = "/thumbnails/" . htmlspecialchars($row['image']);
            $rowImage = str_replace("onerror","whatnow", $rowImage);  //protection against xss attack
            $imageID = 'img' . $row['ID'];
            $rowName = htmlspecialchars($row['name']);
            $rowSubject = htmlspecialchars($row['subject']);
            $rowComment = htmlspecialchars($row['commento']);
            $id = $row['ID'];
            $space = str_repeat('&nbsp;', 10);

            //get number of replies in the thread
            $x = "SELECT COUNT(*) AS replies FROM posts WHERE replyTo = $id";
            $y = (mysqli_query($con, $x));
            $z = mysqli_fetch_assoc($y);
            $q = $z['replies'];

            //display posts
            echo '<div class="post op">';

            //show picture if present
            if($row['image'])
                echo "<img style='float:left;' class='thumbnail' id=$imageID src=$rowImage onclick='resizepic(this.id)'>";
            
            //PRINT POST INFO
            echo "<form action='#' method='post' style='vertical-align:top; display: inline-block';>";
            echo "<p style='padding-left:10px; padding-right:10px;'>";

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
/*
            //print link to user profile is name is registered
            if($row['loggedIn'] == 1)
                echo nl2br("<a href='https://www.4kev.org/users.php?user=$rowName'>$rowName</a>");
            else */
                echo nl2br("$rowName");

            echo "</strong></span>";

            //print date and time
            echo "<span class='info'> {$row['dateTime']}";

            //print post number, number of replies and link to thread
            echo " No.{$row['ID']}</span>";

            //print link to thread
            echo " [<a href=/threads.php?op=".$id.">Reply</a>]";

            //print blue arrow
            $hiddenButton = (string)$row['ID'] . 'btn';
            echo " <a class='arrow' onclick='showButton(\"$hiddenButton\")'>▶</a>";

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

            //fortune
            if($row['fortune']) {
                fortune($row['fortune']);
                echo "<br><br>";
            }

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
            echo '</p></form></div>';

            // THREAD EXPANSION
            echo '<p class="info"><a title="Expand thread" id="expandButton'.$id.'" class="arrow" onclick="expand('.$id.')">▼</a> ';

            //print number of replies
            echo $q . ' replies';

            echo '</p>';

            //#################################################################################################################

            //PRINT LAST REPLIES
            echo '<div id="replies'.$id.'">';
            $sqlReplies = "(SELECT * FROM posts WHERE replyTo = " . $row['ID'] . " ORDER BY ID DESC LIMIT 3) ORDER BY ID ASC;";
            $resReplies = mysqli_query($con, $sqlReplies);
            while($rowReplies = mysqli_fetch_assoc( $resReplies )) {
                //prepare variables
                $rowImage = "/thumbnails/" . htmlspecialchars($rowReplies['image']);
                $imageID = 'img' . $rowReplies['ID'];
                $rowName = htmlspecialchars($rowReplies['name']);
                $rowComment = htmlspecialchars($rowReplies['commento']);
                $id = $rowReplies['ID'];
                $space = str_repeat('&nbsp;', 2);  //spaces between picture and text

                //display posts
                echo '<div class="post">';

                //show picture if present
                if($rowReplies['image'])
                    echo "<img style='float:left;' class='thumbnail' id=$imageID src=$rowImage onclick='resizepic(this.id)'>";

                //PRINT POST INFO
                echo "<form action='#' method='post' style='vertical-align:top; display: inline-block';>";
                echo "<p style='padding-left:10px; padding-right:10px;'>";

                //print user logo
                if($rowReplies['isMod'] == 1)
                    echo " <span style='cursor:pointer;' title='Admin' class='adminLogo'>☯</span> ";
                else if($rowReplies['isMod'] == 2)
                    echo " <span style='cursor:pointer;' title='Mod' class='modLogo'>☯</span> ";
                else if($rowReplies['loggedIn'] == 1)
                    echo " <span style='cursor:pointer;' title='Registered User' class='userLogo'>&#9733</span> ";

                //print name
                echo "<span class='userName'><strong> ";

                if(!$rowReplies['name'])
                    echo("Anonymous");

                //print link to user profile is name is registered
                if($rowReplies['loggedIn'] == 1)
                    echo nl2br("<a href='/users.php?user=$rowName'>$rowName</a>");
                else
                    echo nl2br("$rowName");

                echo "</strong></span>";

                //print date and time
                echo "<span class='info'> {$rowReplies['dateTime']}";

                //print post number
                echo " No.{$rowReplies['ID']}</span>";

                //print blue arrow
                $hiddenButton = (string)$rowReplies['ID'] . 'btn';
                echo " <a class='arrow' onclick='showButton(\"$hiddenButton\")'>▶</a>";

                //show delete button if user is a mod, else show report button
                if($isMod)
                    echo " <button id='$hiddenButton' style='display:none;' type='submit' name='delete' value='{$rowReplies['ID']}'>Delete</button>";
                else
                    echo " <button id='$hiddenButton' style='display:none;' type='submit' name='report' value='{$rowReplies['ID']}'>Report</button>";



                //check if post is banned and echo message
                $sql2 = "SELECT * FROM bannedPosts";
                $res2 = mysqli_query($con, $sql2);
                while($row2 = mysqli_fetch_assoc($res2))
                    if($rowReplies['ID'] == $row2['post']) {
                        echo "<span style='color:red'><strong>(User was banned for this post)</strong></span>";
                        break;
                    } 

                echo "<br><br>";

                //fortune
                if($rowReplies['fortune']) {
                    fortune($rowReplies['fortune']);
                    echo "<br><br>";
                }

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
            echo '</div>';
            echo '<hr>';
        }
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

//count total threads
$sql = "SELECT * FROM posts WHERE board = '{$boardName}' AND bump;";
$res = mysqli_query($con, $sql);
$threads = 0;
while($row = mysqli_fetch_assoc($res)) {
    $threads++;
}
//print links to pages
$pages = ceil($threads / 15);

echo '<p style="text-align:center">';

for($i = 1; $i <= $pages; $i++) {
    //$link = 'boards/' . $boardName . '/' . $i;
    $link = $_SERVER['PHP_SELF'] . '?board=' . $boardName . '&page=' . $i;
    echo '<a href="' . $link . '"><button class="pageButton">' . $i . '</button></a> ';
}



echo '</p><br>';



?>
</body>
</html>