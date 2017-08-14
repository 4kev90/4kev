<?php
session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();
?>

<HTML>
<head>
<title>Rules</title>
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
</head>

<body>
<div class="bgImage">

<?php boardList($con); ?>

<!--BANNER-->
<center>
<?php
$banner = "<A href = 'http://4kev.org/'><img src = '/banners/" . rand(0, 38) . ".gif' /></A>";
echo $banner;
?>

<br><br>
<table><td><center>
<p style="font-size:30px;"><b>GLOBAL RULES</b></p>
<?php echo "<p align='left'>
	1) be polite to other users<br>
	2) do not spam or flood the website<br>
	3) do not post pornography or disturbing content<br>
	4) critics must be constructive<br>
	5) just to make it clear, this is an anime website
	</p>"; ?>
</center><td></table>
<br>
<hr>
</div>
</body>
</HTML>
