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



?>

<HTML>
<head>
<title><?php echo 'Thread ' . $op; ?></title>
<?php printHead(); ?>
</head>

<body>

<div class="bgImage">

    <?php

        boardList($con, $op);
        echo "<br>";
        banner();
        echo "<p id='boardName'>" . strtoupper($boardName) . "</p>";
        echo '<button id="showPostWindow" onclick="showReplyWindow()">Reply</button>';

    ?>

    <?php //print a message if a post has been reported
        if($_POST['report'])
            echo "<script> alert('Reported'); </script>";

        //you must wait 30 seconds before posting a new reply
        if($_GET['message'])
            echo '<script> alert("You must wait 30 seconds before posting a new reply."); </script>';
    ?>
<br>
<br>
<hr>
</div>

<!--hidden form to block bots-->
<form id="hiddenForm" action="https://www.4kev.org/index.php" method="post" enctype="multipart/form-data">
    <textarea placeholder="Name" input type="text" name="name" /></textarea><br>'
    <textarea placeholder="Subject" input type="text" name="subject" /></textarea><br>
    <textarea class="commentField" placeholder="Comment" input type="text" name="comment" /></textarea><br>
    <button type="submit" value="Post"></button>
</form>

<!--reply window-->
<div id='replyWindow' class="draggable">
    <button id="closeReplyWindow" class='close'>✖</button>
    <p style="cursor:move; text-align:center;"><strong>Post a reply</strong></p>
    <?php echo '<form action="/newPost.php?op='.$op.'" method="post" enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">'; ?> 
        <?php
            if(isset($_SESSION['ID'])) {
                $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
                $res = mysqli_query($con, $sql);
                    while($row = mysqli_fetch_assoc( $res ))
                        echo "<strong><p style='text-align:center;' class='userName'>" . $row['name'] . "</p></strong>";
            }
            else
                echo '<textarea name="name" placeholder="Name" type="text" /></textarea><br>';
        ?>
        <textarea class="commentField" name='comment' placeholder="Comment" id="linky" input type='text'></textarea><br>
        <textarea name="JS_enabled" class="JS_enabled" style="display:none">enabled</textarea>
        <!--<input name="fileToUpload" type="file" style="width:200px; bottom:0px; padding:0; margin:0; display: inline-block; vertical-align: middle;">-->
        <!--<input name="fileToUpload" type="file">-->
        <button name="myButton" type="submit" class="postButton">Post</button>
    </form>
</div>

<!--post preview-->
<div class="post" id="preview" style="display:none"></div>

<?php

// unique posters
echo '<p class="info">Unique posters: ';
$x = "SELECT COUNT(DISTINCT ipAddress) AS posters FROM posts WHERE replyTo = $op OR ID = $op";
$y = (mysqli_query($con, $x));
$z = mysqli_fetch_assoc($y);
$q = $z['posters'];
echo $q;
echo '</p>';

//display posts
$selectSQL = "SELECT * FROM posts ORDER BY ID ASC";
$selectRes = mysqli_query($con, $selectSQL);

while( $row = mysqli_fetch_assoc( $selectRes ) ){
    
    if($row['ID'] == $op || $row['replyTo'] == $op) 
        printPost($con, $isMod, $row);
}

?>
<br>
<!--redirect to bottom of page after reply-->
<div id="pageBottom"></div>
</body>
</html>