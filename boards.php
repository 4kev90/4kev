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
            echo '<script> alert("You must wait longer before making a new post."); </script>';
    ?>

    <?php boardList($con, $boardName); ?>


    <br>
        <!--BANNER-->
        <?php banner(); ?>
        <br>
        <p id="boardName"><strong><?php echo ucfirst($boardName); ?></strong></p>
        <?php echo $top_message; ?>

    <br>


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
            <textarea style="width:300px;" placeholder="Options"    rows="1" cols="30" input type="text" name="options" /><?php echo $_COOKIE['keepOptions']; ?></textarea><br>
            <textarea style="width:300px;" placeholder="Subject"    rows="1" cols="30" input type="text" name="subject" /></textarea><br>
            <!--<textarea style="width:300px;" placeholder="Image URL"  rows="1" cols="30" input type="text" name="url" /></textarea><br>-->
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
<!--<textarea style="width:300px;" placeholder="Image URL"  rows="1" cols="30" input type="text" name="url" /></textarea><br>-->
<input type="file" style="display:inline" name="fileToUpload" id="fileToUpload"><br>
<textarea id="linky" rows='4' style="width: 300px; resize:both;" input type='text' name='comment'></textarea><br>
<button style="text-align:center; height:30px; width:300px" type="submit" name="myButton">Post</button>
</form></div>

<!--post preview-->
<div class="post" id="preview" style="display:none"></div>

<div id="dropDown" style="display:none">test</div>

<?php echo '<p>[<a href="/catalog.php?board=' . $boardName . '">Catalog</a>]</p>'; ?>
<hr>


<?php

//####################   PRINT POSTS   #######################



$selectSQL = "SELECT * FROM posts ORDER BY sticky DESC, bump DESC;";
                                  


$selectRes = mysqli_query($con, $selectSQL);
$cont = 0;
while($row = mysqli_fetch_assoc( $selectRes )) {
    //if counter is less than the maximum allowed threads
    if($cont < 150) {
        if(($row['replyTo'] == 0) && ($row['board'] == $boardName)) {
            $cont = $cont+1; 
            if($cont > (($page-1)*15) && $cont <= ($page*15)) { 

            printPost($con, $isMod, $row);

            // THREAD EXPANSION
            echo '<p class="info"><a title="Expand thread" id="expandButton'.$row['ID'].'" class="arrow" onclick="expand('.$row['ID'].')">▼</a> ';

            //print number of replies
            //get number of replies in the thread
                    $x = "SELECT COUNT(*) AS replies FROM posts WHERE replyTo = " . $row['ID'];
                    $y = (mysqli_query($con, $x));
                    $z = mysqli_fetch_assoc($y);
                    $q = $z['replies'];
            echo $q . ' replies';

            //print link to thread
            echo " [<a href=/threads.php?op=".$row['ID'].">Reply</a>]";

            echo '</p>';
                

            //#################################################################################################################


            //PRINT LAST REPLIES
            echo '<div id="replies'.$row['ID'].'">';
            $sqlReplies = "(SELECT * FROM posts WHERE replyTo = " . $row['ID'] . " ORDER BY ID DESC LIMIT 3) ORDER BY ID ASC;";
            $resReplies = mysqli_query($con, $sqlReplies);
            while($rowReplies = mysqli_fetch_assoc( $resReplies )) {

                printPost($con, $isMod, $rowReplies);

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

        //delete old thumbnails
        unlink('thumbnails/' . $row['image']); 
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