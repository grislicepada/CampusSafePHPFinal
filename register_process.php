<?php

include 'db.php';

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];

$sql = "INSERT INTO tbl_users(name,email,password)
VALUES('$name','$email','$password')";

if(mysqli_query($conn, $sql)){

    header("Location: login.php");

}else{

    echo "Registration Failed";

}

?>