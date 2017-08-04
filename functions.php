<?php

$top_message ="<p>Stats page: <a href='https://www.4kev.org/stats.php'>https://www.4kev.org/stats.php</a></p>";

function banner() {
    $banner = "<A href = 'http://4kev.org/'><img src = '/banners/" . rand(0, 39) . ".gif' /></A>";
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


function boardList() {
echo '<div style="clear:both; background-color:#17202a" id="boardlist">
<hr>
<p style="text-align:center;">

    <a href="boards.php?board=random">random</a> |
    <a href="boards.php?board=technology">technology</a> |
    <a href="boards.php?board=development">development</a> | 
    <a href="boards.php?board=music">music</a> | 
    <a href="boards.php?board=anime">anime</a> |
    <a href="boards.php?board=feels">feels</a> |
    <a href="boards.php?board=cyberpunk">cyberpunk</a> |
    <a href="boards.php?board=meta">meta</a> 

</p>
<hr>
</div>
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