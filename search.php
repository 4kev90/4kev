<?php

session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();

//redirect if nothing was entered
if(!$_POST['search'])
    header('Location: http://4kev.org');

$search = mysqli_real_escape_string($con, $_POST['search']);

?>

<HTML>
<head>
<title>Search</title>
<?php printHead(); ?>
</head>

<?php loginForm($con, $op); ?>

<div class="bgImage">

    <?php searchForm($con); ?>

    <?php boardList($con, $op); ?>

    <br>
        <!--BANNER-->
        <?php banner(); ?>
        <br>
        <p id="boardName"><strong><? echo ucfirst($boardName); ?></strong></p>
        <?php echo $top_message; ?>
    <br>

    <hr>
</div>

<!--post preview-->
<div class="post" id="preview" style="display:none"></div>

<?php

//display posts

$selectSQL = "SELECT * FROM posts WHERE commento LIKE '%" . $search . "%' ORDER BY ID DESC";
$selectRes = mysqli_query($con, $selectSQL);

while( $row = mysqli_fetch_assoc($selectRes)) {

    printPost($con, $isMod, $row);

    //print link to thread
    if($row['replyTo'])
        $num = $row['replyTo'];
    else
        $num = $row['ID'];
    $threadlink = "http://4kev.org/threads/" . $num . "#" . $row['ID'];

    //print board
    echo "<p><strong>{$row['board']} [<a href='$threadlink'>Go</a>]</strong></p><hr>";

}

?>
<br>
</body>
</html>