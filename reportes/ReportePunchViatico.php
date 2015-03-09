<?php 
include('../lib/motor.php');

if(!isset($_SESSION)){
	session_start();
}
$s = new Seguridad();
if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Viewer") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0)
	{
		$_SESSION['rutaActual']="Reportes > Punch Viatico";
		#echo '<pre>';
		#print_r($_POST);
		#echo '</pre>';
		
		#echo '<pre>';
		#print_r($_GET);
		#echo '</pre>';		
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
	if(isset($_POST['btnBuscar']))
	{
		if (!empty($_POST['slcSolicitud']))
		{
			header("Location:PunchViatico.php?f={$_POST['slcSolicitud']}");
		}
	}
}
?>
<html>
	<header>
		<title>Reportes de Punch Viaticos</title>
		<link rel="stylesheet" href="../css/styles.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</header>
	<body>
		<?php include("../menu.html");?>
		<div id='page'>
			<center></br>
				<fieldset style="width:70%;border-radius:8px;"><br>
					<form method="post" action="ReportePunchViatico.php">
						<div style="width:100%;">
							<label><b>Seleccione una Solicitud</b></label> &nbsp &nbsp 
							<select name="slcSolicitud"><?php ManejadorDietaViatico::obtenerFormulariosSlc();?></select>&nbsp 
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