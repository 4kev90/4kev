<?php

session_start();
session_destroy();

if (isset($_COOKIE['keepName'])) {
    unset($_COOKIE['keepName']);
    setcookie('keepName', '', time() - 3600, '/'); // empty value and old timestamp
}

$redirect = $_GET['x'];
$redirect2 = $_GET['op'];
$redirect3 = $_GET['board'];



header("Location: $redirect" . "?op=" . $redirect2 . "&board=" . $redirect3);
die;
?>