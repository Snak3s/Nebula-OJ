<?php
	include("script3.php");
	$_SESSION["password".$_POST["id"]]=$_POST["password"];
	if ($_POST["password"]!=getpassword($_POST["id"]))
		echo "密钥错误。";
?>
