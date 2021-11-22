<?php

session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();

if($_GET['num'])
	$num = $_GET['num'];
/*
if($_GET['num']) {
    $num = $_GET['num'];
    $sql = $con->prepare("SELECT * FROM posts WHERE ID = ?");
    $sql->bind_param('i', $num);
    if($sql->execute()) {
        $result = $sql->get_result();
        while ($row = $result->fetch_assoc()) {
            if(!$row['replyTo'])
                $threadExists = 1;
        }
        if($threadExists != 1) {
            header('Location: http://4kev.org');
            die();
        }
    }
  
    //get name of the board
    $aa = "SELECT * FROM posts WHERE ID = $op";
    $bb = (mysqli_query($con, $aa) );
        while($row = mysqli_fetch_assoc( $bb ))
            $boardName = $row['board'];
}
*/
$sql = 'SELECT * FROM posts WHERE ID = ' . $num;



$res = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($res)) {

	//prepare variables
    $rowImage   = "/thumbnails/" . htmlspecialchars($row['image']);
    $rowImage   = str_replace("onerror","whatnow", $rowImage);  //protection against xss attack
    $rowName    = htmlspecialchars($row['name']);
    $rowSubject = htmlspecialchars($row['subject']);
    $rowComment = htmlspecialchars($row['commento']);

    $space = str_repeat('&nbsp;', 2);  //spaces between picture and text

            //show picture if present
            if($row['image'])
            echo "<img style='float:left;' class='thumbnail' src=$rowImage>";

            //PRINT POST INFO
            echo "<form action='#' method='post' style='vertical-align:top; display: inline-block';>";
            echo "<p style='padding-left:10px;'>";

            //print subject
            echo "<strong><span class='subject'>{$rowSubject}</span></strong>";

            //print user logo
            if($row['isMod'] == 1)
                echo " <span style='cursor:pointer;' title='Admin' class='adminLogo'>☯</span> ";
            else if($row['isMod'] == 2)
                echo " <span style='cursor:pointer;' title='Mod' class='modLogo'>☯</span> ";
            else if($row['name'] == 'Bot Amber')
                echo " <span style='cursor:pointer;' title='Mod' class='modLogo'>☯</span> ";
            else if($row['loggedIn'] == 1)
                echo " <span style='cursor:pointer;' title='Registered User' class='userLogo'>&#9733</span> ";

            //print name
            echo "<span class='userName'><strong> ";

            if(!$row['name'])
                echo("Anonymous");
/*
            //print link to user profile is name is registered
            if($row['loggedIn'] == 1)
                echo nl2br("<a href='users.php?user=$rowName'>$rowName</a>");
            else */
                echo nl2br("$rowName");

            echo "</strong></span>";

            //print date and time
            echo "<span class='info'> {$row['dateTime']}</span>";

            //print post number
            echo " <a class='quickReply'>{$row['ID']}</a>";

            //print blue arrow
            $hiddenButton = (string)$row['ID'] . 'btn';
            echo " <a class='arrow'>▶ </a>";

            //links to post replies
            echo '<span class="linksToReplies">';

            if($row['bump'])
                $x = $row['ID'];
            else
                $x = $row['replyTo'];
            $ltrsql = "SELECT * FROM posts WHERE replyTo = " . $x . " ORDER BY ID ASC"; 
            $ltrres = mysqli_query($con, $ltrsql); 
            
            while($ltrrow = mysqli_fetch_assoc($ltrres)) {
                $y = $ltrrow['commento'];
                $z = $row['ID'];
                if(strpos($y, $z) !== false)
                    echo "<A style='text-decoration: underline;' class='postlink'>>>{$ltrrow['ID']}</A> ";
            }

            echo '</span>';

            //check if post is banned and echo message
            $sql2 = "SELECT * FROM bannedPosts";
            $res2 = mysqli_query($con, $sql2);
            while($row2 = mysqli_fetch_assoc($res2))
                if($row['ID'] == $row2['post']) {
                    echo "<span style='color:red'><strong>(User was banned for this post)</strong></span>";
                    break;
                } 

            echo "<br><br>";

            //fortune
            if($row['fortune']) {
                fortune($row['fortune']);
                echo "<br><br>";
            }

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
	    
	            //divide line into words
	            $words = explode(" ", $line);
	            foreach ($words as $word) {

	            $word = checkYoutube($word);
	            $word = wordFilter($word);
    
                //if word is a link to a post, show post preview
                $checkLink = htmlspecialchars_decode($word);
                if($checkLink[0] == '>' && $checkLink[1] == '>') {
                    $postLink =  preg_replace("/[^0-9]/","", basename($word)); 
                    echo nl2br("<A style='text-decoration: underline;' href='#$postLink' onmouseover='postPreview(event, $postLink)' onmouseout='hidePostPreview()'>$word</A>");
                }
               
                //print original word
                else
                    echo nl2br("$word ");
            }
        echo nl2br("</span>");
        }
    echo "</p></form></div>";
    }

?>