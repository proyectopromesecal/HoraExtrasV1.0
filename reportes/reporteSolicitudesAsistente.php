<?php 
include('../lib/motor.php');
error_reporting(E_ALL ^ E_WARNING); 
error_reporting(E_ERROR | E_PARSE);

if(!isset($_SESSION)){
	session_start();
}

$s = new Seguridad();
global $query;
global $queryTotal;
global $rsTotal;
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

$domain = $_SERVER['HTTP_HOST'];  
$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
$includes = $_SESSION['m']->obtenerIncludes($url);
if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Asistente") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0)
	{	
		$_SESSION['rutaActual']="Reportes > Horas Extra";
	}
	else
	{
		header("Location:../index.php");
	}
}
else
{
	header('Location:Login.php');
}
/*
echo '<pre>';
echo print_r($_POST);
echo '</pre>';
*/

if($_POST)
{	
	$queryNG="SELECT she.id, she.noOficio, count(s.id_empleado) cantidad_e, she.fecha fecha_solicitada, she.fecha_creacion, she.usr from solicitudhe she
				inner join solicitudes s on s.id_solicitud = she.id
				inner join empleado e on e.id = s.id_empleado
				inner join solicitudes_autorizadas sa on sa.id_solicitud = she.id
				inner join usuario u on she.usr = u.usuario  
				where sa.tipo = 'HoraExtra' and sa.autorizado=1 ";

	$queryG="SELECT she.id, she.noOficio, count(hist.id_empleado) guardados, sum(hist.pago) monto_guardado, she.fecha fecha_solicitada, she.fecha_creacion, she.usr  from solicitudhe she
				inner join solicitudes s on s.id_solicitud = she.id
				inner join empleado e on e.id = s.id_empleado
				inner join horario h on h.id_empleado = e.id and h.fecha = she.fecha
				inner join historial_empleado hist on hist.id_SHE = she.id and hist.id_empleado = e.id and hist.id_horario = h.id
				inner join usuario u on she.usr = u.usuario
				WHERE ";

	if(isset($_POST['btnBuscar']))
	{
		//qng es el query personalizado
		$qng = "";
		$qg="";
		$qng .= $queryNG;
		$qg .= $queryG;
		if(!empty($_POST['slcFecha']) && !empty($_POST['slcFecha2']))
		{
			$qng .= " and she.fecha BETWEEN '{$_POST['slcFecha']}' and '{$_POST['slcFecha2']}' ";
			$qg .= " she.fecha BETWEEN '{$_POST['slcFecha']}' and '{$_POST['slcFecha2']}' ";
		}
		if (!empty($_POST['slcUsuario']))
		{
			$qng .= " and u.id = '{$_POST['slcUsuario']}' ";
			$qg .= " and u.id = '{$_POST['slcUsuario']}' ";
		}

		$qng .= " GROUP BY she.id, she.noOficio, she.fecha, fecha_creacion, she.usr ";				
		$qg .= " GROUP BY she.id, she.noOficio, she.fecha, fecha_creacion, she.usr ";

		$rsNG = sqlsrv_query($_SESSION['con'],$qng, $params, $options);
		$rsG = sqlsrv_query($_SESSION['con'],$qg, $params, $options);
		$_SESSION['rsng'] =$rsNG;	
		$_SESSION['rsg'] =$rsG;	
	}	
}
?>

<html>
	<head>
		<title>Monitor de Solicitudes</title>
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
			#contenido a{
				color: blue;
			}

		</style>
		<script language='javascript'>

		</script>

	</head>
	<body>
		<?php include("../menu.html");?>
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method='post'>
					<fieldset style="width:95%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
						<div class="row" >	
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<label>Filtrar por Usuario: </label>
										<select class="form-control" name='slcUsuario' required><?php Manejador::obtenerUsuariosSlc($_SESSION['id']);?></select>
									</div>							
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<label>Filtrar por Fecha: </label>
										<div class="form-inline">
											<input type="date" name="slcFecha" value='' required class="form-control">
											<input type="date" name="slcFecha2" value='' required class="form-control">
										</div>
									</div>							
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<button class="btn btn-primary btn-block" type='submit' name='btnBuscar' value=''>Crear Reporte</button>
									</div>							
								</div>
							</div>
						</div>
						<br>
						<div class="row" style="height:60%;overflow:auto;">	
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
								<table class='table table-hover table-striped'>
									<th>Accion</th> <th>Num. Oficio</th><th>Cantidad de Empleados</th><th>Fecha Solicitada</th><th>Fecha de Creacion</th><th>Usuario</th>
									<?php 
										if(isset($_SESSION['rsng']) && !empty($_SESSION['rsng']))
										{
											if($_SESSION['rsng'])
											{

												if(sqlsrv_num_rows($_SESSION['rsng'])<=0)
												{
													echo "<script language='javascript'>
													alert('No hay resultados que coincidan con su criterio de busqueda.');
													</script>";
												}
												else
												{
													while($fila=sqlsrv_fetch_array($_SESSION['rsng'], SQLSRV_FETCH_ASSOC))
													{		
														$temp = explode("@", $fila['usr']);
														$datos[] = $fila['noOficio'].";".$fila['cantidad_e'].";".$fila['fecha_solicitada']->format('Y-m-d').";".$fila['fecha_creacion']->format('Y-m-d').";".$temp[0];								
														echo "<tr class='tab_bg_2'>
																	<td><a href='/horasextra/solicitudPDF.php?s={$fila['id']}' target='_blank'>Ver Solicitud</a></td>
																	<td>{$fila['noOficio']}</td>
																	<td>{$fila['cantidad_e']}</td>
																	<td>{$fila['fecha_solicitada']->format('Y-m-d')}</td>
																	<td>{$fila['fecha_creacion']->format('Y-m-d')}</td>
																	<td>{$temp[0]}</td>
															 </tr>";

													}
													
													$_SESSION['datos'] = $datos; 	
												}
											}
											else
											{
												echo "Hubo un error con la base de datos";
											}
											$_SESSION['rsng']='';
										}
										else
										{
											//echo "No hay rs en sesion";
										}						
									?>
								</table>
							</div>
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
								<table class='table table-hover table-striped'>
									<thead>
										<tr>
											<th>Accion</th><th>Num. Oficio</th><th>Empleados Guardados</th><th>Monto Guardado</th><th>Fecha Solicitada</th><th>Usuario</th>
										</tr>
									</thead>
									<tbody>
										<?php 
											if(isset($_SESSION['rsg']) && !empty($_SESSION['rsg']))
											{
												if($_SESSION['rsg'])
												{
													if(sqlsrv_num_rows($_SESSION['rsg'])<=0)
													{
														echo "<script language='javascript'>
														alert('No hay resultados que coincidan con su criterio de busqueda.');
														</script>";
													}
													else
													{
														while($fila=sqlsrv_fetch_array($_SESSION['rsg'], SQLSRV_FETCH_ASSOC))
														{		
															$temp = explode("@", $fila['usr']);
															$datosg[] = $fila['noOficio'].";".$fila['guardados'].";".$fila['monto_guardado'].";".$fila['fecha_solicitada']->format('Y-m-d').";".$fila['fecha_creacion']->format('Y-m-d').";".$fila['usr'];								
															echo "<tr>
																		<td><a href='/horasextra/reportehe.php?s={$fila['id']}&usr={$fila['usr']}' target='_blank'>Ver Solicitud</a></td>
																		<td>{$fila['noOficio']}</td>
																		<td>{$fila['guardados']}</td>
																		<td>";echo number_format($fila['monto_guardado'],2,'.',','); echo"</td>
																		<td>{$fila['fecha_solicitada']->format('Y-m-d')}</td>
																		<td>{$temp[0]}</td>
																 </tr>";
														}
														
														$_SESSION['datosg'] = $datosg; 	
													}
												}
												else
												{
													echo "Hubo un error con la base de datos";
												}
												$_SESSION['rsg']='';
											}
										?>
									</tbody>
								</table>							
							</div>
						</div>				
					</fieldset>
				</form>	
			</div>		
		</div>
		<?php include("../footer.html");?>
	</body>
</html>