<?php
session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();

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

// update visitLog
$sql = "INSERT INTO visitLog (ipAddress, dateTime) VALUES ('$ipAddr','$date')";
$res = mysqli_query($con, $sql);


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

<div class="bgHome">

    <!--<div id="lastImages">-->
    <?php
    /*
        //display last images
        $sql = "SELECT * FROM posts ORDER BY ID DESC";
        $res = mysqli_query($con, $sql);
        $cont = 0;
        while(($cont < 10) && $row = mysqli_fetch_assoc( $res )) {
            if($row['image'] && ($row['board'] != 'test')) {

                //link to thread
                if($row['replyTo'])
                    $num = $row['replyTo'];
                else
                    $num = $row['ID'];
                $threadlink = "https://4kev.org/threads/" . $num  . "#" . $row['ID'];

                //board
                //echo "<p style='text-align:center'><strong>No.{$row['ID']} {$row['board']}<br></strong></p>";

                //picture
                if($row['image']) {
                    $pic = $row['image'];

                    echo "<a class='homePageImageLink' href='$threadlink'>";

                      if (strpos(strtolower($pic), 'pdf'))
                          echo "<img class='homePageImage' src='images/pdflogo.png'>";
                      else if (!strpos($pic, 'webm') && !strpos($pic, 'mp3')) 
                          echo "<img class='homePageImage' src='thumbnails/$pic'>";
                      else
                          echo "<img class='homePageImage' src='images/video.png'>";

                    echo "</a>";

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
    */
    ?>
    <!--</div>-->

    <?php
        echo "<br>";
        banner();
        echo "<p id='boardName'>4KEV WILL SHUT DOWN ON NOVEMBER 20<br>THANK YOU ALL</p>";
        //echo "<p id='about'>The website is under maintenance. The old homepage is available at https://www.4kev.org/lastposts.php</p>";
    ?>
    <p class="homePageSection">Boards</p>
    <a href="https://www.4kev.org/boards/technology/"><button class="boardButton">Technology</button></a>
    <a href="https://www.4kev.org/boards/programming/"><button class="boardButton">Programming</button></a>
    <a href="https://www.4kev.org/boards/media/"><button class="boardButton">Media</button></a>
    <a href="https://www.4kev.org/boards/random/"><button class="boardButton">Random</button></a>
    <a href="https://www.4kev.org/boards/meta/"><button class="boardButton">Meta</button></a>
    <br><br>

    <p class="homePageSection">Last Posts</p>
    <div text-align:center; padding:5px;">
        <div id="lastPosts">
            <?php
                //display last posts
                $sql = "SELECT * FROM posts ORDER BY ID DESC";
                $res = mysqli_query($con, $sql);
                //echo "<p style='text-align:center; font-weight:bold;'>LAST POSTS</p>";
                $cont = 0;
                while(($cont < 20) && $row = mysqli_fetch_assoc( $res )) {
                    if($row['commento'] && ($row['board'] != 'test')) {

                        //stampa link to thread
                        if($row['replyTo'])
                            $num = $row['replyTo'];
                        else
                            $num = $row['ID'];
                        $threadlink = "https://4kev.org/threads/" . $num . "#" . $row['ID'];

                        //stampa board
                        //echo "<p><a class='homePageCommentLink' href='$threadlink'><span class='homePageCommentInfo'>{$row['ID']} </span><span class='homePageComment'>";
                        // echo "<p><a class='homePageCommentLink' href='$threadlink'>{$row['ID']} <span class='homePageComment'>";
                        echo "<a href='$threadlink'><div class='homePageCommentLink'><p>";

                        //stampa commento
                            $rowComment = htmlspecialchars($row['commento']);
                        //divide line into words
                            $words = explode(" ", $rowComment);
                            foreach ($words as $word) {
                               $word = wordFilter($word);
                               echo "$word "; 
                            }
                        echo "</p></div></a><br>";
                        $cont++;
                    }
                }
            ?>
        </div>

    </div>

    <p class="homePageSection">Stats</p>
    <?php footer($con); ?>

    <p class="homePageSection">Friends</p>
    <a href="https://onee.ch/" target="_blank"><img class="banners" src="/images/banner_oneechan.png"></a>
    <a href="https://iji.cx/" target="_blank"><img class="banners" src="/images/banner_iji.gif"></a>

<!--
    <div id="banners">
        <a><img class="banners" src="/images/banner_iji.gif"></a>
        <a><img class="banners" src="/images/banner_oneechan.png"></a>
    <div>
-->
</div>

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

</body>
</HTML>
