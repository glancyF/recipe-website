<?php

$servername = "localhost";
$username = "desheval";
$password = "webove aplikace";
$dbname = "desheval";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

