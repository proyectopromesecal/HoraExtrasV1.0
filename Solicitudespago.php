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
	if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0 or strcmp($s->verificarTipo(), "Asistente") ==0)
	{		
		$_SESSION['rutaActual']="Solicitudes > Pago de Horas Extra";
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
	$rsCustom;
	if(isset($_POST['btnBuscar']))
	{
		if ($_POST['slcFiltro']=='fecha2')
		{
			$rsCustom = ManejadorSolicitud::solicitudesAprobadas($_SESSION['dpto'], "",$_POST['slcFecha'], $_SESSION['usuario']);
		}
		else if ($_POST['slcFiltro']=='fecha')
		{	
			$rsCustom = ManejadorSolicitud::solicitudesAprobadas($_SESSION['dpto'], $_POST['slcFecha'],"", $_SESSION['usuario']);
		}
	}
	else if (isset($_POST['btnGenerar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para generar el pago seleccione solo 1 solicitud');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					$valores = explode("-", $valor);
					//$now = date('Y-m-d');
					//$fechaS = ManejadorSolicitud::obtenerFechaSolicitud($valores[0]);
					//$month = date("m",strtotime($fechaS->format('Y-m-d'))) +1;
					//$year = date("Y",strtotime($now));
					//$temp = $month."/"."02"."/".$year;
					//$f = new Datetime('06/02/$year');
					//echo "<script>alert('{$temp}');</script>";	
					header("Location:calculoSolicitud.php?s={$valores[0]}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione una solicitud para realizar el calculo);</script>";
		}
	}
}
?>
<html>
	<header>
		<title>Pago Horas Extra</title>
		<meta charset='utf-8'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo $includes;?>
		<style>
			#contenido{
				position: fixed;
			    top: 120px;
			    bottom: 100px;
			    left: 0;
			    right: 0;
			    overflow: auto;
			}
		</style>
	</header>
	<body>
		<header>
			<?php include("menu.html");?>
		</header>
		
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method="post" action="solicitudespago.php">
					<div class="row">
						<fieldset style="width:95%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
							<legend>Opciones y b&uacute;squeda</legend>
							<div class="row">
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<div class="form-group">
										<select name="slcFiltro" class="form-control"><option value='fecha'>Fecha de Creacion</option><option value='fecha2'>Fecha Solicitada</option></select>
									</div>
								</div>
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<div class="form-group">
										<input type="date" name="slcFecha" value='' class="form-control"></input>
									</div>
								</div>
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<div class="form-group">
										<button type="submit" name="btnBuscar" class="btn btn-primary btn-block">Buscar</button>
									</div>
								</div>
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<div class="form-group">
										<button type="submit" name="btnGenerar" class="btn btn-primary btn-block">Generar solicitud de pago</button>
									</div>
								</div>							
							</div>
							<div class='row' style="height:50%;overflow:auto;">
								<table class='table table-bordered table-striped table-hover' style="width:100%;">
									<thead>
										<tr>
											<th>Selec</th><th>No.Solicitud</th><th>Estado</th><th>Fecha de creaci&oacute;n</th><th>Fecha solicitada</th><th>Empleados Solicitados</th><th>Empleados Guardados</th><th>Monto guardado</th>
										</tr>
									</thead>
									<tbody>
										<?php 
											$rs;
											if(strstr($_SESSION['tipo'], "SuperAdmin"))
											{
												if(!empty($rsCustom))
												{
													$rs = $rsCustom;
												}	
												else
												{
													$rs =ManejadorSolicitud::solicitudesAprobadas('',"","");
												}
											}
											else
											{
												if(!empty($rsCustom))
												{
													$rs = $rsCustom;
												}
												else
												{
													$rs =ManejadorSolicitud::solicitudesAprobadas($_SESSION['dpto'],"","", $_SESSION['usuario']);
												}
											}
											if($rs)
											{
												while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
												{
													$guardados="";
													$monto=0;
													$hist = ManejadorSolicitud::obtenerHistSolicitud($_SESSION['usuario'], $fila['id']);
													if (!empty($hist)) {
														$temp = explode(";", $hist);
														$guardados = $temp[0];
														$monto= $temp[1];
													}
													$enlace="";
													$estado = ManejadorSolicitud::verEstado($fila['id']);
													$cantidad = ManejadorSolicitud::cantidadEmpleados($fila['id']);
													echo "
														<tr>
															<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}-{$estado}' readonly></input></td>
															<td>{$fila['noOficioHE']}</td>
															<td>{$estado}</td>
															<td>{$fila['fecha_creacion']->format('d/m/Y')}</td>
															<td>{$fila['fecha']->format('d/m/Y')}</td>
															<td>"; echo $cantidad ."</td>
															<td>"; echo $guardados ."</td>
															<td>"; echo $monto ." RD$</td>
														</tr>";
												}
											}
											else
											{
												echo "<script language='javascript' type='text/javascript'>alert('Hubo un problema al cargar las solicitudes de la base de datos.')</script>";
											}
										?>										
									</tbody>

								</table>
							</div>
							<br>	
							
						
						</fieldset>							
					</div>

				</form>
			</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>