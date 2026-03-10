<?php
$conn= mysqli_connect("localhost" , "root", "", "fzone_team");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>