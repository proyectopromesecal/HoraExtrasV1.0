<?php
if(!isset($_SESSION))
{
	session_start();
}
unset($_SESSION["usuario"]); 
unset($_SESSION["tipo"]);
unset($_SESSION["dpto"]);
unset($_SESSION["id"]);
$_SESSION = array();
session_destroy();

header ('Location:Login.php');	
?>