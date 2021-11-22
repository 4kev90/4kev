<?php

session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();

//check if user is a mod
$sessionID = $_SESSION['ID'];
$sql = "SELECT * FROM users WHERE ID = $sessionID";
$res = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($res)) {
    if($row['isMod'] == 1)
        $isMod = 1;
    else if($row['isMod'] == 2)
        $isMod = 2;
    else
        $isMod = 0;
}
?>


<HTML>
<head>
<?php printHead(); ?>
</head>

<body>


<div class="bgHome">

<?php boardList($con); ?>

<div class="bgHome">
    <p id="boardName"><strong>ADMIN PAGE</strong></p>

    <?php topBar($con, $boardName, $boardName); ?>

    <form action="#" method="post">
        <input  class="modInput" name="pswd"       placeholder="Password"     type="password" />
        <br>
        <input  class="modInput" name="postNumber" placeholder="Post number"  type="text" />
        <br>
        <button class="modBtn" name="action"     value="deletePost"         type="submit">Delete Post</button>
        <br>
        <button class="modBtn" name="action"     value="deleteSpam"         type="submit">Delete Spam</button>
        <br>
        <input  class="modInput" name="reason"     placeholder="Ban Reason"   type="text" />
        <br>
        <button class="modBtn" name="action"     value="permaban"           type="submit">Permaban</button>
        <br>
        <button class="modBtn" name="action"     value="ban24h"             type="submit">Ban 24h</button>
        <br>
        <button class="modBtn" name="action"     value="shutItDown"         type="submit"   style="background-color:red;">SHUT IT DOWN</button>
    </form>

<?php
echo "<p>";

//prepare variables to insert
$id = $_SESSION['ID'];
//get name of mod
$sql = "SELECT * FROM users";
$res = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($res)) {
    if($row['ID'] == $id)
        $name = $row['name'];
}
date_default_timezone_set('Europe/Paris');
$date = date('d/m/Y H:i:s', time());
$ipAddr = $_SERVER['REMOTE_ADDR'];
$pass = 'password';
$userPass = $_POST['pswd'];
$postNumber = $_POST['postNumber'];
$reason = $_POST['reason'];
$action = $_POST['action'];


//delete post
if(my_hash_equals($pass, $userPass) && $action == 'deletePost') {
    echo "<p>Post N°$postNumber deleted</p>";
    $sql = "DELETE FROM posts WHERE ID = $postNumber OR replyTo = $postNumber";
    mysqli_query($con, $sql);
    //register action in actionMod
    $sql = "INSERT INTO actionMod (name, dateTime, ipAddress, action) VALUES ('$name','$date','$ipAddr','deletePost')";
    mysqli_query($con,$sql);
}

//delete image
if(my_hash_equals($pass, $userPass) && $action == 'deleteImage') {

    $sql = "SELECT image FROM posts WHERE ID=$postNumber";
    $res = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($res);
    $del = "uploads/" . $row['image'];
        unlink($del);

    echo $row['image'] . " deleted.";

    $sql = "UPDATE posts SET image=0 WHERE ID=$postNumber";
    $res = mysqli_query($con, $sql);

    //register action in actionMod
    $sql = "INSERT INTO actionMod (name, dateTime, ipAddress, action) VALUES ('$name','$date','$ipAddr','deleteImage')";
    mysqli_query($con,$sql);
}

//delete url
if(my_hash_equals($pass, $userPass) && $action == 'deleteUrl') {

    $sql = "UPDATE posts SET imageUrl=0 WHERE ID=$postNumber";
    $res = mysqli_query($con, $sql);

    echo "url deleted.";

    //register action in actionMod
    $sql = "INSERT INTO actionMod (name, dateTime, ipAddress, action) VALUES ('$name','$date','$ipAddr','deleteUrl')";
    mysqli_query($con,$sql);
}

//delete spam
if(my_hash_equals($pass, $userPass) && $action == 'deleteSpam') {
        echo "<p>Spam deleted</p>";
        $x = "SELECT * FROM posts WHERE ID = $postNumber";
        $y = (mysqli_query($con, $x));
        while($row = mysqli_fetch_assoc($y))
            $ip = $row['ipAddress'];   
        $sql = "DELETE FROM posts WHERE ipAddress = '$ip'";
        mysqli_query($con, $sql);
        //register action in actionMod
        $sql = "INSERT INTO actionMod (name, dateTime, ipAddress, action) VALUES ('$name','$date','$ipAddr','deleteSpam')";
        mysqli_query($con,$sql);
}

//shut it down
if(my_hash_equals($pass, $userPass) && $action == 'shutItDown') {
        echo "<p>Posting disabled</p>";
        rename("newPost.php","newPostDisabled.php");
}

//ban user (PERMABAN)
if(my_hash_equals($pass, $userPass) && $action == 'permaban') {
    echo "<p>Permabanned!!!</p>";
    $expire = (float)date('YmdHis', time()) + 10000000000;
    $x = "SELECT * FROM posts WHERE ID = $postNumber";
    $y = (mysqli_query($con, $x));
    while($row = mysqli_fetch_assoc($y)) {
        $ipAddr = $row['ipAddress'];
        $sql = "INSERT INTO bannedUsers (ipAddress, reason, expire) VALUES ('$ipAddr', '$reason', '$expire')";
        mysqli_query($con, $sql);
    }
//insert post into bannedPosts
    $sql = "INSERT INTO bannedPosts (post) VALUES ('$postNumber')";
    mysqli_query($con,$sql);
//register action in actionMod
    $sql = "INSERT INTO actionMod (name, dateTime, ipAddress, action) VALUES ('$name','$date','$ipAddr','permaban')";
    mysqli_query($con,$sql);
}

//ban user (24h ban)
if(my_hash_equals($pass, $userPass) && $action == 'ban24h') {
    echo "<p>Banned 24h!!!</p>";
    $expire = (float)date("YmdHis", strtotime("+1 day"));
    //$expire = (float)date('YmdHis', time()) + 100;
    $date1 = date('d/m/Y H:i:s', time());
    $date2 = date("d/m/Y H:i:s", strtotime("+1 day"));
    $x = "SELECT * FROM posts WHERE ID = $postNumber";
    $y = (mysqli_query($con, $x));
    while($row = mysqli_fetch_assoc($y)) {
        $ipAddr = $row['ipAddress'];
        $sql = "INSERT INTO bannedUsers (ipAddress, reason, expire, date1, date2) VALUES ('$ipAddr', '$reason', '$expire', '$date1', '$date2')";
        mysqli_query($con, $sql);
    }
    //insert post into bannedPosts
    $sql = "INSERT INTO bannedPosts (post) VALUES ('$postNumber')";
    mysqli_query($con,$sql);
    //register action in actionMod
    $sql = "INSERT INTO actionMod (name, dateTime, ipAddress, action) VALUES ('$name','$date','$ipAddr','ban24h')";
    mysqli_query($con,$sql);
}

//show all users
if ($action == 'showUsers') {
    $sql = "SELECT * FROM users";
    $res = mysqli_query($con, $sql);

    while($row = mysqli_fetch_assoc($res)) {
        echo $row['ID'];
        echo " ";
        echo htmlspecialchars($row['name']);
        echo " ";
        //echo $row['email'];
        echo " ";
        //echo $row['pwd'];
        echo "<br>";
    }
}

//show all posts
if ($action == 'showPosts') {
    $sql = "SELECT * FROM posts ORDER BY ID DESC";
    $res = mysqli_query($con, $sql);

    while($row = mysqli_fetch_assoc($res)) {
        if($row['commento'])
            echo $row['ID'];
            echo "__";
            echo $row['board'];
            echo "__";
            echo $row['replyTo'];
            echo "__";
            /*
            echo $row['name'];
            echo "__";*/
            echo htmlspecialchars($row['commento']);
            echo "</br>";
    }
}

//show all banned users
if ($action == 'showBans') {
    $sql = "SELECT * FROM bannedUsers";
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res)) {
        //echo $row['ipAddress'];
        //echo " --- ";
        //echo $row['expire'];
        //echo " --- ";
        echo $row['reason'];
        echo " --- ";
        //echo $row['date1'];
        //echo " --- ";
        echo $row['date2'];
        echo "</br>";
    }
}

//show reports
if ($action == 'showReports') {
    $sql = "SELECT * FROM reports";
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res)) {

        $sql2 = "SELECT * FROM posts";
        $res2 = mysqli_query($con, $sql2);
        while($row2 = mysqli_fetch_assoc($res2)) {
            //post link to post
            if($row2['ID'] == $row['post'] && !$row2['replyTo']) {
                echo "<a href='http://4kev.org/boards.php?board={$row2['board']}'>{$row['post']}</a><br>";
            }
            if($row2['ID'] == $row['post'] && $row2['replyTo']) {
                $num = $row2['replyTo'];
                echo "<a href='http://4kev.org/threads.php?op={$row2['replyTo']}#{$row2['ID']}'>{$row['post']}</a><br>";
            }
        }
    }
}

//create sticky
if(my_hash_equals($pass, $userPass) && $action == 'createSticky') {
    $sql = "UPDATE posts SET sticky=1 WHERE ID=$postNumber AND bump IS NOT NULL";
    $res = mysqli_query($con, $sql);
    echo "sticky created";
}

//remove sticky
if(my_hash_equals($pass, $userPass) && $action == 'removeSticky') {
    $sql = "UPDATE posts SET sticky=0 WHERE ID=$postNumber";
    $res = mysqli_query($con, $sql);
    echo "sticky removed.";
}

//lift all bans
if(my_hash_equals("dropAllBans", $userPass)) {
echo "all bans lifted";
$x = "DELETE FROM bannedUsers";
mysqli_query($con, $x);
}

  
//delete all reports
if(my_hash_equals("noreports", $userPass)) {
    $sql = "DELETE FROM reports";
    mysqli_query($con, $sql);
    echo "all reports cancelled";
}

//delete posts on test board
if(my_hash_equals("deleteTest", $userPass)) {
echo "deleting posts from test board";
$x = "DELETE FROM posts WHERE board = 'test';";
mysqli_query($con, $x);
}

//unban myself
if(my_hash_equals("liftban", $userPass)) {
    echo "ban lifted";
    $ipAddr = $_SERVER['REMOTE_ADDR'];
    $x = "DELETE FROM bannedUsers WHERE ipAddress = '$ipAddr';";
    mysqli_query($con, $x);
}

//show all mod actions
if(my_hash_equals("showAction", $userPass)) {
    $sql = "SELECT * FROM actionMod";
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res)) {
        echo $row['name'];
        echo " - _ - ";
        echo $row['dateTime'];
        echo " - _ - ";
        echo $row['ipAddress'];
        echo " - _ - ";
        echo $row['action'];
        echo "</br>";

    }
}
/*
// CREATE BACKUP OF TABLE
$sql ="CREATE TABLE posts160817 AS SELECT * FROM posts";
if(mysqli_query($con, $sql))
    echo "yep";
else
    echo "nooo";
*/

/*
//drop column
$sql = "ALTER TABLE bannedUsers DROP COLUMN expire";
if(mysqli_query($con, $sql))
  echo 'success';
else
  echo 'fail';
*/

/*
//add column
$sql = "ALTER TABLE bannedUsers ADD (date1 VARCHAR(100) NOT NULL, date2 VARCHAR(100) NOT NULL);";
if(mysqli_query($con, $sql))
  echo 'success';
else
  echo 'fail';
*/

/*
$sql = 'SHOW COLUMNS FROM posts';
$res = mysqli_query($con, $sql);

while($row = mysqli_fetch_assoc($res)){
    echo $row['Field'];
    echo " ";
    echo $row['Type'];
    echo "<br>";
}
*/


echo "</p>";
?>

</div>
</HTML>