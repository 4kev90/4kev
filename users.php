<?php

session_start();

include('functions.php');
include('connectToDatabase.php');

//connect to database
$con = connect_to_database();

if($_GET['user']) 
    $userName = $_GET['user'];

?>

<HTML>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="/style.css?v=<?=time();?>">
<?php
	$style = $_COOKIE["style"];
    if($style != 'cyber')
        echo '<link rel="stylesheet" type="text/css" href="/' . $style . '.css?v=' . time() . '"';
?>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="myjs.js?v=<?=time();?>" ></script>

<div class="bgImage">

<?php //print a message if a post has been reported
    if($_POST['report'])
        echo "<script> alert('Reported'); </script>";
?>

<?php boardList(); ?>

<!--BANNER-->
<center>
    <?php include "banner.php"; ?>

<br><br>
<table><td><center>
<p style="font-size:30px"><b><?php echo $userName; ?></b></p>
<?php echo $top_message; ?>
</center><td></table>
<br>
</center>
<hr>
</div>

<?php

//check table existance
$selectSQL = "SELECT * FROM posts ORDER BY ID DESC;";
$selectRes = mysqli_query($con, $selectSQL);
$cont = 0;
while(($row = mysqli_fetch_assoc( $selectRes ))) {
    //show the last 50 posts
    if($cont < 50) {
        if(($row['name'] == $userName) && ($row['loggedIn'] == 1)) {
            $cont = $cont+1;
               
	        //prepare variables
	        $rowImage = "uploads/" . htmlspecialchars($row['image']);
	        $imageID = 'img' . $row['ID'];
	        $rowName = htmlspecialchars($row['name']);
	        $rowSubject = htmlspecialchars($row['subject']);
	        $rowComment = htmlspecialchars($row['commento']);
	        $id = $row['ID'];
	        $space = str_repeat('&nbsp;', 2);  //spaces between picture and text

	        //display post
			echo nl2br("<table style='margin-bottom:5px; display:inline-table;'><tr>");

					//show picture if present
					if($row['image'])
					    echo nl2br("<td style='vertical-align:top'><img class='smallpic' id=$imageID src=$rowImage onclick='resizepic(this.id)'></td>");

					//print subject
					echo nl2br("<td style='vertical-align:top'><p style='display:inline;' class='grey'><b class='yellow'>{$rowSubject}</b>");

					if($row['isMod'] == 1)
					    echo nl2br(" <font color='white'><b style='font-size:130%; cursor:pointer;' title='Admin'>☯</b></font> ");

					else if($row['isMod'] == 2)
					    echo nl2br(" <font color='red'><b style='font-size:130%; cursor:pointer;' title='Mod'>☯</b></font> ");

					else if($row['loggedIn'] == 1)
					    echo nl2br(" <font color='orange'><b style='font-size:130%; cursor:pointer;' title='Registered User'>&#9733</b></font> ");

					
					echo nl2br("<font color='lawngreen'><b> ");

					//print anonymous if name is not present
					if(!$row['name'])
					    echo("Anonymous");

					//print name, date, time, post number, number of replies and link to thread
					$hiddenButton = makeFileName();
					echo nl2br("$rowName</b></font> {$row['dateTime']} No.{$row['ID']} Board: {$row['board']}  <a class='blue' onclick='showButton($hiddenButton)'>▶</a></p>");

					//show delete button if user is a mod, else show report button
		       		if($isMod)
		            	echo "  <form id='$hiddenButton' style='display:none' action='#' method='post'>
		                    <button type='submit' name='delete' value='{$row['ID']}'>Delete</button>
		                    </form>";
		        	else
		           		echo "  <form id='$hiddenButton' style='display:none' action='#' method='post'>
		                    <button type='submit' name='report' value='{$row['ID']}'>Report</button>
		                    </form>";

					//check if post is banned and echo message
					$sql2 = "SELECT * FROM bannedPosts";
					$res2 = mysqli_query($con, $sql2);
					while($row2 = mysqli_fetch_assoc($res2))
					    if($row['ID'] == $row2['post']) {
					        echo "<p style='color:red'><b>(User was banned for this post)</b></p>";
					        break;
					}

		        

					echo "<br><br>";

					//PRINT COMMENT
		        	//divide comment into lines
		        	$lines = explode("\n", $rowComment);
		 
		        	//apply redtext
			        foreach ($lines as $line) {
			            //check for redtext
			            $checkRed = htmlspecialchars_decode($line);
			            if($checkRed[0] == '>')
			                echo nl2br("<p><font color='red'>");
			            else 
			                echo nl2br("<p><font>");
		 
		        		echo $space;
		    
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
		                    	echo "$word "; 
		            	}
		        		echo nl2br("</font></p>");
		        	}
					echo "</td></tr></table><br>";
				}
			
		    }
		}

?>

<?php boardList(); ?>

</html>

