<?php

session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();

$boardName = 'pub';

//prepare variables to insert
$name = mysqli_real_escape_string($con, $_POST['name']);
$comm = mysqli_real_escape_string($con, $_POST['comment']);
$ipAddr = $_SERVER['REMOTE_ADDR'];

setcookie('keepName', $name, time()+3600, '/');

//check if user is banned
if($comm) {
    $sql = "SELECT * FROM bannedUsers ";
    $result = (mysqli_query($con, $sql));
    while($row = mysqli_fetch_assoc( $result )) {
        if($row['ipAddress'] == $ipAddr) {
            //check if ban is expired
            $actualDate = (float)date('YmdHis', time());
            if($row['expire'] >= $actualDate) {
                //if user is still banned, send him to ban page with query string with info about ban
                $reason = $row['reason'];
                $date1 = $row['date1'];
                $date2 = $row['date2'];
                header('Location: http://4kev.org/banned.php?reason=' . $reason . '&date1=' . $date1 . '&date2=' . $date2);
                die;
            }
            
        }
    }
}

//insert data into table
if($comm) {

        $sql = "INSERT INTO pub (name, commento, ipAddress) VALUES ('$name', '$comm', '$ipAddr')";
        mysqli_query($con, $sql);

//redirect to same page
header('Location: ' . $_SERVER['PHP_SELF']);
die;
}

?>

<HTML>
<head>
<title><?php echo 'Pub'; ?></title>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<?php
    if($_COOKIE["style"]) 
        $style = $_COOKIE["style"];
    else
        $style = $defaultTheme;
    echo '<link rel="stylesheet" type="text/css" href="/themes/' . $style . '.css?v=' . time() . '">'; 
?>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="/myjs.js?v=<?=time();?>"></script>
</head>

<div class="bgImage">

    <?php boardList($con, $op); ?>

    <br>
    <div id="boardName">
    <!--BANNER-->
    <?php banner(); ?>
    <p style="font-size:30px"><b><? echo $boardName; ?></b></p>
    <?php echo $top_message; ?>
    </div>
    <br><br>

    <!--submission form-->
    <div class="form" id="form">
        <form style='display:inline;' action="#" method="post" enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">   
            <?php
                    echo '<textarea placeholder="Name" rows="1" cols="30" input type="text" name="name" />' . $_COOKIE["keepName"] . '</textarea><br>';
            ?>
            <textarea placeholder="Comment" style="width:300px; resize:both;" rows="4" cols="40" input type="text" name="comment" /></textarea><br>
            <button style="text-align:center; height:30px; width:300px" type="submit" value="Post" name="myButton">Post</button>
        </form>
    </div>
    <br><hr>
</div>

<?php
//display posts
$selectSQL = "SELECT * FROM pub ORDER BY ID DESC";
$selectRes = mysqli_query($con, $selectSQL);

echo "<div style='text-align:center;'><div class='post' style='width:80%; text-align:left;'>";
echo "<p style='padding-left:10px;'>";

while( $row = mysqli_fetch_assoc( $selectRes ) ){

    //prepare variables
    $rowID      = $row['ID'];
    $rowName    = htmlspecialchars($row['name']);
    $rowComment = htmlspecialchars($row['commento']);

            //print name
            echo "<span class='userName'><strong> ";

            if(!$row['name'])
                echo("Anonymous");
            else
                echo nl2br("$rowName");
            echo ': ';

            echo "</strong></span>";

            //PRINT COMMENT
        //divide comment into lines
        $lines = explode("\n", $rowComment);

        //apply redtext
        foreach ($lines as $line) {
            //check for redtext
            $checkRed = htmlspecialchars_decode($line);
            if($checkRed[0] == '>')
                echo nl2br("<span class='redtext'>");
            else 
                echo nl2br("<span>");

                echo nl2br("$line ");
            
        echo nl2br("</span><br>");
        }
}

echo "</p></div></div>";

?>
<br>
</body>
</html>


