<?php

$reason = $_GET['reason'];
$date1 = $_GET['date1'];
$date2 = $_GET['date2'];

echo "<br><br><center><h1>YOU'RE BANNED</h1>";
echo "<br>Reason: ";
echo $reason;
echo "<br>Issued on: ";
echo $date1;
echo "<br>Expires the: ";
echo $date2;
echo "</center>";
?>