<?php
    session_start();

    require_once 'vendor/autoload.php';

    $access_token = $_SESSION['access_token'];

    $client = new Google_Client();

    session_destroy();
    header('location: login.php');
    ?>