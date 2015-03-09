<?php 
include("../lib/motor.php");
if(!isset($_SESSION)){
	session_start();
}
$s = new Seguridad();
if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0 or strcmp($s->verificarTipo(), "Asistente") ==0)
	{	
		$_SESSION['rutaActual']="Reportes > Reporte de hora extra Secretaria";
	}
	else
	{
		header("Location:index.php");
	}
}
else
{
	header('Location:Login.php');
}
if($_POST)
{

	if(isset($_POST['btnReporte']))
	{
		header("Location:HESecretaria.php?fi={$_POST['fechaInicio']}&ff={$_POST['fechaFinal']}");
	}
}
?>
<html>
	<head>
		<title>Reporte de Transporte</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
	</head>
	<body class="ext-webkit ext-chrome">
		<?php include("../menu.html");?>
			<div id="page">
				<form method="post" action="reporteHESecretaria.php">
					<center>
						<input type="date" name="fechaInicio" required></input> - <input type="date" name="fechaFinal" required></input>
						<input type="submit" value="Generar reporte" name="btnReporte" class="submit" style="width:200px"></input>						
					</center>
				</form>			
			</div>
		<?php include("../footer.html");?>
	</body>
</html>