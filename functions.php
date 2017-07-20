<?php

//message to display under the name of the board
$top_message ="<p></p>";

function connect_to_database() {
    $servername = "xxxx";
    $username = "xxxx";
    $password = "xxxx";
    $mydb = "xxxx";
    return mysqli_connect($servername, $username, $password, $mydb);
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
    if(strpos($word, 'www.youtube.com/watch') !== false) {
        $randomID = rand(0, 300000);
        echo $word;
        echo " [<A onclick='ytvid($randomID)' class='embed'>embed</A>]";
        echo "<br>";
        $word = str_replace("watch?v=","embed/", $word);
        $word = "<iframe class='hidevideo' id='$randomID' src='$word' width='560' height='315' frameborder='0' allowfullscreen></iframe>";
    }
    if(strpos($word, 'https://youtu.be') !== false) {
        $word = str_replace("youtu.be","www.youtube.com/embed", $word);
        $word = "<iframe src='$word' width='560' height='315' frameborder='0' allowfullscreen></iframe>";
    }
    return $word;
}

function wordFilter($word) {
    if(strpos($word, 'nigger') !== false)
        $word = str_replace("nigger","brony",$word);
    elseif(strpos($word, 'NIGGER') !== false)
        $word = str_replace("NIGGER","BRONY",$word);
    elseif(strpos($word, 'faggot') !== false)
        $word = str_replace("faggot","juggalo",$word);
    elseif(strpos($word, 'FAGGOT') !== false)
        $word = str_replace("FAGGOT","JUGGALO",$word);

    return $word;
}

function boardList() {
echo '<div style="clear:both; background-color:#17202a">
<hr>
<p style="text-align:center;">

<A href="http://4kev.org/boards/random.php">random</A> |
<A href="http://4kev.org/boards/technology.php">technology</A> |
<A href="http://4kev.org/boards/politics.php">politics</A> |
<A href="http://4kev.org/boards/videogames.php">videogames</A> | 
<A href="http://4kev.org/boards/music.php">music</A> | 
<A href="http://4kev.org/boards/anime.php">anime</A> |
<A href="http://4kev.org/boards/feels.php">feels</A> |
<A href="http://4kev.org/boards/cyberpunk.php">cyberpunk</A> 

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