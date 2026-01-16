<?php 
	include("lib/motor.php");
	if(isset($_POST['id'])=== true && !empty($_POST['id']))
	{
		$id = $_POST['id'];
		ManejadorCargo::obtenerCargo();
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<meta charset='utf-8'>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

</body>
</html>