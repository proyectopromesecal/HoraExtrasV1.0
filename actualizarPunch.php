<?php 
include("lib/motor.php");
if(!isset($_SESSION)){
    session_start();
}
if(isset($_SESSION['fecha1p']) && !empty($_SESSION['fecha1p']) && isset($_SESSION['fecha2p']) && !empty($_SESSION['fecha2p']))
{
	$fecha1 = $_SESSION['fecha1p'];
	$fecha2 = $_SESSION['fecha2p'];
	ManejadorPunch::cargarHorario($fecha1, $fecha2);
	ManejadorPunch::limpiarHorario();
	header('Location:index.php');
}
else
{
	echo "Hubo un problema cargando los datos.";
}
?>
<html>
	<head>
		<title>Actualizando Datos</title>
	</head>
	<body>
		<center>
			<h1>Actualizando las Tablas...</h1>
			<img src="imagenes/ajax-loader.gif">
			<h3>Este Proceso puede tardar unos minutos, por favor espere...</h3>		
		</center>
	</body>
</html>