<?php 
include('../lib/motor.php');

if(!isset($_SESSION)){
	session_start();
}
$s = new Seguridad();
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

$domain = $_SERVER['HTTP_HOST'];  
$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
$includes = $_SESSION['m']->obtenerIncludes($url);

if($s->verificar())
{
	$f=0;
	foreach ($_SESSION['permisos'] as $value) {
		if ($value == 'Viewer' || $value == 'SuperAdmin') {
			$f=1;
		}
	}

	if($f)
	{	
		$_SESSION['rutaActual']="Reportes > Dieta y Viaticos";
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
	global $query;
	$query="SELECT fecha_entrada, fecha_salida, convert(varchar,hora_entrada, 108) as hora_entrada, convert(varchar,hora_salida,108) as hora_salida, empleado.cedula, empleado.nombre AS nombre, t_cargo.nombre AS cargo, t_departamento.nombre AS departamento, dietaviatico.no_oficio AS solicitud
			FROM destinos_viaticos , empleado, dietaviatico, viatico_empleado, t_departamento, t_cargo
			WHERE dietaviatico.id = viatico_empleado.id_formulario
			AND dietaviatico.id = destinos_viaticos.id_viatico
			AND empleado.id = viatico_empleado.id_empleado
			AND empleado.cargo = t_cargo.id
			AND empleado.departamento = t_departamento.id ";
	if($_POST)
	{
		if(isset($_POST['btnReporte']))
		{
			if(isset($_SESSION['rs']) && $_POST['txtTitulo']!='')
			{
				echo "<script language='javascript'>
							window.open('reporteViatico.php','_blank');
					 </script>";
					 $_SESSION['titulo'] = $_POST['txtTitulo'];
			}
			else
			{
				if($_POST['txtTitulo']=='')
				{
					echo "<script language='javascript'>
							alert('Debe de ponerle un titulo al reporte.');
						</script>";
				}
				else
				{
					echo "<script language='javascript'>
							alert('Aplique los filtros correspondientes y busque los registros para poder exportarlo.');
						</script>";
				}
			}
		}
		else if(isset($_POST['btnBuscar']))
		{
			//concatenacion es el query personalizado
			$concatenacion = "";
			$custom = false;
			$concatenacion .= $query;
			if ($_POST['slcDepartamento'] != 'todos')
			{
				$concatenacion .= " and t_departamento.id = '{$_POST['slcDepartamento']}' 
									ORDER BY {$_POST['slcOrdenar']} ";
				$custom = true;
			}
			else
			{
				if ($_POST['slcDepartamento'] != 'todos')
				{
					$concatenacion .= " and t_departamento.id = '{$_POST['slcDepartamento']}' ";
					$custom = true;
				}
				if ($_POST['slcMes'] !='')
				{
					$mes = explode("-",$_POST['slcMes']);
					$concatenacion.= " 	AND month(fecha_entrada)='{$mes['1']}' ";
					$custom = true;
				}
				if ($_POST['slcFecha'] != '')
				{
					$concatenacion .= " and dietaviatico.fecha_creacion = '{$_POST['slcFecha']}' ";
					$custom = true;
				}
				if ($_POST['slcSolicitudes'] != '')
				{
					$concatenacion .= " and dietaviatico.id = '{$_POST['slcSolicitudes']}' ";
					$custom = true;
				}
				if($_POST['txtEmpleado']!='')
				{
					if(is_numeric($_POST['txtEmpleado']))
					{
						$concatenacion .= " and empleado.cedula = {$_POST['txtEmpleado']} ";
						$custom = true;					
					}
					else
					{
						if(strstr($_POST['txtEmpleado'], 'Cedula')== false)
						{
							echo "<script>alert('La cedula solo debe contener numeros.);</script>";
						}
					}
				}
				if ($_POST['slcOrdenar'])
				{
					$concatenacion .= " ORDER BY {$_POST['slcOrdenar']} ";
					$custom = true;					
				}		
			}
			$rscustom = sqlsrv_query($_SESSION['con'],$concatenacion, $params, $options);
			$_SESSION['rs'] =$rscustom;	
		}	
	}
	//echo $concatenacion;
}
?>

<html>
	<head>
		<title>Reportes Dieta y Viaticos</title>
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
	</head>
	<body>
		<?php include("../menu.html");?>
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method='post' action='generadorViatico.php'>
					<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
						<div class="row">	
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<label>Filtrar por Departamento: </label>
										<select class="form-control" name='slcDepartamento'><?php Manejador::obtenerDepartamentos();?></select>
									</div>
									<div class="form-group">
										<label>Filtrar por Mes: </label>
										<input type="month" name="slcMes" value='' class="form-control"></input>
									</div>
									<div class="form-group">
										<label>Titulo del Reporte: </label>
										<input type='text' name='txtTitulo' class="form-control" placeholder="Ej: Reporte de Viaticos 2016">
									</div>
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<label>Filtrar por Fecha de Creaci&oacute;n: </label>
										<input type="date" name="slcFecha" value='' class="form-control" >
									</div>
									<div class="form-group">
										<label>Filtrar por Empleado: </label>
										<input type="text" name="txtEmpleado" class="form-control" value='Cedula Num' onclick="if(this.value=='Cedula Num') this.value=''" onblur="if(this.value=='') this.value='Cedula Num' " maxlength='11'>
									</div>
									<div class="form-group">
										<input type='submit' name='btnBuscar' value='Buscar resultados' class="btn btn-primary btn-block">
									</div>
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<label>Filtrar por Solicitud: </label><br>
										<select name="slcSolicitudes" class="form-control"><?php ManejadorDietaViatico::obtenerFormulariosSlc();?></select>
									</div>
									<div class="form-group">
										<label>Ordenar por: </label>
										<select name='slcOrdenar' class="form-control"><?php Manejador::obtenerOrdenamientosViaticos();?></select>
									</div>
									<div class="form-group">
										<button class="btn btn-default6 btn-block" type='submit' name='btnReporte' value=''>Crear Reporte</button>
									</div>
								</div>
							</div>
						</div>
						<br>
						<div class="row">
							<div style="overflow: auto; height:60%;width: 100%;">
								<table class="table table-hover table-striped">
									<thead>
										<tr>
											<th>Nombre</th> <th>Cedula</th><th>Cargo</th><th>Departamento</th>
											<th >No. Solicitud</th><th >Fecha de Salida</th>
											<th >Fecha de Entrada</th><th>Hora de Salida</th>
											<th >Hora de Entrada</th>
										</tr>
									</thead>
									<tbody>
										<?php 
											if(isset($_SESSION['rs']))
											{
												$rs = $_SESSION['rs'];
												if($rs)
												{
													if(sqlsrv_num_rows($rs)<=0)
													{
														echo "<script language='javascript'>
														alert('No hay resultados que coincidan con su criterio de busqueda.');
														</script>";
													}
													else
													{
														while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
														{		
															$datos[] = $fila['nombre'].";".$fila['cedula'].";".$fila['cargo'].";".$fila['departamento'].";".$fila['solicitud'].";".$fila['fecha_entrada']->format('d/m/Y').";".$fila['fecha_salida']->format('d/m/Y').";".$fila['hora_entrada'].";".$fila['hora_salida'];								
															echo "<tr>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtNombre' value='{$fila['nombre']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtCedula' value='{$fila['cedula']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtCargo' value='{$fila['cargo']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtDepartamento' value='{$fila['departamento']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtSolicitud' value='{$fila['solicitud']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtFechaEntrada' value='{$fila['fecha_entrada']->format('d/m/Y')}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtFechaSalida' value='{$fila['fecha_salida']->format('d/m/Y')} ' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtHoraEntrada' value='{$fila['hora_entrada']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtHoraSalida' value='{$fila['hora_salida']}' readonly></input></td>
																 </tr>";
														}
														$_SESSION['datos'] = $datos; 	
													}
												}
												else
												{
													//echo "Hubo un error con la base de datos";
												}
											}
											else
											{
												//echo "No hay rs en sesion";
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