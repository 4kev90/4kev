<?php

$top_message = "<p id='topMessage'></p>";

function printHead() {

    
    $style = 'cyber2';

    if($_GET['style']) {
        $style = $_GET['style'];
        setcookie('style', $style, time()+3600, '/');
    }
    else if($_COOKIE["style"])
        $style = $_COOKIE["style"];
/*
    if(!isMobileDevice())
        echo '<link rel="stylesheet" type="text/css" href="/themes/cyber2.css">';
    else
        echo '<link rel="stylesheet" type="text/css" href="/themes/mobile.css">';
*/
    if(!isMobileDevice())
        echo '<link rel="stylesheet" type="text/css" href="/cyber2.css?v=' . time() . '">';
    else
        echo '<link rel="stylesheet" type="text/css" href="/mobile.css?v=' . time() . '">';

    echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="jquery.ui.touch-punch.min.js"></script>
        <script type="text/javascript" src="/myjs.js?v=' . time() . '" ></script>';
}

function printPost($con, $isMod, $rowReplies) {

    //prepare variables
                $rowImage = "/thumbnails/" . htmlspecialchars($rowReplies['image']);
                $imageID = 'img' . $rowReplies['ID'];
                $urlID = 'url' . $rowReplies['ID'];
                $rowName = htmlspecialchars($rowReplies['name']);
                $rowComment = htmlspecialchars($rowReplies['commento']);
                $rowSubject = htmlspecialchars($rowReplies['subject']);
                $rowImageUrl = htmlspecialchars(addslashes($rowReplies['imageUrl']));  //protection against xss attack
                $rowImageUrl = str_replace(" ","", $rowImageUrl);  //protection against xss attack
                $rowImageUrl = str_replace("onerror","whatnow", $rowImageUrl);  //protection against xss attack
                $rowFileName = htmlspecialchars($rowReplies['fileName']);
                $id = $rowReplies['ID'];

                /*
                //santa hat
                if($rowReplies['image']) 
                    echo '<img class="santahat" src="/images/santahat.png">';
                */
                /*
                //valentines day
                if($rowReplies['image']) 
                    echo '<img class="santahat" src="/images/hearts.png">';
                */
                /*
                //halloween
                if($rowReplies['image']) 
                    echo '<img class="santahat" src="/images/pumpkin.png">';
                */
                /*
                // easter
                if($rowReplies['image']) 
                    echo '<img class="santahat" src="/images/eggs.png">';
                */
                //display posts
                if($rowReplies['replyTo'])
                    echo '<div class="post" id="'.$id.'">';
                else 
                    echo '<div class="post op" id="'.$id.'">';

                /*
                //show picture if present (URL)
                if($rowReplies['imageUrl'])
                    echo "<img style='float:left;' class='smallUrl' id=$urlID src=$rowImageUrl onclick='resizeUrl(this.id)'>";
                */
                //show picture if present
                /*
                if($rowReplies['image']) {
                    if (strpos($rowReplies['image'], 'mp3')) 
                        echo '<audio controls><source src="/uploads/'.$rowReplies['image'].'" type="audio/mpeg"></audio>';
                    else if (strpos($rowReplies['image'], 'webm')) 
                        echo '<video width="320" height="240" preload="metadata" controls><source src="/uploads/'.$rowReplies['image'].'" type="video/webm"></video>';
                    else if (strpos($rowReplies['image'], 'pdf')) 
                        echo '<a target="_blank" href="/uploads/'.$rowReplies['image'].'"><img style="height:150px; width:auto;" src="/images/pdflogo.png"></a>';
                    else 
                        echo "<img class='thumbnail' style='float:left;' id=$imageID src=$rowImage onclick='resizepic(this.id)'>";
                }
                */

                //show picture if present
                /*
                if($rowReplies['image']) {
                    if (strpos($rowReplies['image'], 'mp3')) 
                        echo '<audio controls><source src="/uploads/'.$rowReplies['image'].'" type="audio/mpeg"></audio>';
                    else if (strpos($rowReplies['image'], 'webm')) 
                        echo '<video width="320" height="240" preload="metadata" controls><source src="/uploads/'.$rowReplies['image'].'" type="video/webm"></video>';
                    else if (strpos($rowReplies['image'], 'pdf')) 
                        echo '<a target="_blank" href="/uploads/'.$rowReplies['image'].'"><img style="height:150px; width:auto;" src="/images/pdflogo.png"></a>';
                    else 
                        echo "<img class='thumbnail' style='float:left;' id=$imageID src=$rowImage onclick='resizepic(this.id)'>";
                }
                */
                //PRINT POST INFO
                echo "<form action='#' method='post' style='vertical-align:top; display: inline-block';>";
                echo "<p style='padding-left:10px; padding-right:10px;'>";

                //print subject
                echo "<strong><span class='subject'>{$rowSubject}</span></strong>";

                //print user logo
                if($rowReplies['isMod'] == 1)
                    echo " <span style='cursor:pointer;' title='Admin' class='adminLogo'>☯</span> ";
                else if($rowReplies['isMod'] == 2)
                    echo " <span style='cursor:pointer;' title='Mod' class='modLogo'>☯</span> ";
                else if($rowReplies['loggedIn'] == 1 && ($rowReplies['name'] == "Gogil" || $rowReplies['name'] == "Sharknado" || $rowReplies['name'] == "FishByte"))
                    //echo " <span style='cursor:pointer;' title='Superuser' class='superuser'>&#9733</span> ";
                    echo "<img title='Superuser' class='redstar' src='/images/redstar.png'>";
                else if($rowReplies['loggedIn'] == 1)
                    echo " <span style='cursor:pointer;' title='Registered User' class='userLogo'>&#9733</span> ";

                //print name
                echo "<span class='userName'><strong> ";

                if(!$rowReplies['name'])
                    echo("Anonymous");
/*
                if($rowReplies['loggedIn'] == 1) 
                    echo $rowName;
                else
                    echo nl2br("$rowName");
*/

                if($rowReplies['loggedIn'] == 1) {
                        echo $rowName;
                }
                else {
                    $rowName = wordFilter($rowName);
                    echo nl2br("$rowName");
                }

                echo "</strong></span>";

                //print date and time
                $justDate = substr($rowReplies['dateTime'], 0, 10);
                echo "<span class='info'> {$justDate}</span>";

                //print post number
                if($rowReplies['replyTo'])
                    echo " <a class='quickReply' onclick='formAction(".$rowReplies['replyTo'].")'>{$rowReplies['ID']}</a>";
                else
                    echo " <a class='quickReply' onclick='formAction(".$rowReplies['ID'].")'>{$rowReplies['ID']}</a>";
/*
                //thumbs up/downs
                echo '<a target="_blank" href="https://www.youtube.com/watch?v=68ugkg9RePc" title="Like" style="color:#004899;"> ▲ </a>';
                echo '<a target="_blank" href="https://www.youtube.com/watch?v=y6120QOlsfU" title="Dislike" style="color:red;">▼ </a>';
*/
                //print sticky logo
                if($rowReplies['sticky'])
                    echo " <img title='Sticky' src='/images/sticky.gif'>";
/*
                //print blue arrow
                $hiddenButton = (string)$rowReplies['ID'] . 'btn';
                echo " <a class='arrow' onclick='showButton(\"$hiddenButton\")'>▶</a>";

                //print red arrow
                echo " <a class='redArrow'>▶</a>";

                //show delete button if user is a mod, else show report button
                if($isMod)
                    echo " <button id='$hiddenButton' style='display:none;' type='submit' name='delete' value='{$rowReplies['ID']}'>Delete</button>";
                else
                    echo " <button id='$hiddenButton' style='display:none;' type='submit' name='report' value='{$rowReplies['ID']}'>Report</button>";
*/
                //show delete button if user is admin
                if($isMod == 1)
                    echo " <button id='deleteButton' type='submit' name='delete' value='{$rowReplies['ID']}'>[Delete]</button>";

                //links to post replies
                echo '<span class="linksToReplies">';

                if($rowReplies['bump'])
                    $x = $rowReplies['ID'];
                else
                    $x = $rowReplies['replyTo'];
                $ltrsql = "SELECT * FROM posts WHERE replyTo = " . $x . " ORDER BY ID ASC"; 
                $ltrres = mysqli_query($con, $ltrsql); 
                
                while($ltrrow = mysqli_fetch_assoc($ltrres)) {
                    $y = $ltrrow['commento'];
                    $z = $rowReplies['ID'];
                    if(strpos($y, $z) !== false)
                        //echo "<A style='text-decoration: underline;' href='#" . $ltrrow['ID'] . "' onmouseover='postPreview(event, {$ltrrow['ID']})' onmouseout='hidePostPreview()' class='postlink'>>>{$ltrrow['ID']}</A> ";
                        echo   "<A style='text-decoration: underline;' onmouseover='preview(event, ".$ltrrow['ID'].")' onmouseout='hidePostPreview()' class='postlink'>>>".$ltrrow['ID']."</A> ";
                }
                echo '</span>';
                /*
                //print url info
                if($rowReplies['imageUrl']) 
                    echo '<br>Url: <a target="_blank" href="' . $rowImageUrl . '">' . $rowImageUrl . '</a>';
                */

                /*
                //print image info
                if($rowReplies['image']) {
                    //fileSize
                    $fileSize = filesize('uploads/' . $rowReplies['image']);
                    if($fileSize > 1000000)
                        $fileSize = round($fileSize / 1000000, 2) . ' MB';
                    else
                        $fileSize = round($fileSize / 1000, 2) . ' KB';
                    //dimensions
                    list($width, $height) = getimagesize('uploads/' . $rowReplies['image']);
                    $dimensions = $width . 'x' . $height;
                    //echo '<br>File: <a target="_blank" href="/uploads/' . $rowReplies['image'] . '">' . $rowFileName . '</a> (' . $fileSize . ', ' . $dimensions . ')';
                    echo '<br>File: <a target="_blank" href="/uploads/' . $rowReplies['image'] . '">' . $rowFileName . '</a>';
                }
                */
                //check if post is banned and echo message
                $sql2 = "SELECT * FROM bannedPosts";
                $res2 = mysqli_query($con, $sql2);
                while($row2 = mysqli_fetch_assoc($res2))
                    if($rowReplies['ID'] == $row2['post']) {
                        echo "<span style='color:red'><strong>(User was banned for this post)</strong></span>";
                        break;
                    } 

                echo "<br><br>";

                //fortune
                if($rowReplies['fortune']) {
                    fortune($rowReplies['fortune']);
                    echo "<br><br>";
                }

                echo '</p>';

                //PRINT COMMENT

                echo "<p style='padding-left:10px; padding-right:10px;'>";

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

                    //divide line into words
                    $words = explode(" ", $line);
                    foreach ($words as $word) {
         
                       $word = checkYoutube($word);
                       $word = wordFilter($word);
                       if (strpos($word, '[spoiler]') !== false)
                        echo '<span class="spoiler">';
                        if (strpos($word, '[/spoiler]') !== false)
                        echo '</span>';
            
                        //if word is a link to a post, show post preview
                        $checkLink = htmlspecialchars_decode($word);
                        if($checkLink[0] == '>' && $checkLink[1] == '>') {
                            $postLink =  preg_replace("/[^0-9]/","", basename($word)); 
                            echo nl2br("<A style='text-decoration: underline;' href='#$postLink' onmouseover='preview(event, $postLink)' onmouseout='hidePostPreview()'>$word</A>");
                        }
                       
                        //print original word
                        else
                            echo nl2br("$word "); 
                    }
                    // end redtext and spoiler span
                    echo nl2br("</span></span>");
                }

                echo '</p></form></div><br>';
}

function isMobileDevice() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

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

function banner() {
    $banner = '<a href = "/index.php"><img id="banner" src = "/banners/' . rand(0, 94) . '.gif" /></a>';
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
    /*
    if(strpos(strtolower($word), 'fuck') !== false)
        $word = str_replace("fuck","FUARK",strtolower($word));
    if(strpos(strtolower($word), 'shit') !== false)
        $word = str_replace("shit","soykaf",strtolower($word));
    if(strpos(strtolower($word), 'nigger') !== false)
        $word = str_replace("nigger","brony",strtolower($word));
    if(strpos(strtolower($word), 'faggot') !== false)
        $word = str_replace("faggot","juggalo",strtolower($word));
    */
    if(strpos(strtolower($word), '[spoiler]') !== false)
        $word = str_replace("[spoiler]","<span class='spoiler'>",strtolower($word));
    if(strpos(strtolower($word), '[/spoiler]') !== false)
        $word = str_replace("[/spoiler]","</span>",strtolower($word));
    if(strpos(strtolower($word), 'trap') !== false)
        $word = str_replace("trap","fag",strtolower($word));
    /*
    if(strpos(strtolower($word), '[code]') !== false)
        $word = str_replace("[code]","<pre class='code'>",strtolower($word));
    if(strpos(strtolower($word), '[/code]') !== false)
        $word = str_replace("[/code]","</pre>",strtolower($word));
    */
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

function onlineUsers($con) {
    $ipAddr = $_SERVER['REMOTE_ADDR'];
    date_default_timezone_set('Europe/Paris');
    $date = date('d/m/Y H:i:s', time());

    //delete old user log
    $sql = "DELETE FROM onlineUsers WHERE ipAddress = '$ipAddr';";
    $res = mysqli_query($con, $sql);

    //insert new user log
    $sql = "INSERT INTO onlineUsers (ipAddress, dateTime) VALUES ('$ipAddr','$date')";
    $res = mysqli_query($con, $sql);

    //delete logs older than x minutes
    $sql = "SELECT * FROM onlineUsers";
    $res = mysqli_query($con, $sql);
    $onlineUsers = 0;
    while($row = mysqli_fetch_assoc($res)) {
        if(compareDates($row['dateTime'], $date) > 600) {
            $sql2 = "DELETE FROM onlineUsers WHERE ipAddress = '".$row['ipAddress']."'";
            $res2 = mysqli_query($con, $sql2);
        }
        else $onlineUsers++;
    }
    return $onlineUsers;
}

function topBar($con, $query, $header) {

        //<a href='/index.php'><button id='homePageButton'>Home</button></a>";
/*
        // print new thread button or reply button if user is logged in
        if(isset($_SESSION['ID'])) {
            if($query == $header)
                echo '<button id="showPostWindow" onclick="showPostWindow()">New Thread</button>';
            if(is_numeric($query))
                echo '<button id="showPostWindow" onclick="showPostWindow()">Reply</button>';
        }
*/

        if(!isset($_SESSION['ID'])) {

        // REGISTER FORM
        echo'
        <button id="showLoginWindow" onclick="showLoginWindow()">Login / Register</button>
        <br>
        <div id="registerWindow" class="draggable">
            <form id="registerForm" action= "/register.php" method="post" onsubmit="myButton.disabled = true; return true;">
                <input type="text" placeholder="Name" name="name" /><br>
                <input type="password" placeholder="Password" name="pwd" /><br>
                <input type="password" placeholder="Confirm password" name="pwd2" /><br>
                <input type="text" placeholder="Email" name="email" /><br>
                <textarea name="JS_enabled" id="JS_enabled" style="display:none"></textarea>
                <button id="registerButton" type="submit">Register</button>
            </form>
        </div>'
        ; 

        // LOGIN FORM
        echo '
            <div id="loginWindow" class="draggable">
                <form id="loginForm" action= "/login.php?op=' . $query . '&board=' . $query . '&x=' . $_SERVER['PHP_SELF'] . '" method="post" onsubmit="myButton.disabled = true; return true;">            
                    <input type="text" name="email" placeholder="Email" />
                    <input type="password" name="pwd" placeholder="Password" />
                    <button id="loginButton" type="submit">Log In</button>
                </form>
                <p>Not registered yet?</p>
                <button id="showRegisterWindow" onclick="showRegisterWindow()">Sign Up</button>
            </div>';
        }
        else {
            $sql = "SELECT * FROM users WHERE ID = " . $_SESSION['ID'];
                $res = mysqli_query($con, $sql);
                while($row = mysqli_fetch_assoc( $res ))
                    //<p style="text-height:30px; display:inline;">Logged in as <strong>' . $row["name"] . '</strong></p>
                    echo '                                                                   
                        <form id="logoutForm" action= "/logout.php?op=' . $query . '&board=' . $query . '&x=' . $_SERVER['PHP_SELF'] . '" method="post"> 
                            <button id="logoutButton">Logout (' . $row["name"] . ')</button>
                        </form>
                        <br>                 
                        ';
        }
        echo "<br>";
}

function searchForm($con) {
    echo "
    <div id='searchForm'>
        <form action='/search.php' method='post'>
            <input type='text' name='search' placeholder='Search'><br>
            <button type='submit' style='display:none'></button>
        </form>
    </div>
    ";
}

function loginForm($con, $query) {
    if(!isset($_SESSION['ID'])) {
        // LOGIN FORM AND REGISTER FORM
        echo '
        <div id="login">
        <form action= "/login.php?op=' . $query . '&board=' . $query . '&x=' . $_SERVER['PHP_SELF'] . '" method="post" onsubmit="myButton.disabled = true; return true;">
        <input type="text" name="email" placeholder="Email" />
        <input style="display:inline-block" type="password" name="pwd" placeholder="Password" />
        <button type="submit" name="myButton" style="text-align:center; height:30px">Log In</button>
        </form>
        <button id="showRegisterForm" onclick="showRegisterForm()">Sign Up</button>
        </div>

        <div id="registerForm" style="display:none; margin: 0 auto; width:200px;">
        <form action= "register.php" method="post" onsubmit="myButton.disabled = true; return true;">
        <input type="text" placeholder="Name" name="name" /><br>
        <input type="password" placeholder="Password" name="pwd" /><br>
        <input type="password" placeholder="Confirm password" name="pwd2" /><br>
        <input type="text" placeholder="Email" name="email" /><br>
        <button style="text-align:center; height:30px; width:100%" type="submit" name="myButton">Sign Up</button>
        </form></div>';
    }
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

function boardList($con) {

    $boardList = array('technology', 'programming', 'media', 'random', 'meta', );
    // DESKTOP
    if (!isMobileDevice()) {
        echo '<div id="boardlist">';
        //echo '<a href="https://www.4kev.org/index.php"><p class="boards">Home</p></a>';
        foreach($boardList as $boardName)
            echo '<a href="/boards/' . $boardName . '/"><p class="boards">' . ucfirst($boardName) . '</p></a>';
        echo '</div>';
    }
    
    // MOBILE
    else {
        echo '<select style="padding-left:30px; -webkit-appearance:none;" id="boardlist" onchange="location = this.value;">';
        echo '<option selected hidden>Boards</option>';
        //echo '<option value="https://www.4kev.org/index.php">Home</option>';
        foreach($boardList as $boardName)
            echo '<option value="/boards/' . $boardName . '/">' . ucfirst($boardName) . '</option>';
        echo '</select>';
    }
    
}

function footer($con) {
    echo '<div id="footer" style="text-align:center">';
    //OTHERS
    $sql = "SELECT * FROM hitCounter";
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res))
        $count = $row['count'];

    //TOTAL FILE SIZE
    $size = 0;
    $sql = "SELECT image FROM posts WHERE image";
    $res = mysqli_query($con, $sql);
    while($row = mysqli_fetch_assoc($res)) 
        $size += filesize('uploads/' . $row['image']);

    /*echo '<p style="display:inline">Users Online: '.onlineUsers($con).'
        <br>Users Today: '.activeUsers($con).'
        <br>Active Content: ' . round($size / 1000000) . ' MB
        <br>4kev@protonmail.com
        </p>';
    */

    echo '<p style="display:inline">Users Online: '.onlineUsers($con).'
        <br>Active Content: ' . round($size / 1000000) . ' MB
        <br>4kev@protonmail.com
        </p>';
    
/*
    //THEMES

         | <a href="?style=cyber">Cyber</a>
         | <a href="?style=windows95">Windows95</a>
         | <a href="?style=modern">Modern</a>

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


function foo($n) {
    if($n==0) 
        return 3;
    else
        return 1 + foo($n - 1);
}







?>