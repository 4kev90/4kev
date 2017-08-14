<?php

$top_message ="<p>Work in progress</p>";

function loginBar($con, $query) {
    if(!isset($_SESSION['ID']))
        echo '
        <button id="showLogin" style="width:100px; text-align:center; height:30px;" onclick="showLogin()">Login</button>
        <div id="login" style="display:none; width:200px; margin: 0 auto;">
        <form action= "../login.php?op=' . $query . '&board=' . $query . '&x=' . $_SERVER['PHP_SELF'] . '" method="post" onsubmit="myButton.disabled = true; return true;">
        <input type="text" name="email" placeholder="Email" /><br>
        <input type="password" name="pwd" placeholder="Password" /><br>
        <button type="submit" name="myButton" style="text-align:center; height:30px; width:100%">Log In</button>
        </form><br></div>';
    else {
        $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
            $res = mysqli_query($con, $sql);
            while($row = mysqli_fetch_assoc( $res ))
                 echo '
                    <div class="form">
                    <p style="display:inline">Logged in as <strong>' . $row["name"] . '</strong></p>
                    <form action= "../logout.php?op=' . $query . '&board=' . $query . '&x=' . $_SERVER['PHP_SELF'] . '" method="post" style="display:inline;">
                    <button>Log Out</button>
                    </form></div><br><br>';
    }
}

function banner() {
    $banner = "<a href = 'http://4kev.org/'><img class='banner' src = '/banners/" . rand(0, 46) . ".gif' /></a>";
    echo $banner;
}

function makePwd($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function makeFileName($length = 13) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function checkYoutube($word) {

    if(strpos(strtok($word,'?'), 'www.youtube.com/watch') !== false) {
        $randomID = rand(0, 300000);
        $randomID2= rand(0, 300000);
        echo $word;
        $word = str_replace("watch?v=","embed/", $word);
        $link = "<iframe src='$word' width='560' height='315' frameborder='0' allowfullscreen></iframe>";
        echo " [<A onclick='ytvid($randomID, $randomID2)' class='embed'>embed</A>]<br>";
        $word = "<div id=$randomID class='hidevideo'></div>";
        echo "<div style='display:none' id=$randomID2>$link</div>";
    }
    if(strpos(strtok($word,'?'), 'https://youtu.be') !== false) {
        
        $randomID = rand(0, 300000);
        $randomID2= rand(0, 300000);
        echo $word;
        $word = str_replace("youtu.be","www.youtube.com/embed", $word);
        $link = "<iframe src='$word' width='560' height='315' frameborder='0' allowfullscreen></iframe>";
        echo " [<A onclick='ytvid($randomID, $randomID2)' class='embed'>embed</A>]<br>";
        $word = "<div id=$randomID class='hidevideo'></div>";
        echo "<div style='display:none' id=$randomID2>$link</div>";
    }
    return $word;
}

function wordFilter($word) {
    if(strpos(strtolower($word), 'nigger') !== false)
        $word = str_replace("nigger","brony",strtolower($word));
    if(strpos(strtolower($word), 'faggot') !== false)
        $word = str_replace("faggot","juggalo",strtolower($word));
 
    return $word;
}


function boardList($con) {
echo '<div class="boardlist">
<p style="text-align:center;" class="boards">| ';

$boardList = array('random', 'anime', 'cyberpunk', 'development', 'feels', 'music', 'politics', 'technology', 'videogames', 'meta');

foreach($boardList as $boardName)
    echo '<a class="boards" href="boards.php?board=' . $boardName . '">' . $boardName . '</a> | ';

/*
$sql = 'SELECT boardName FROM boards ORDER BY boardName ASC';
$res = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($res)) {
    $boardName = $row['boardName'];
    if($boardName != 'test')
        echo '<a href="boards.php?board=' . $boardName . '">' . $boardName . '</a> | ';
}*/

echo '
</p>
</div>
<div class="topSpacing"></div>
';
}


function my_hash_equals($str1, $str2) {
    if(strlen($str1) != strlen($str2)) {
      return false;
    } else {
      $res = $str1 ^ $str2;
      $ret = 0;
      for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
      return !$ret;
    }
}

?>