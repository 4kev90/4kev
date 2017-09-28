<?php

$top_message ="<p><a href='https://www.youtube.com/channel/UCro01uaJve6mGAag5aSfOeg/live'>>> Official Radio <<</a></p>";

$defaultTheme = 'cyber';

function compareDates($older, $newer) {
    $older = str_replace('/', '-', $older);
    $newer = str_replace('/', '-', $newer);
    return strtotime($newer) - strtotime($older);
}

function fortune($num) {

    switch ($num) {
        case 0:
            echo '<span style="color:#F51C6A"><b>Your fortune: Reply hazy, try again</b></span>';
            break;
        case 1:
            echo '<span style="color:#FD4D32"><b>Your fortune: Excellent Luck</b></span>';
            break;
        case 2:
            echo '<span style="color:#E7890C"><b>Your fortune: Good Luck</b></span>';
            break;
        case 3:
            echo '<span style="color:#BAC200"><b>Your fortune: Average Luck</b></span>';
            break;
        case 4:
            echo '<span style="color:#7FEC11"><b>Your fortune: Bad Luck</b></span>';
            break;
        case 5:
            echo '<span style="color:#43FD3B"><b>Your fortune: Good news will come to you by mail</b></span>';
            break;
        case 6:
            echo '<span style="color:#16F174"><b>Your fortune: （　´_ゝ`）ﾌｰﾝ </b></span>';
            break;
        case 7:
            echo '<span style="color:#00CBB0"><b>Your fortune: ｷﾀ━━━━━━(ﾟ∀ﾟ)━━━━━━&#160;!!!!</b></span>';
            break;
        case 8:
            echo '<span style="color:#0893E1"><b>Your fortune: You will meet a dark handsome stranger</b></span>';
            break;
        case 9:
            echo '<span style="color:#2A56FB"><b>Your fortune: Better not tell you now</b></span>';
            break;
        case 10:
            echo '<span style="color:#6023F8"><b>Your fortune: Outlook good</b></span>';
            break;
        case 11:
            echo '<span style="color:#9D05DA"><b>Your fortune: Very Bad Luck</b></span>';
            break;
        case 12:
            echo '<span style="color:#D302A7"><b>Your fortune: Godly Luck</b></span>';
            break;
    }
}
/*
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
                    <p>Logged in as <strong>' . $row["name"] . '</strong></p>
                    <form action= "../logout.php?op=' . $query . '&board=' . $query . '&x=' . $_SERVER['PHP_SELF'] . '" method="post" style="display:inline;">
                    <button>Log Out</button>
                    </form></div><br><br>';
    }
}
*/
function banner() {
    $banner = "<a href = 'http://4kev.org/'><img style='height:100px; width:300px;' class='banner' src = '/banners/" . rand(0, 56) . ".gif' /></a>";
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

function makeSalt($length = 8) {
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
        $idSpan = rand(0, 999999);
        $idVideo= rand(0, 999999);
        //print link and embed button
        echo '<span id='.$idSpan.'>'.$word.'</span>';
        echo " [<A onclick='ytvid($idSpan, $idVideo)' class='embed'>embed</A>]<br>";
        //return iframe
        $word = "<iframe style='display:none' id=".$idVideo." width='560' height='315' frameborder='0' allowfullscreen></iframe>";
    }

    if(strpos(strtok($word,'?'), 'https://youtu.be') !== false) {
        $idSpan = rand(0, 999999);
        $idVideo= rand(0, 999999);
        //print link and embed button
        echo '<span id='.$idSpan.'>'.$word.'</span>';
        echo " [<A onclick='ytvid($idSpan, $idVideo)' class='embed'>embed</A>]<br>";
        //return iframe
        $word = "<iframe style='display:none' id=".$idVideo." width='560' height='315' frameborder='0' allowfullscreen></iframe>";
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

function activeUsers($con) {
    $ipAddr = $_SERVER['REMOTE_ADDR'];
    date_default_timezone_set('Europe/Paris');
    $date = date('d/m/Y H:i:s', time());

    //delete old user log
    $sql = "DELETE FROM activeUsers WHERE ipAddress = '$ipAddr';";
    $res = mysqli_query($con, $sql);

    //insert new user log
    $sql = "INSERT INTO activeUsers (ipAddress, dateTime) VALUES ('$ipAddr','$date')";
    $res = mysqli_query($con, $sql);

    //delete logs older than x minutes
    $sql = "SELECT * FROM activeUsers";
    $res = mysqli_query($con, $sql);
    $activeUsers = 0;
    while($row = mysqli_fetch_assoc($res)) {
        if(compareDates($row['dateTime'], $date) > 86400) {
            $sql2 = "DELETE FROM activeUsers WHERE ipAddress = '".$row['ipAddress']."'";
            $res2 = mysqli_query($con, $sql2);
        }
        else $activeUsers++;
    }
    return $activeUsers;
}

function loginForm($con, $query) {
    if(!isset($_SESSION['ID']))
        echo '
        <button id="showLogin" style="text-align:center; height:30px;" onclick="showLogin()">Log In</button>
        <div id="login" style="display:none;">
        <form action= "/login.php?op=' . $query . '&board=' . $query . '&x=' . $_SERVER['PHP_SELF'] . '" method="post" onsubmit="myButton.disabled = true; return true;">
        <input type="text" name="email" placeholder="Email" /><br>
        <input type="password" name="pwd" placeholder="Password" /><br>
        <button type="submit" name="myButton" style="text-align:center; height:30px; width:150px">Log In</button>
        </form></div>';
    else {
        $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
            $res = mysqli_query($con, $sql);
            while($row = mysqli_fetch_assoc( $res ))
                 echo '
                    <div class="form" id="loggedIn">
                    <p style="text-height:30px">Logged in as <strong>' . $row["name"] . '</strong></p>
                    <form action= "/logout.php?op=' . $query . '&board=' . $query . '&x=' . $_SERVER['PHP_SELF'] . '" method="post" style="display:inline;">
                    <button style="text-align:center; height:30px; width:150px">Log Out</button>
                    </form>
                    </div>';
    }
}


function boardList($con, $query) {
echo '<div id="boardlist">';

//BOARDS
$boardList = array('random', 'anime',  'cyberpunk', 'development', 'feels', 'music', 'politics', 'technology', 'videogames', 'weapons', 'meta');
foreach($boardList as $boardName)
    echo '<a href="/boards/' . $boardName . '/"><p class="boards">&nbsp;' . ucfirst($boardName) . '</p></a>';

    echo '<a href="/pub.php"><p class="boards">&nbsp;Pub</p></a>';
    echo '<a href="/index.php"><p class="boards">&nbsp;Home</p></a>';

echo '</div>';
}

function footer($con) {
    echo '<div id="footer">';

    //OTHERS
    $sql = "SELECT * FROM hitCounter";
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res))
        $count = $row['count'];

    echo '<p>
        Users last 24h: '.activeUsers($con).'
        Visits: '.$count.'
        <a href="/statistics">Statistics</a>
        <a onclick="showRules()">Rules</a>
        <a href="https://github.com/federicoolivo/4kev">GitHub</a>
        </p>';

    echo '<hr>';
/*
    //THEMES
    echo '<p>
        <a href="index.php?style=cyber">Cyber</a>
        <a href="index.php?style=normie">Normie</a>
        </p>';
*/
    echo '</div>';
}
/*
        <a href="index.php?style=tomorrow">Tomorrow</a>
        <a href="index.php?style=insomnia">Insomnia</a>
        <a href="index.php?style=yotsuba">Yotsuba</a>
        <a href="index.php?style=yotsuba-b">Yotsuba-B</a>
        <a href="index.php?style=photon">Photon</a>
*/
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