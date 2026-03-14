<?php
$conn= mysqli_connect("localhost" , "root", "", "mystore");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>