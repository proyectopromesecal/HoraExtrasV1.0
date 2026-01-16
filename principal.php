<?php 
include('lib/motor.php');

$s = new Seguridad();
if(!isset($_SESSION)){
	session_start();
}
$domain = $_SERVER['HTTP_HOST'];  
$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
$includes = $_SESSION['m']->obtenerIncludes($url);
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
		<meta charset='utf-8'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo $includes;?>
		<style type="text/css">
			#contenido{
				position: fixed;
			    top: 120px;
			    bottom: 60px;
			    left: 0;
			    right: 0;
			    overflow: auto;
			}
		</style>
		<script type="text/javascript">
			$(document).ready(function() {
				//alert($(window).width() + " " + $(window).height());
			});
		</script>
	</header>
	<body>
		<header>
			<?php include('menu.html');?>
		</header>
		<div id="contenido">
			<div class="container-fluid body-content">
				<div id="dialog" title="Aviso" style="width:800px;"></div>
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<fieldset style="width:50%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component vertical-center">
							<table class="table table-striped ">
								<thead> 
									<tr>
										<th>Solicitudes</th><th>Cantidad</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Nuevas</td><td><?php echo $nuevas;?></td>
									</tr>
									<tr>
										<td>Enviadas y pendientes</td><td><?php echo $enviadas;?></td>
									</tr>	
									<tr>
										<td>Rechazadas</td><td><?php echo $rechazadas;?></td>
									</tr>	
									<tr>
										<td>Aprobadas</td><td><?php echo $aprobadas;?></td>
									</tr>
									<tr>
										<td>Total</td><td><?php echo $total;?></td>
									</tr>
								</tbody>
							</table>					
						</fieldset>						
					</div>
				</div>		
			</div>
		</div>
		<footer>
			<?php include('footer.html');?>
		</footer>
	</body>
</html>