<?php 
include('lib/motor.php');

$s = new Seguridad();
if(!isset($_SESSION)){
	session_start();
}
if($s->verificar())
{
	//var_dump($_SESSION);
}
else
{
	header('Location:Login.php');
}
$_SESSION['rutaActual']="Inicio";
$aprobadas=0;
$rechazadas=0;
$enviadas=0;
$nuevas=0;
$total=0;

$aprobadas = ManejadorSolicitud::contarHEAprobadas($_SESSION['dpto'], $_SESSION['usuario']);
$rechazadas = ManejadorSolicitud::contarHERechazadas($_SESSION['dpto'], $_SESSION['usuario']);
$enviadas = ManejadorSolicitud::contarHEPendientes($_SESSION['dpto'], $_SESSION['usuario']);
$nuevas = ManejadorSolicitud::contarHENuevas($_SESSION['dpto'], $_SESSION['usuario']);
$total = $aprobadas + $rechazadas + $enviadas + $nuevas;
?>
<html>
	<header>
		<title>Pantalla Principal</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<link rel="stylesheet" href="js/jquery-ui/jquery-ui.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<script src="js/jquery-2.1.1.js"></script>
		<script src="js/jquery-ui/jquery-ui.js"></script>
		<script type="text/javascript">
			$(function() {
			 	$(document).ready(function(){
			    	//$( "#dialog" ).dialog();
			    	//$( "#dialog" ).load('archivos/aviso.html').dialog('open');
				});
			});
		</script>
	</header>
	<body>
		<?php include("menu.html");?>
		<div id="dialog" title="Aviso" style="width:800px;">
			
		</div>
		<div id='page'>
			<center></br>
				<fieldset style="width:70%;border-radius:8px;"><br>
					<div class='center'>
						<table class='tab_cadre_fixe'>
							<tr class='tab_bg_2'>
								<th>Solicitudes</th><th>Cantidad</th>
							</tr>	
							<tr class='tab_bg_2'>
								<td class="top">Nuevas</td><td><?php echo $nuevas;?></td>
							</tr>
							<tr class='tab_bg_2'>
								<td>Enviadas y pendientes</td><td><?php echo $enviadas;?></td>
							</tr>	
							<tr class='tab_bg_2'>
								<td>Rechazadas</td><td><?php echo $rechazadas;?></td>
							</tr>	
							<tr class='tab_bg_2'>
								<td>Aprobadas</td><td><?php echo $aprobadas;?></td>
							</tr>
							<tr class='tab_bg_2'>
								<td>Total</td><td><?php echo $total;?></td>
							</tr>
						</table>					
					</div>	
				</fieldset>	
			</center>
		</div>
		<?php include("footer.html");?>
	</body>
</html>