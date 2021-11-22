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
        banner();
        echo "<p id='boardName'>" . strtoupper($boardName) . "</p>";
        //echo '<a href="/catalog.php?board=' . $boardName . '"><button id="catalogButton">Catalog</button></a><br><br>';
        //topBar($con, $boardName, $boardName);
        echo '<button id="showPostWindow" onclick="showPostWindow()">New Thread</button>';

        //print a message if a post has been reported
        if($_POST['report'])
            echo "<script> alert('Reported'); </script>";

        //you must wait 2 minutes before posting a new thread
        if($_GET['message'])
            echo '<script> alert("You must wait longer before making a new post."); </script>';
    ?>

    <!--hidden form to block bots-->
    <form id="hiddenForm" action="https://www.4kev.org/index.php" method="post" enctype="multipart/form-data">
        <textarea placeholder="Name" input type="text" name="name" /></textarea><br>'
        <textarea placeholder="Subject" input type="text" name="subject" /></textarea><br>
        <textarea class="commentField" placeholder="Comment" input type="text" name="comment" /></textarea><br>
        <button type="submit" value="Post"></button>
    </form>

    <!--submission form-->
    <div id="postWindow" class="draggable">
        <button id="closePostWindow" class='close'>✖</button>
        <p style="text-align:center;"><strong>Start a new thread</strong></p>

        <?php echo '<form action="/newPost.php?board='.$boardName.'" method="post" enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">'; ?>
        
            <?php 
            if(isset($_SESSION['ID'])) {
                $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
                $res = mysqli_query($con, $sql);
                    while($row = mysqli_fetch_assoc( $res ))
                        echo "<strong><p style='text-align:center' class='userName'>" . $row['name'] . "</p></strong>";
            }
            else
                echo '<textarea placeholder="Name" input type="text" name="name" /></textarea><br>';
            ?>
            <textarea placeholder="Subject" input type="text" name="subject" /></textarea><br>
            <textarea class="commentField" placeholder="Comment" input type="text" name="comment" /></textarea><br>

            <?php
                if($isMod)
                    echo ('<input name="fileToUpload" type="file">');
            ?>
            <button type="submit" id="postButton" value="Post" class="postButton" >Post</button>
        </form>
    </div>
    <br>
    <br>
</div>

<!--reply window-->
<div id='replyWindow' class="draggable">
    <button id="closeReplyWindow" class='close'>✖</button>
    <p style="text-align:center;"><strong>Post a reply</strong></p>
    <form id='formAction' style='display:inline;' method='post' enctype='multipart/form-data' onsubmit='myButton.disabled = true; return true;'>
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
        <!--<input name="fileToUpload" type="file">-->
        <button name="myButton" type="submit" class="postButton" class"postButton">Post</button>
    </form>
</div>

<!--post preview-->
<div class="post" id="preview" style="display:none"></div>

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

            echo "<br>";

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
            echo " <a href=/threads.php?op=".$row['ID']."><button>Enter</button></a>";

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
            echo '<br><hr>';
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