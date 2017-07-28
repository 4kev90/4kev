<?php
session_start();

include('functions.php');

//connect to database
$con = connect_to_database();
?>

<HTML>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
<link rel="stylesheet" type="text/css" href="/style.css?v=<?=time();?>">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="../../myjs.js?v=<?=time();?>" ></script>
<script src="jquery-3.2.0.min.js"></script>

<div class="bgImage">

<?php boardList(); ?>

<!--BANNER-->
<center>
<?php
$banner = "<A href = 'http://4kev.org/'><img src = '/banners/" . rand(0, 38) . ".gif' /></A>";
echo $banner;
?>

<br><br>
<table><td><center>
<p style="font-size:30px;"><b>Welcome to 4kev</b></p>
<?php echo $top_message; ?>
</center><td></table>
<br>
<center><div style="width:50%">



<!--LOGIN BAR-->
<?php 
if(!isset($_SESSION['ID']))
    echo '<!--LOGIN BUTTON-->
    <button id="showLogin" style="width:100px; text-align:center; height:30px;" onclick="showLogin()">Login</button>
    <div id="login" style="display:none"><table>
    <form action= "../login.php?x=' . $_SERVER['PHP_SELF'] . '" method="post" onsubmit="myButton.disabled = true; return true;">
    <td><p>Email</p></td><td><input type="text" name="email" /></td>
    <td><p>Password</p></td><td><input type="password" name="pwd" /></td>
    <td><button type="submit" name="myButton">Log In</button></td>
    </form></table><br></div>';
else {
    $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
        $res = mysqli_query($con, $sql);
        while($row = mysqli_fetch_assoc( $res ))
             echo "
    <table><td><p style='display:inline'>Logged in as <b>" . $row['name'] . "</b></p></td>";
    echo '
    <form action= "../logout.php?x=' . $_SERVER['PHP_SELF'] . '" method="post">
    <td><button>Log Out</button></td>
    </form></table>';
}
?>

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
    <div id="form" style='display:none'>
    <form action= "register.php" method="post" onsubmit="myButton.disabled = true; return true;">
    <table>
        <tr><td><input type="text" placeholder="Name" name="name" /></td></tr>
        <tr><td><input type="password" placeholder="Password" name="pwd" /></td></tr>
        <tr><td><input type="password" placeholder="Confirm password" name="pwd2" /></td></tr>
        <tr><td><input type="text" placeholder="Email" name="email" /></td></tr>
      
        <tr><td><button style="text-align:center; width:100%;height:30px;" type="submit" name="myButton">Register</button></td></tr>
    </table>
    </form>
    </div>

</div>
</center>

<hr>

</div>


<div>
<center>
<table style="width: 50%">
<?php
//display last posts
$sql = "SELECT * FROM posts ORDER BY ID DESC";
$res = mysqli_query($con, $sql);
echo "<tr><td style='text-align:center'><b><p>LAST POSTS</p></b></td></tr>";
$cont = 0;
while(($cont < 20) && $row = mysqli_fetch_assoc( $res )) {
    if($row['board'] != "test") {

        //stampa link to thread
        if($row['replyTo'])
            $num = $row['replyTo'];
        else
            $num = $row['ID'];
        $threadlink = "http://4kev.org/threads/" . $num . ".php";

        //stampa board
        echo "<tr><td><p><a href='$threadlink'><b>No.{$row['ID']} {$row['board']}<br></b></a>";

        //stampa commento
            $rowComment = htmlspecialchars($row['commento']);
        //divide line into words
            $words = explode(" ", $rowComment);
            foreach ($words as $word) {
               $word = wordFilter($word);
               echo "$word "; 
            }

        

        echo "</p></td></tr>";
        $cont++;
    }
}
?>
</td></table>
</div>
<hr>
<div style="clear:both">
<center>
<p style="clear:both;"><i>kev@4kev.org</i> | <A href="http://4kev.org/rules.php">Rules</A>  | Visits: <?php include "count.php"; ?></p>
</center>
<br>
</div>
</center>
</div>
</HTML>
