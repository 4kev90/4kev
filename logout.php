<?php

session_start();
session_destroy();

$redirect = $_GET['x'];
header("Location: $redirect");
die;
?>