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

<?php loginForm($con, $op); ?>

<div class="bgImage">

    <?php searchForm($con); ?>

    <?php //print a message if a post has been reported
        if($_POST['report'])
            echo "<script> alert('Reported'); </script>";

        //you must wait 30 seconds before posting a new reply
        if($_GET['message'])
            echo '<script> alert("You must wait 30 seconds before posting a new reply."); </script>';
    ?>

    <?php boardList($con, $op); ?>


    <br>
        <!--BANNER-->
        <?php banner(); ?>
        <br>
        <p id="boardName"><strong><? echo ucfirst($boardName); ?></strong></p>
        <?php echo $top_message; ?>

    <br>


    <!--POST REPLY BUTTON-->
    <button id="showForm" style="text-align:center; height:30px;" onclick="showForm()">Post a Reply</button>

    <!--submission form-->
    <div class="form" id="form" style="display:none">
        <?php echo '<form style="display:inline;" action="/newPost.php?op='.$op.'" method="post" enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">'; ?>  
            <?php
                if(isset($_SESSION['ID'])) {
                    $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
                    $res = mysqli_query($con, $sql);
                        while($row = mysqli_fetch_assoc( $res ))
                            echo '<strong><p class="userName">' . $row['name'] . "</p></strong>";
                }
                else
                    echo '<textarea placeholder="Name" rows="1" cols="30" input type="text" name="name" />' . $_COOKIE["keepName"] . '</textarea><br>';
            ?>
            <textarea placeholder="Options" style="width:300px;" rows="1" cols="30" input type="text" name="options" /><?php echo $_COOKIE['keepOptions']; ?></textarea><br>
            <!--<textarea style="width:300px;" placeholder="Image URL"  rows="1" cols="30" input type="text" name="url" /></textarea><br>-->
            <input style="width:300px;" type="file" name="fileToUpload" id="fileToUpload"><br>
            <textarea placeholder="Comment" style="width:300px; resize:both;" rows="4" cols="40" input type="text" name="comment" /></textarea><br>
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
<!--<textarea style="width:300px;" placeholder="Image URL"  rows="1" cols="30" input type="text" name="url" /></textarea><br>-->
<input type="file" style="display:inline" name="fileToUpload" id="fileToUpload"><br>
<textarea id="linky" rows='4' style="width: 300px; resize:both;" input type='text' name='comment'></textarea><br>
<button style="text-align:center; height:30px; width:300px" type="submit" name="myButton">Post</button>
</form></div>

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