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
<title><?php echo $boardName; ?></title>
<?php printHead(); ?>
</head>
<body>

<div class="bgImage">

    <?php
        boardList($con, $boardName);
        echo "<br>";
        echo "<br>";
        echo "<p id='boardName'>" . strtoupper($boardName) . "</p>";
        echo '<a href="/boards.php?board=' . $boardName . '"><button id="catalogButton">Return</button></a>';
        echo '<button id="showPostWindow" onclick="showPostWindow()">New Thread</button>';

        //print a message if a post has been reported
        if($_POST['report'])
            echo "<script> alert('Reported'); </script>";

        //you must wait 2 minutes before posting a new thread
        if($_GET['message'])
            echo '<script> alert("You must wait longer before making a new post."); </script>';
    ?>

    <!--submission form-->
    <div id="postWindow" class="draggable">
        <button id="closePostWindow" class='close'>✖</button>
        <p style="text-align:center;"><strong>Start a new thread</strong></p>

        <?php $captcha_number = rand(1,9); ?>
        <?php echo '<form action="/newPost.php?board='.$boardName.'&captcha=' . $captcha_number . '" method="post" enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">'; ?>
        
            <?php 
            if(isset($_SESSION['ID'])) {
                $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
                $res = mysqli_query($con, $sql);
                    while($row = mysqli_fetch_assoc( $res ))
                        echo "<strong><p style='text-align:center' class='userName'>" . $row['name'] . "</p></strong>";
            }
            else
                echo '<textarea placeholder="Name" input type="text" name="name" />' . $_COOKIE["keepName"] . '</textarea><br>';
            ?>
            <textarea placeholder="Subject" input type="text" name="subject" /></textarea><br>
            <textarea class="commentField" placeholder="Comment" input type="text" name="comment" /></textarea><br>

            <div class="captcha">
                <?php 
                    echo "<img id='new_thread_captcha_image' name='captcha" . $captcha_number . "' src='/captchas/captcha" . $captcha_number . ".png'>";
                ?>
                <textarea id="new_thread_captcha_typed" name="captcha_typed" placeholder="Enter digits"></textarea>
                <p id="mistyped_captcha_message" style="display:none; color:red;">You seem to have mistyped the CAPTCHA</p>
            </div>

            <textarea name="JS_enabled" id="JS_enabled" style="display:none">enabled</textarea>
            <input name="fileToUpload" type="file" style="width:25%; float:left; bottom:0px; padding:0; margin:0; display: inline-block; vertical-align: middle;">
            <button name="myButton" type="button" id="postButton" onclick="check_captcha()"  value="Post" class="postButton" >Post</button>
        </form>
    </div>
    <br>
    <br>
</div>

<hr>

<?php

//####################   PRINT POSTS   #######################

$selectSQL = "SELECT * FROM posts ORDER BY bump DESC;";
$selectRes = mysqli_query($con, $selectSQL);
$cont = 0;
echo '<div style="text-align:center">';
while($row = mysqli_fetch_assoc( $selectRes )) {
    //if counter is less than the maximum allowed threads
    if($cont < 150) {
        if(($row['replyTo'] == 0) && ($row['board'] == $boardName)) {

            echo '<a href="threads/' . $row['ID'] . '"><div class="catalog"><p>';

            //show picture if present (URL)
            $rowImageUrl = htmlspecialchars(addslashes($row['imageUrl']));  //protection against xss attack
            $rowImageUrl = str_replace(" ","", $rowImageUrl);  //protection against xss attack
            $rowImageUrl = str_replace("onerror","whatnow", $rowImageUrl);  //protection against xss attack
            if($row['imageUrl'])
                echo "<img style='max-width:100%; max-height:150px' src=$rowImageUrl>";

            //show picture if present
            if($row['image'])
                echo '<img style="max-width:100%; max-height:150px" src="thumbnails/' . $row['image'] . '"><br>';

                //get number of replies in the thread
                $x = "SELECT COUNT(*) AS replies FROM posts WHERE replyTo = " . $row['ID'];
                $y = (mysqli_query($con, $x));
                $z = mysqli_fetch_assoc($y);
                $q = $z['replies'];
                
                //get number of images in the thread
                $x = "SELECT COUNT(*) AS imageReplies FROM posts WHERE replyTo = " . $row['ID'] . " AND image";
                $y = (mysqli_query($con, $x));
                $z = mysqli_fetch_assoc($y);
                $r = $z['imageReplies'];

                echo '<span title="(R)eplies / (I)mage replies">R: <strong>' . $q . '</strong> / I: <strong>' . $r . '</strong></span>';

                echo '<br>';
                echo '<strong><span class="subject">' . htmlspecialchars($row['subject']) . '</span></strong> ';
                echo htmlspecialchars($row['commento']);
            
            echo '</p></div></a>';
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
echo '</div';

?>
</body>
</html>