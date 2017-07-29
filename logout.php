<?php

session_start();
session_destroy();

$redirect = $_GET['x'];
$redirect2 = $_GET['op'];
$redirect3 = $_GET['board'];

header("Location: $redirect" . "?op=" . $redirect2 . "&board=" . $redirect3);
die;
?>