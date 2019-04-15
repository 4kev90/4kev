<?php
session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();


if($_GET['style']) {
    $style = $_GET['style'];

    setcookie('style', $style, time()+3600, '/');
}
else
    $style = $_COOKIE["style"];

//UPDATE ACTIVE HOURS
date_default_timezone_set('Europe/Paris');
$hour = date('H', time());
$sql = "SELECT * FROM activeHours WHERE hour = " . $hour;
$res = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($res)) 
    $visitsUpdated = $row['visits'] + 1;
$sql = "UPDATE activeHours SET visits = " . $visitsUpdated . " WHERE hour = '" . $hour . "'";
$res = mysqli_query($con, $sql);

//UPDATE ACTIVE USERS
$ipAddr = $_SERVER['REMOTE_ADDR'];
date_default_timezone_set('Europe/Paris');
$date = date('d/m/Y H:i:s', time());

//delete old user log
$sql = "DELETE FROM activeUsers WHERE ipAddress = '$ipAddr';";
$res = mysqli_query($con, $sql);

//insert new user log
$sql = "INSERT INTO activeUsers (ipAddress, dateTime) VALUES ('$ipAddr','$date')";
$res = mysqli_query($con, $sql);

//delete logs older than x minutes
$sql = "SELECT * FROM activeUsers";
$res = mysqli_query($con, $sql);
$activeUsers = 0;
while($row = mysqli_fetch_assoc($res)) {
    if(compareDates($row['dateTime'], $date) > 86400) {
        $sql2 = "DELETE FROM activeUsers WHERE ipAddress = '".$row['ipAddress']."'";
        $res2 = mysqli_query($con, $sql2);
    }
    else $activeUsers++;
}

//UPDATE HIT COUNTER
$sql = "SELECT * FROM hitCounter";
$res = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($res)) {
    $count = $row['count'];
    $a = "UPDATE hitCounter SET count=($count+1) WHERE count=$count";
    $b = mysqli_query($con, $a);
}
?>

<HTML>
<head>
<title>4kev</title>
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
<?php printHead(); ?>
</head>

<body>

<?php loginForm($con); ?>

<div class="bgImage">
<!--
    <div class="snowflakes" aria-hidden="true">
      <div class="snowflake">
      ❄
      </div>
      <div class="snowflake">
      ❅
      </div>
      <div class="snowflake">
      ❆
      </div>
      <div class="snowflake">
      ❄
      </div>
      <div class="snowflake">
      ❅
      </div>
      <div class="snowflake">
      ❆
      </div>
      <div class="snowflake">
      ❄
      </div>
      <div class="snowflake">
      ❅
      </div>
      <div class="snowflake">
      ❆
      </div>
      <div class="snowflake">
      ❄
      </div>
    </div>
-->
    <?php searchForm($con); ?>

    <?php boardList($con); ?>

    <br>
        <!--BANNER-->
        <?php banner(); ?>
        <br>
        <p id="boardName"><strong>Welcome to 4kev</strong></p>
        <?php echo $top_message; ?>

    <br>

    <!--ERROR / CONFIRMATION MESSAGE -->
    <?php
        $err = (isset($_GET['err'])) ? $_GET['err'] : 0;
        if($err == 1)
            echo '<table><td style="height:30px; background:lightgreen; border:1px solid darkgreen; border-spacing: 1px; padding: 3px;"><p>You will receive an activation email</p></td></table>';
        if($err == 2)
            echo '<table><td style="height:30px; background:lightcoral; border:1px solid darkred; border-spacing: 1px; padding: 3px;"><p>Name is already taken</p></td></table>';
        if($err == 3)
            echo '<table><td style="height:30px; background:lightcoral; border:1px solid darkred; border-spacing: 1px; padding: 3px;"><p>Email is already registered</p></td></table>';
        if($err == 4)
            echo '<table><td style="height:30px; background:lightgreen; border:1px solid darkgreen; border-spacing: 1px; padding: 3px;"><p>Your account is now active</p></td></table>';
        if($err == 5)
            echo '<table><td style="height:30px; background:lightcoral; border:1px solid darkred; border-spacing: 1px; padding: 3px;"><p>Incorrect password. Try again</p></td></table>';
        if($err == 6)
            echo '<table><td style="height:30px; background:lightcoral; border:1px solid darkred; border-spacing: 1px; padding: 3px;"><p>Password is too short (min 8 characters)</p></td></table>';
    ?>



    <!--REGISTER BUTTON-->
    <?php
        if(!isset($_SESSION['ID']))
            echo '<button id="showForm" style="width:100px; text-align:center; height:30px;" onclick="showForm()">Register</button>';
    ?>

    <!--REGISTER WINDOW-->
        <div id="form" style='display:none; margin: 0 auto; width:200px;'>
        <form action= "register.php" method="post" onsubmit="myButton.disabled = true; return true;">
            <input type="text" placeholder="Name" name="name" /><br>
            <input type="password" placeholder="Password" name="pwd" /><br>
            <input type="password" placeholder="Confirm password" name="pwd2" /><br>
            <input type="text" placeholder="Email" name="email" /><br>
            <button style="text-align:center; height:30px; width:100%" type="submit" name="myButton">Register</button>
        </form>
        </div>
        <br><hr>
</div>

<!--
<div style="display:inline-block; float:left; width:10%; text-align:center;">
    <a target="_blank" href="https://ceil-m.tumblr.com/"><img src="http://www.4kev.org/uploads/12014.png"></a>
</div>
-->

<div style="display:inline-block; float:left; width:75%">
    
    <div class="lastPosts" style="float:right; width:93%; margin-right: 10px;">

        <?php
            //display last posts
            $sql = "SELECT * FROM posts ORDER BY ID DESC";
            $res = mysqli_query($con, $sql);
            echo "<strong><p style='text-align:center'>LAST POSTS</p></strong>";
            $cont = 0;
            while(($cont < 70) && $row = mysqli_fetch_assoc( $res )) {
                if($row['board'] != "test" && $row['board'] != "traps" && $row['commento']) {

                    //stampa link to thread
                    if($row['replyTo'])
                        $num = $row['replyTo'];
                    else
                        $num = $row['ID'];
                    $threadlink = "https://4kev.org/threads/" . $num . "#" . $row['ID'];

                    //stampa board
                    echo "<p><a href='$threadlink'><strong>No.{$row['ID']} {$row['board']}</strong><br></a>";

                    //stampa commento
                        $rowComment = htmlspecialchars($row['commento']);
                    //divide line into words
                        $words = explode(" ", $rowComment);
                        foreach ($words as $word) {
                           $word = wordFilter($word);
                           echo "$word "; 
                        }

                

                echo "</p>";
                $cont++;
            }

            }
        ?>
    </div>
</div>

<div style="display:inline-block; float:right; width:25%">
    <div class="post" style="width:80%">
        <?php
            //display last images
            $sql = "SELECT * FROM posts ORDER BY ID DESC";
            $res = mysqli_query($con, $sql);
            echo "<strong><p style='text-align:center'>LAST IMAGES</p></strong>";
            $cont = 0;
            while(($cont < 15) && $row = mysqli_fetch_assoc( $res )) {
                if($row['board'] != "test" && ($row['image'] || $row['imageUrl'])) {

                    //link to thread
                    if($row['replyTo'])
                        $num = $row['replyTo'];
                    else
                        $num = $row['ID'];
                    $threadlink = "https://4kev.org/threads/" . $num  . "#" . $row['ID'];

                    //board
                    echo "<p style='text-align:center'><strong>No.{$row['ID']} {$row['board']}<br></strong></p>";

                    //picture
                    if($row['image']) {
                        $pic = $row['image'];
                        if (strpos(strtolower($pic), 'pdf'))
                            echo "<p style='text-align:center'><a href='$threadlink'><img style='width:170px; height:auto;' src='pdflogo.png'></a></p>";
                        else if (!strpos($pic, 'webm') && !strpos($pic, 'mp3')) 
                            echo "<p style='text-align:center'><a href='$threadlink'><img style='width:170px; height:auto;' src='thumbnails/$pic'></a></p>";
                        else
                            echo "<p style='text-align:center'><a href='$threadlink'><img style='width:170px; height:auto;' src='video.png'></a></p>";
                        $cont++;
                    }
                    //url
                    if($row['imageUrl']) {
                        $url = $row['imageUrl'];
                        echo "<p style='text-align:center'><a href='$threadlink'><img style='width:170px; height:auto;' src='" . $row['imageUrl'] . "'></a></p>";
                        $cont++;
                    }
                }
            }
        ?>
    </div>
</div>
<div style="clear:both" />
<br>
<hr>

</div>

<!--RULES-->
<div id='rules' style='display:none; text-align:center'>
    <div class="post">
        <p align='left'>
            1) be polite to other users<br>
            2) do not spam or flood the website<br>
            3) do not post pornography or disturbing content<br>
            4) do not post degenerate content<br>
        </p>
    </div>
    <hr>
</div>

<?php footer($con); ?>

<br>
</body>
</HTML>
