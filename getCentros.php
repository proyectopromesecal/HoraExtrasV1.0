<?php 
	include("lib/motor.php");
	if(isset($_POST['id'])=== true && !empty($_POST['id']))
	{
		$id = $_POST['id'];
		ManejadorRegionCSP::obtenerCSP($id,0);
	}
?>