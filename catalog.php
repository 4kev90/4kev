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

<?php loginForm($con, $boardName); ?>

<div class="bgImage">

    <?php searchForm($con); ?>

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
        <?php echo '<form style="display:inline;" action= "/newPost.php?board='.$boardName.'" method="post" enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">'; ?>
        
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

<!--reply window-->
<div id="draggable" class='replyWindow'>
<p style="cursor:move; text-align:center;"><strong>Post a reply</strong><span class='close'>&times;</span></p>
<form id='formAction' style='display:inline;' method='post' enctype='multipart/form-data' onsubmit='myButton.disabled = true; return true;'>
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
<div class="post" id="preview" style="display:none"></div>

<?php echo '<p>[<a href="boards/' . $boardName . '/">Thread List</a>]</p>'; ?>
<hr>

<?php

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