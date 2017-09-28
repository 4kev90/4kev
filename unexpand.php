<?php

session_start();

include('functions.php');
include('connectToDatabase.php');

if($_GET['num'])
    $num = $_GET['num'];

//connect to database
$con = connect_to_database();

	//PRINT LAST REPLIES
            $sqlReplies = "(SELECT * FROM posts WHERE replyTo = " . $num . " ORDER BY ID DESC LIMIT 3) ORDER BY ID ASC;";
            $resReplies = mysqli_query($con, $sqlReplies);
            while($rowReplies = mysqli_fetch_assoc( $resReplies )) {
                //prepare variables
                $rowImage = "/thumbnails/" . htmlspecialchars($rowReplies['image']);
                $imageID = 'img' . $rowReplies['ID'];
                $rowName = htmlspecialchars($rowReplies['name']);
                $rowComment = htmlspecialchars($rowReplies['commento']);
                $id = $rowReplies['ID'];
                $space = str_repeat('&nbsp;', 2);  //spaces between picture and text

                //display posts
                echo '<div class="post">';

                //show picture if present
                if($rowReplies['image'])
                    echo "<img style='float:left;' class='thumbnail' id=$imageID src=$rowImage onclick='resizepic(this.id)'>";

                //PRINT POST INFO
                echo "<form action='#' method='post' style='vertical-align:top; display: inline-block';>";
                echo "<p style='padding-left:10px; padding-right:10px;'>";

                //print user logo
                if($rowReplies['isMod'] == 1)
                    echo " <span style='cursor:pointer;' title='Admin' class='adminLogo'>☯</span> ";
                else if($rowReplies['isMod'] == 2)
                    echo " <span style='cursor:pointer;' title='Mod' class='modLogo'>☯</span> ";
                else if($rowReplies['loggedIn'] == 1)
                    echo " <span style='cursor:pointer;' title='Registered User' class='userLogo'>&#9733</span> ";

                //print name
                echo "<span class='userName'><strong> ";

                if(!$rowReplies['name'])
                    echo("Anonymous");

                //print link to user profile is name is registered
                if($rowReplies['loggedIn'] == 1)
                    echo nl2br("<a href='users.php?user=$rowName'>$rowName</a>");
                else
                    echo nl2br("$rowName");

                echo "</strong></span>";

                //print date and time
                echo "<span class='info'> {$rowReplies['dateTime']}";

                //print post number
                echo " No.{$rowReplies['ID']}</span>";

                //print blue arrow
                $hiddenButton = (string)$rowReplies['ID'] . 'btn';
                echo " <a class='arrow' onclick='showButton(\"$hiddenButton\")'>▶</a>";

                //show delete button if user is a mod, else show report button
                if($isMod)
                    echo " <button id='$hiddenButton' style='display:none;' type='submit' name='delete' value='{$rowReplies['ID']}'>Delete</button>";
                else
                    echo " <button id='$hiddenButton' style='display:none;' type='submit' name='report' value='{$rowReplies['ID']}'>Report</button>";



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
                            $sql="SELECT * FROM posts WHERE ID = $postLink";
                            $res = mysqli_query($con, $sql);
                            while($row = mysqli_fetch_assoc( $res )) 
                                $linkComm = htmlspecialchars(addslashes($row['commento']));
                            $linkComm = htmlspecialchars(preg_replace("/\r\n|\r|\n/",'<br/>',$linkComm));
                            echo nl2br("<A onMouseOver=\"post_preview('$linkComm')\" onMouseOut='hide_preview()'>{$word} </A> ");
                        }
                       
                        //print original word
                        else
                            echo nl2br("$word "); 
                    }
                    echo nl2br("</span>");
                }
                echo '</p></form></div><br>';
            }


?>
