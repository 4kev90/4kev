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
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
<?php
    if($_GET['style'])
        $style = $_GET['style'];
    else if($_COOKIE["style"]) 
        $style = $_COOKIE["style"];
    else
        $style = $defaultTheme;
    echo '<link rel="stylesheet" type="text/css" href="themes/' . $style . '.css?v=' . time() . '">'; 
?>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="../../myjs.js?v=<?=time();?>" ></script>
<script src="jquery-3.2.0.min.js"></script>
</head>

<body>

<?php loginForm($con); ?>

<div class="bgImage">

    <?php boardList($con); ?>

    <br>
    <div id="boardName">
    <!--BANNER-->
    <?php banner(); ?>
    <p style="font-size:30px"><strong>Welcome to 4kev</strong></p>
    <?php echo $top_message; ?>
    </div>

    <br><br>

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

<div style="display:inline-block; float:left; width:64%">
    
    <div class="post" style="float:right; width:77%; margin-right: 10px;">

        <?php
            //display last posts
            $sql = "SELECT * FROM posts ORDER BY ID DESC";
            $res = mysqli_query($con, $sql);
            echo "<strong><p style='text-align:center'>LAST POSTS</p></strong>";
            $cont = 0;
            while(($cont < 30) && $row = mysqli_fetch_assoc( $res )) {
                if($row['board'] != "test" && $row['commento']) {

                    //stampa link to thread
                    if($row['replyTo'])
                        $num = $row['replyTo'];
                    else
                        $num = $row['ID'];
                    $threadlink = "http://4kev.org/threads/" . $num . "#" . $row['ID'];

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

<div style="display:inline-block; float:right; width:36%">
    <div class="post" style="width:58%">
        <?php
            //display last images
            $sql = "SELECT * FROM posts ORDER BY ID DESC";
            $res = mysqli_query($con, $sql);
            echo "<strong><p style='text-align:center'>LAST IMAGES</p></strong>";
            $cont = 0;
            while(($cont < 10) && $row = mysqli_fetch_assoc( $res )) {
                if($row['board'] != "test" && $row['image']) {

                    //link to thread
                    if($row['replyTo'])
                        $num = $row['replyTo'];
                    else
                        $num = $row['ID'];
                    $threadlink = "http://4kev.org/threads/" . $num  . "#" . $row['ID'];

                    //board
                    echo "<p style='text-align:center'><strong>No.{$row['ID']} {$row['board']}<br></strong></p>";

                    //picture
                    $pic = $row['image'];
                    echo "<p style='text-align:center'><a href='$threadlink'><img style='max-height:170px; max-width:170px;' src='thumbnails/$pic'></a></p>";

                    $cont++;
                }
            }
        ?>
    </div>
</div>
<div style="clear:both" />
<br>
<hr>

<!--RULES-->
<div id='rules' style='display:none; text-align:center'>
    <div class="post">
        <p align='left'>
            1) be polite to other users<br>
            2) do not spam or flood the website<br>
            3) do not post pornography or disturbing content<br>
            4) critics about the website must be constructive<br>
        </p>
    </div>
    <hr>
</div>

<?php footer($con); ?>

<br>
</body>
</HTML>
