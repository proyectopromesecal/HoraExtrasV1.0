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

$domain = $_SERVER['HTTP_HOST'];  
$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
$includes = $_SESSION['m']->obtenerIncludes($url);

$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
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
	$query="SELECT empleado.id as id, empleado.nombre as nombre, cedula, t_cargo.nombre as cargo, t_departamento.nombre as departamento, convert(varchar, horario.fecha, 103) as fecha,
			convert(varchar,horadeentrada, 108) as horadeentrada,
			convert(varchar,horadesalida, 108) as horadesalida, convert(varchar,tiempo_extra, 108) as tiempo_extra, pago, sueldo
			FROM empleado, horario, historial_empleado, t_cargo, t_departamento, solicitudhe, solicitudes_autorizadas
			WHERE empleado.id = horario.id_empleado
			AND t_cargo.id = empleado.cargo
			AND t_departamento.id = empleado.departamento
			and empleado.id = historial_empleado.id_empleado
			and horario.id = historial_empleado.id_horario
			and solicitudhe.id = historial_empleado.id_she
			and solicitudhe.id = solicitudes_autorizadas.id_solicitud
			and solicitudes_autorizadas.autorizado =1 ";

	$queryTotal="SELECT REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,sum(pago)),1), '.00','') as totalpago
				FROM empleado, horario, historial_empleado, solicitudhe, solicitudes_autorizadas
				WHERE empleado.id = horario.id_empleado
				and empleado.id = historial_empleado.id_empleado
				and horario.id = historial_empleado.id_horario 
				and solicitudhe.id = historial_empleado.id_she
				and solicitudhe.id = solicitudes_autorizadas.id_solicitud
				and solicitudes_autorizadas.autorizado =1 ";

	if(isset($_POST['btnReporte']))
	{
		if(isset($_SESSION['rs']) && $_POST['txtTitulo']!='')
		{
			echo "<script language='javascript'>
						window.open('reportCustom.php','_blank');
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
		if ($_POST['slcDepartamento'] != 'todos' && $_POST['slcFecha'] !='' && $_POST['slcMes']=='' && !is_numeric($_POST['txtEmpleado']) && isset($_POST['chkferiado']))
		{
			$concatenacion .= " and solicitudhe.fecha = '{$_POST['slcFecha']}' and departamento = '{$_POST['slcDepartamento']}' and feriado = 1
								ORDER BY {$_POST['slcOrdenar']}";
			$queryTotal .= " and solicitudhe.fecha = '{$_POST['slcFecha']}' and departamento = '{$_POST['slcDepartamento']}' and feriado = 1
								ORDER BY {$_POST['slcOrdenar']}";
			$custom = true;
		}
		else
		{
			if(isset($_POST['chkferiado']))
			{
				$concatenacion .= " and feriado =1 ";
				$queryTotal .= " and feriado = 1";
				$custom = true;			
			}
			if($_POST['slcFecha'] !='' && $_POST['slcFecha2'] !='')
			{
				$concatenacion .= " and solicitudhe.fecha BETWEEN '{$_POST['slcFecha']}' and '{$_POST['slcFecha2']}' ";
				$queryTotal .= " and solicitudhe.fecha BETWEEN '{$_POST['slcFecha']}' and '{$_POST['slcFecha2']}' ";
				$custom = true;
			}
			if ($_POST['slcDepartamento'] != 'todos')
			{
				$concatenacion .= " and departamento = '{$_POST['slcDepartamento']}'";
				$queryTotal .= " and departamento ='{$_POST['slcDepartamento']}'";
				$custom = true;
			}
			if($_POST['txtEmpleado']!='')
			{
				if(is_numeric($_POST['txtEmpleado']))
				{
					$concatenacion .= " and cedula = '{$_POST['txtEmpleado']}'";
					$queryTotal .= " and cedula = '{$_POST['txtEmpleado']}'";
					$custom = true;					
				}
				else
				{
					if(strstr($_POST['txtEmpleado'], 'Cedula')== false)
					{
						echo "La cedula solo debe contener numeros.";
					}
				}
			}
			if($_POST['slcMes']!= '')
			{
				$columna = explode("-",$_POST['slcMes']);
				$concatenacion .= " and MONTH(solicitudhe.fecha) = {$columna[1]} and YEAR(solicitudhe.fecha) = {$columna[0]}";
				$queryTotal .= " and MONTH(solicitudhe.fecha) = {$columna[1]} and YEAR(solicitudhe.fecha) = {$columna[0]}";
				$custom = true;
			}
			if ($custom==false) {
				$fecha=date('Y-m-d');
				$concatenacion.=" AND solicitudhe.fecha='{$fecha}'
						group by nombre, cedula, cargo, departamento, fecha";
				$queryTotal.=" AND fecha='{$fecha}'";
			}
			if ($_POST['slcOrdenar'])
			{
				$concatenacion .= " ORDER BY {$_POST['slcOrdenar']} ";
				$custom = true;					
			}		
		}
		$rsCustom = sqlsrv_query($_SESSION['con'],$concatenacion, $params, $options);
		$rsTotal = sqlsrv_query($_SESSION['con'],$queryTotal, $params, $options);
		$_SESSION['rs'] =$rsCustom;	
		//echo $concatenacion;
	}	
}
?>

<html>
	<head>
		<title>Reportes Horas Extras</title>
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
		<script language='javascript'>
			function abrirConsolidado()
			{
				window.open('reporteHoraExtra.php','_blank');
			}
		</script>
		<link rel="stylesheet" href="../css/styles.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>
	<body>
		<?php include("../menu.html");?>
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method='post' action='generadorReportes.php'>
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
										<input type='text' name='txtTitulo' class="form-control" placeholder="Ej: Reporte de Horas Extra Octubre 2013">
									</div>
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<label>Filtrar por Fecha: </label>
										<div class="form-inline">
											<input type="date" name="slcFecha" value='' class="form-control" >
											<input type="date" name="slcFecha2" value='' class="form-control" >
										</div>
									</div>
									<div class="form-group">
										<label>Filtrar por Empleado: </label>
										<input type="text" name="txtEmpleado" class="form-control" value='Cedula Num' onclick="if(this.value=='Cedula Num') this.value=''" onblur="if(this.value=='') this.value='Cedula Num' " maxlength='11'>
									</div>
									<div class="form-group">
										<label>Ordenar por: </label>
										<select name='slcOrdenar' class="form-control"><?php Manejador::obtenerOrdenamientos();?></select>
									</div>
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<label>Feriado: </label><br>
										<input type="checkbox" name="chkferiado" value='si'>
									</div>
									<div class="form-group">
										<button class="btn btn-default btn-block" type="submit" onclick='abrirConsolidado()' value="">Consolidado</button>
									</div>
									<div class="form-group">
										<button class="btn btn-default6 btn-block" type='submit' name='btnReporte' value=''>Crear Reporte</button>
									</div>
									<div class="form-group">
										<button class="btn btn-primary btn-block" type='submit' name='btnBuscar' value=''>Buscar resultados</button>
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
											<th>Fecha</th><th>Hora de Entrada</th><th>Hora de Salida</th>  
											<th>Tiempo extra</th><th>Pago</th>
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
															$datos[] = $fila['nombre'].";".$fila['cedula'].";".$fila['cargo'].";".$fila['departamento'].";".$fila['fecha'].";".$fila['horadeentrada'].";".$fila['horadesalida'].";".$fila['tiempo_extra'].";".$fila['pago'];								
															echo "<tr>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtNombre' value='{$fila['nombre']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtCedula' value='{$fila['cedula']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtCargo' value='{$fila['cargo']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtDepartamento' value='{$fila['departamento']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtFecha' value='{$fila['fecha']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtHoraEntrada' value='{$fila['horadeentrada']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtHoraSalida' value='{$fila['horadesalida']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtTiempoExtra' value='{$fila['tiempo_extra']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtPago' value='{$fila['pago']} RD$' readonly></input></td>
																 </tr>";
														}
														
														if($rsTotal)
														{
															$fila = sqlsrv_fetch_array($rsTotal, SQLSRV_FETCH_ASSOC);
															echo "<tr><td colspan=8>Total:</td><td><input id='txt' style='width: 100%;' type='text' name='txtTotalPago' value='{$fila['totalpago']} RD$' readonly></input></td></tr>";
														}
														$datos[]=$fila['totalpago'];
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