<?php 
	include_once("lib/motor.php");
	
	$e = new Extractor();	
	
	$e->extraer("D:\punch\handpunch\\");

	header("Location:actualizarPunch.php");			
?>
<html>
	<head>
		<title>Actualizar</title>
	</head>
	<body>
		<center>
			<h1>Recogiendo Datos...</h1>
			<img id='loading-image' src='imagenes/ajax-loader.gif'>
			<h3>Este Proceso puede tardar unos minutos, por favor espere...</h3>
		</center>
	</body>
</html>