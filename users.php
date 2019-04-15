<?php

session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();

if($_GET['user']) 
    $userName = $_GET['user'];

?>

<HTML>
<head>
<title><?php echo $userName; ?></title>
<?php printHead(); ?>
</head>
<body>

<?php loginForm($con, $boardName); ?>

<div class="bgImage">

    <?php searchForm($con); ?>

    <?php boardList($con, $boardName); ?>

    <br>
        <!--BANNER-->
        <?php banner(); ?>
        <br>
        <p id="boardName"><strong><?php echo ucfirst($userName); ?></strong></p>
        <?php echo $top_message; ?>
    <br>

    <br><hr>
</div>

<!--post preview-->
<div class="post" id="preview" style="display:none"></div>

<?php

//####################   PRINT POSTS   #######################



$selectSQL = "SELECT * FROM posts WHERE name = '" . $userName . "' AND loggedIn ORDER BY ID DESC";
$selectRes = mysqli_query($con, $selectSQL);

while($row = mysqli_fetch_assoc( $selectRes )) { 

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
</body>
</html>

