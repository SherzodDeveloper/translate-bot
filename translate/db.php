<?php
	$host = 'localhost';
	$username = 'u6178_darknet';
	$password = "darknetuzb02";
	$dbname = "u6178_darknet";

	$conn = mysqli_connect($host,$username,$password,$dbname);
	if (!$conn) {
		echo "MYSQLI_ERROR\n\n" . mysqli_error($conn);
	}
	function realstring($text){
    	global $conn;
    	$res = mysqli_real_escape_string($conn,$text);
    	return $res;
    }
	$admin = "1020678098";
?>