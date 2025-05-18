<?php

function connection(){ 

$host = "localhost";
$username = "root";
$password = "";
$database = "quizzleberry7";

$con = new mysqli ($host, $username, $password, $database);

if($con->connect_error){
    echo $con->connect_error;
}
else{
    return $con;
}
}
