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
        $style = 'cyber';
    echo '<link rel="stylesheet" type="text/css" href="themes/' . $style . '.css?v=' . time() . '">'; 
?>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="../../myjs.js?v=<?=time();?>" ></script>
<script src="jquery-3.2.0.min.js"></script>
</head>

<body>


<div class="bgImage">

    <?php boardList($con); ?>

    <!--BANNER-->
    <?php banner(); ?>

    <br><br>
    <div class="boardName">
    <p style="font-size:30px"><strong>Welcome to 4kev</strong></p>
    <?php echo $top_message; ?>
    </div>
    <br><br>

    <!--LOGIN BAR-->
    <?php loginBar($con); ?>

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
            while(($cont < 15) && $row = mysqli_fetch_assoc( $res )) {
                if($row['board'] != "test" && $row['commento']) {

                    //stampa link to thread
                    if($row['replyTo'])
                        $num = $row['replyTo'];
                    else
                        $num = $row['ID'];
                    $threadlink = "http://4kev.org/threads.php?op=" . $num . "#" . $row['ID'];

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
while(($cont < 5) && $row = mysqli_fetch_assoc( $res )) {
    if($row['board'] != "test" && $row['image']) {

        //link to thread
        if($row['replyTo'])
            $num = $row['replyTo'];
        else
            $num = $row['ID'];
        $threadlink = "http://4kev.org/threads.php?op=" . $num  . "#" . $row['ID'];

        //board
        echo "<p style='text-align:center'><a href='$threadlink'><strong>No.{$row['ID']} {$row['board']}<br></strong></a></p>";

        //picture
        $pic = $row['image'];
        echo "<p style='text-align:center'><a href='$threadlink'><img style='max-height:170px; max-width:170px;' src='uploads/$pic'></a></p>";

        $cont++;
    }
}
?>
</div>
</div>




<div style="clear:both" />
<br><hr>
<div style="clear:both">

<?php
    $sql = "SELECT * FROM hitCounter";
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res)) {
        $count = $row['count'];
        $a = "UPDATE hitCounter SET count=($count+1) WHERE count=$count";
        $b = mysqli_query($con, $a);
    }
?>
<div class="footer">
<p align="center" style="clear:both;">
    <a href="stats.php">Stats</a> | 
    <a href="rules.php">Rules</a> | 
    Visits: <?php echo $count; ?>
</p>
<p align="center"> 
    <a href="index.php?style=4kev">4kev</a> | 
    <a href="index.php?style=cyber">Cyber</a> | 
    <a href="index.php?style=tomorrow">Tomorrow</a> | 
    <a href="index.php?style=insomnia">Insomnia</a> | 
    <a href="index.php?style=yotsuba">Yotsuba</a> | 
    <a href="index.php?style=yotsuba-b">Yotsuba-B</a> | 
    <a href="index.php?style=photon">Photon</a> | 
    <!--<a href="index.php?style=zen">Zen</a>-->
</p>
</div>
</div>
</div>
<br>
</body>
</HTML>
