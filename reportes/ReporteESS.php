<?php 
include('../lib/motor.php');

if(!isset($_SESSION)){
	session_start();
}
$s = new Seguridad();
if($s->verificar())
{
	$_SESSION['rutaActual']="Reportes > Entrada y Salida";
}
else
{
	header('Location:Login.php');
}

if($_POST)
{
	if(isset($_POST['btnBuscar']))
	{
		if (!empty($_POST['slcFecha']))
		{
			header("Location:PunchSecretaria.php?f={$_POST['slcFecha']}");
		}
	}
}
?>
<html>
	<header>
		<title>Reportes de Entrada y Salida</title>
		<link rel="stylesheet" href="../css/styles.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</header>
	<body>
		<?php include("../menu.html");?>
		<div id='page'>
			<center></br>
				<fieldset style="width:70%;border-radius:8px;"><br>
					<form method="post" action="ReporteESs.php">
						<div style="width:100%;">
							<label><b>Seleccione una Fecha</b></label> &nbsp &nbsp 
							<input type="date" name="slcFecha" value=''></input>&nbsp 
							<button type="submit" name="btnBuscar">Generar Reporte</button>
						</div>
					</form>
				</fieldset>	
			</center>
		</div>
		<div id='footer'>
			<table width='100%'>
				<tr>
					<td class='left'>
						<span class='copyright'><a href='http://promesecal.gob.do/'>PROMESE CAL</a></span>
					</td>
					<td class='copyright'>
						<span class='copyright'>Version actual: 0.14</span>
					</td>
					<td class='right'>
						<span class='copyright'>SCHE  0.14 Copyright (C) 2013 by NiosX PromeseCal.</span>
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>