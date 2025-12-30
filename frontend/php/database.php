<?php

$DB_SERVER = "localhost"; 
$DB_USER = "root"; 
$DB_PASS = ""; 
$DB_NAME = "jobfit1"; 

try
{
$dbh = new PDO("mysql:host=".$DB_SERVER.";dbname=".$DB_NAME, $DB_USER, $DB_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
}
catch (PDOException $e)
{
exit("Error: " . $e->getMessage());
}


$con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);

if (mysqli_connect_error())
{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

?>