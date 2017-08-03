<?php
session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();
?>

<HTML>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
<link rel="stylesheet" type="text/css" href="/style.css?v=<?=time();?>">
<?php
	$style = $_COOKIE["style"];
    if($style != 'cyber')
        echo '<link rel="stylesheet" type="text/css" href="/' . $style . '.css?v=' . time() . '"';
?>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="../../myjs.js?v=<?=time();?>" ></script>
<script src="jquery-3.2.0.min.js"></script>

<div class="bgImage">

<?php boardList(); ?>

<center>
<?php include "banner.php"; ?>

<br><br>
<table><td><center>
<p style="font-size:30px;"><b>There are no rules</b></p>
<?php echo "<p>If we don't like what you post, you get banned</p>"; ?>
</center><td></table>
<br>
<hr>
</div>

</HTML>
