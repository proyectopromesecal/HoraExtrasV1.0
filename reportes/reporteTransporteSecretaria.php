<?php 
include("../lib/motor.php");
if(!isset($_SESSION)){
	session_start();
}
$s = new Seguridad();
if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0  or strcmp($s->verificarTipo(), "Asistente") ==0)
	{	
		$_SESSION['rutaActual']="Reportes > Transporte Secretaria";
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
		echo "<script>window.open('transporteSemanalSecretaria.php?fi={$_POST['fechaInicio']}&ff={$_POST['fechaFinal']}');</script>";
		/*
		$text=ManejadorTransporte::validarHorasExtra($_POST['fechaInicio'], $_POST['fechaFinal'], $_SESSION['usuario']);
		if(empty($text))
		{
			echo "<script>window.open('transporteSemanalSecretaria.php?fi={$_POST['fechaInicio']}&ff={$_POST['fechaFinal']}');</script>";
		}
		else
		{
			echo "<script>alert('No puedes continuar. {$text}');</script>";
		}
		*/
		
		//header("Location:transporteSemanalSecretaria.php?fi={$_POST['fechaInicio']}&ff={$_POST['fechaFinal']}");
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
				<form method="post">
					<center>
						<input type="date" name="fechaInicio" required></input> - <input type="date" name="fechaFinal" required></input>
						<input type="submit" value="Generar reporte" name="btnReporte" class="submit" style="width:200px"></input>						
					</center>
				</form>			
			</div>
		<?php include("../footer.html");?>
	</body>
</html>