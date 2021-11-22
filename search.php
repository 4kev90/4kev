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
<body>

<div class="bgImage">


    <?php 
        boardList($con);
        echo "<br>";
        banner();
        echo "<br>";
        echo "<p id='boardName'>Search</p>";
    ?>

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
    echo "<p style='display:inline;'><strong>{$row['board']}</strong></p> <button><a href='$threadlink'>View Thread</a></button><hr>";

}

?>
<br>
</body>
</html>