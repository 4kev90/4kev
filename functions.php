<?php

//message to display under the name of the board
$top_message ="<p></p>";

function connect_to_database() {
    $servername = "xxxx";
    $username   = "xxxx";
    $password   = "xxxx";
    $mydb       = "xxxx";
    return mysqli_connect($servername, $username, $password, $mydb);
}

function makePwd($length = 8) {
    $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString     = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function makeFileName($length = 13) {
    $characters       = '0123456789';
    $charactersLength = strlen($characters);
    $randomString     = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function checkYoutube($word) {
    if(strpos($word, 'www.youtube.com/watch') !== false) {
      //make sure that word doesn't contain multiple urls
      if(substr_count($word, 'http') == 1) {
        $randomID = rand(0, 300000);
        $randomID2= rand(0, 300000);
        echo $word;
        $word = str_replace("watch?v=","embed/", $word);
        $link = "<iframe src='$word' width='560' height='315' frameborder='0' allowfullscreen></iframe>";
        echo " [<A onclick='ytvid($randomID, $randomID2)' class='embed'>embed</A>]<br>";
        $word = "<div id=$randomID class='hidevideo'></div>";
        echo "<div style='display:none' id=$randomID2>$link</div>";
      }
    }
    if(strpos($word, 'https://youtu.be') !== false) {
      //make sure that word doesn't contain multiple urls
      if(substr_count($word, 'http') == 1) {
        $randomID = rand(0, 300000);
        $randomID2= rand(0, 300000);
        echo $word;
        $word = str_replace("youtu.be","www.youtube.com/embed", $word);
        $link = "<iframe src='$word' width='560' height='315' frameborder='0' allowfullscreen></iframe>";
        echo " [<A onclick='ytvid($randomID, $randomID2)' class='embed'>embed</A>]<br>";
        $word = "<div id=$randomID class='hidevideo'></div>";
        echo "<div style='display:none' id=$randomID2>$link</div>";
      }
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
<p style="text-align:center;" id="boardlist">
<A href="http://4kev.org/boards.php?board=random">random</A> 
<div class="seperator">|</div>
<A href="http://4kev.org/boards.php?board=technology">technology</A>
<div class="seperator">|</div>
<A href="http://4kev.org/boards.php?board=videogames">videogames</A>
<div class="seperator">|</div>
<A href="http://4kev.org/boards.php?board=music">music</A>
<div class="seperator">|</div>
<A href="http://4kev.org/boards.php?board=anime">anime</A>
<div class="seperator">|</div>
<A href="http://4kev.org/boards.php?board=feels">feels</A>
<div class="seperator">|</div>
<A href="http://4kev.org/boards.php?board=cyberpunk">cyberpunk</A>
<div class="seperator">|</div>
<A href="http://4kev.org/boards.php?board=meta">meta</A> 

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
