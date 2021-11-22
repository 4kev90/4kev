<?php
session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();
?>

<HTML>
<head>
<title>Statistics</title>
<?php printHead(); ?>
</head>
<body>

<?php 
	//topBar($con); 
?>

<?php boardList($con); ?>

<div class="bgImage">

    <p id="boardName"><strong>Statistics</strong></p>
    
    <?php echo $top_message; ?>

<br>

<?php

echo '<div style="text-align:center"><div style="text-align:left" class="post">';

//VISITS
$sql = "SELECT * FROM hitCounter";
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res))
        $count = $row['count'];
echo '<p>Visits: ' . $count . '</p>';

//TOTAL POSTS
$selectSQL = "SELECT ID FROM posts ORDER BY ID DESC LIMIT 1;";
$result = (mysqli_query($con, $selectSQL) );
while($row = mysqli_fetch_assoc( $result )) 
    $q = $row['ID'];

echo '<p>Total posts: ' . $q . '</p>';

//TOTAL FILE SIZE
$size = 0;
$sql = "SELECT image FROM posts WHERE image";
$res = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($res)) 
	$size += filesize('uploads/' . $row['image']);

echo '<p>Total file size: ' . round(($size / 1000000), 3) . ' MB</p>';

//ACTIVE THREADS
$x = "SELECT COUNT(bump) AS threads FROM posts WHERE bump";
$y = mysqli_query($con, $x);
$z = mysqli_fetch_assoc($y);
$q = $z['threads'];

echo '<p>Active threads: ' . $q . '</p>';

//get number of replies
$x = "SELECT COUNT(replyTo) AS replies FROM posts WHERE replyTo";
$y = mysqli_query($con, $x);
$z = mysqli_fetch_assoc($y);
$r = $z['replies'];

//AVERAGE REPLIES PER THREAD
$avg = $r / $q;
echo '<p>Average replies per thread: ' . round($avg, 2) . '</p>';

// POSTS PER BOARD
echo '<p>Posts per board:</p>';

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
	for($i = 0; $i < ($q / 30); $i++)
		echo '▀';
	echo ' ';
	echo $q;
	echo ' ';
	echo $board;
	echo ' ';
	echo '<br>';
}

echo '</p>';

// VISITS PER HOUR
echo '<p>Visits per hour:</p>';
echo '<p>';

$sql = "SELECT * FROM activeHours";
$res = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($res)) {
	echo $row['hour'] . " ";
	for($i = 0; $i < $row['visits']; $i+=200)
		echo '▀';
	echo '<br>';
	
}

echo '</p>';

echo '</div></div>';

?>