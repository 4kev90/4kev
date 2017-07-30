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

<!--BANNER-->
<center>
<?php
$banner = "<A href = 'http://4kev.org/'><img src = '/banners/" . rand(0, 38) . ".gif' /></A>";
echo $banner;
?>

<br><br>
<table><td><center>
<p style="font-size:30px;"><b>Statistics</b></p>
<?php echo $top_message; ?>
</center><td></table>
<br>
<hr>
</div>

<?php

echo '<table align="center"><tr><td>';

//VISITS
$sql = "SELECT * FROM hitCounter";
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res))
        $count = $row['count'];
echo '<p style="color:lightblue"><b>Visits: <font color="orange">' . $count . '</font></b> </p>';

//TOTAL POSTS
$selectSQL = "SELECT ID FROM posts ORDER BY ID DESC LIMIT 1;";
$result = (mysqli_query($con, $selectSQL) );
while($row = mysqli_fetch_assoc( $result )) 
    $q = $row['ID'];

echo '<p style="color:lightblue"><b>Total posts: <font color="orange">' . $q . '</font></b> </p>';

//TOTAL FILE SIZE
$size = 0;
$sql = "SELECT image FROM posts WHERE image";
$res = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($res)) {
	$file = 'uploads/' . $row['image'];
	$size += filesize('uploads/' . $row['image']);
}

echo '<p style="color:lightblue"><b>Total file size: <font color="orange">' . round(($size / 1000000), 3) . ' MB</font></b> </p>';

//ACTIVE THREADS
$x = "SELECT COUNT(bump) AS threads FROM posts WHERE bump";
$y = mysqli_query($con, $x);
$z = mysqli_fetch_assoc($y);
$q = $z['threads'];

echo '<p style="color:lightblue"><b>Active threads: <font color="orange">' . $q . '</font></b> </p>';

//get number of replies
$x = "SELECT COUNT(replyTo) AS replies FROM posts WHERE replyTo";
$y = mysqli_query($con, $x);
$z = mysqli_fetch_assoc($y);
$r = $z['replies'];

//AVERAGE REPLIES PER THREAD
$avg = $r / $q;
echo '<p style="color:lightblue"><b>Average replies per thread: <font color="orange">' . round($avg, 2) . '</font></b> </p>';

// POSTS PER BOARD
echo '<p style="color:lightblue"><b>Posts per board:</b></p>';

$boards = array();
$posts  = array();

//put all boards into array
$sql = 'SELECT * FROM boards';
$res = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($res)) {
	array_push($boards, $row['boardName']);
}

//put board counters into array
foreach($boards as $board) {
	$x = 'SELECT COUNT(board) AS boardPosts FROM posts WHERE board = "' . $board . '"';
	$y = (mysqli_query($con, $x));
	$z = mysqli_fetch_assoc($y);
	$q = $z['boardPosts'];
	array_push($posts, $q);
}

//sort arrays
for($j = 0; $j < (count($posts) - 1); $j++) 
	for($i = 0; $i < (count($posts) - 1); $i++) {
		if($posts[$i] < $posts[$i+1]) {
			$temp = $posts[$i];
			$posts[$i] = $posts[$i+1];
			$posts[$i+1] = $temp;
			$temp = $boards[$i];
			$boards[$i] = $boards[$i+1];
			$boards[$i+1] = $temp;
		}
	}

echo '<p>';

foreach($boards as $board) {
	$x = 'SELECT COUNT(board) AS boardPosts FROM posts WHERE board = "' . $board . '"';
	$y = (mysqli_query($con, $x));
	$z = mysqli_fetch_assoc($y);
	$q = $z['boardPosts'];
	echo '<font color="lawngreen">' . $board . '</font>';
	echo '<font color="darkgrey">';
	for($i = strlen($board); $i < 15; $i++)
		echo '_';
	echo "</font>";
	echo '<font color="orange"><b>' . $q . '</b></font>';
	echo '<font color="darkgrey">';
	for($i = strlen($q); $i < 7; $i++)
		echo '_';
	echo "</font>";
	echo '<font color="fuchsia"><b>';
	for($i = 0; $i < ($q / 5); $i++)
		echo '#';
	echo '</b></font>';
	echo '<br>';
}

echo '</p>';

echo '</td></tr></table>';

?>