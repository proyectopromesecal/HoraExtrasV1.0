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
		$_SESSION['rutaActual']="Reportes > Transporte";
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
	$query="SELECT empleado.nombre as nombre, cedula, t_cargo.nombre as cargo, t_departamento.nombre as departamento,
			formulario_transporte.fecha as fecha, pago
			FROM empleado, pago_transporte, formulario_transporte, t_cargo, t_departamento
			WHERE empleado.id = pago_transporte.id_empleado
			AND pago_transporte.id_formulario_transporte = formulario_transporte.id
			AND t_cargo.id = empleado.cargo
			AND t_departamento.id = empleado.departamento
			AND empleado.nivel =0 ";
			
	$queryTotal="SELECT REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,sum(pago)),1), '.00','') as totalpago
				FROM empleado, pago_transporte, formulario_transporte, t_cargo, t_departamento
				WHERE empleado.id = pago_transporte.id_empleado
				AND pago_transporte.id_formulario_transporte = formulario_transporte.id
				AND t_cargo.id = empleado.cargo
				AND t_departamento.id = empleado.departamento
				AND empleado.nivel =0 ";
			
	if(isset($_POST['btnReporte']))
	{
		if(isset($_SESSION['rs']) && $_POST['txtTitulo']!='')
		{
			echo "<script language='javascript'>
						window.open('trCustom.php','_blank');
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
		echo "<script language='javascript'>
					location.assign('generadorTransporte.php');
			 </script>";
	}
	else if(isset($_POST['btnBuscar']))
	{
		//concatenacion es el query personalizado
		$concatenacion = "";
		$custom = false;
		$concatenacion .= $query;
		if ($_POST['slcDepartamento'] != 'todos' && $_POST['slcFecha'] !='' && $_POST['slcMes']=='' && !is_numeric($_POST['txtEmpleado']))
		{
			$concatenacion .= " AND formulario_transporte.fecha = '{$_POST['slcFecha']}' and empleado.departamento = '{$_POST['slcDepartamento']}'
								ORDER BY {$_POST['slcOrdenar']}";
			$queryTotal .= " AND formulario_transporte.fecha = '{$_POST['slcFecha']}' and empleado.departamento = '{$_POST['slcDepartamento']}'
								ORDER BY {$_POST['slcOrdenar']}";
			$custom = true;
		}
		else
		{
			if($_POST['slcFecha'] !='' && $_POST['slcFecha2'] !='')
			{
				$concatenacion .= " and formulario_transporte.fecha BETWEEN '{$_POST['slcFecha']}' and '{$_POST['slcFecha2']}' ";
				$queryTotal .= " and formulario_transporte.fecha BETWEEN '{$_POST['slcFecha']}' and '{$_POST['slcFecha2']}' ";
				$custom = true;
			}
			if ($_POST['slcDepartamento'] != 'todos')
			{
				$concatenacion .= " and empleado.departamento = '{$_POST['slcDepartamento']}'";
				$queryTotal .= " and empleado.departamento ='{$_POST['slcDepartamento']}'";
				$custom = true;
			}
			if($_POST['txtEmpleado']!='')
			{
				if(is_numeric($_POST['txtEmpleado']))
				{
					$concatenacion .= " and empleado.cedula = '{$_POST['txtEmpleado']}'";
					$queryTotal .= " and empleado.cedula = '{$_POST['txtEmpleado']}'";
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
				$concatenacion .= " and MONTH(formulario_transporte.fecha) = {$columna[1]} and YEAR(formulario_transporte.fecha) = {$columna[0]}";
				$queryTotal .= " and MONTH(formulario_transporte.fecha) = {$columna[1]} and YEAR(formulario_transporte.fecha) = {$columna[0]}";
				$custom = true;
			}
			if ($custom==false) {
				$fecha=date('Y-m-d');
				$concatenacion.=" AND fecha='{$fecha}'
						group by nombre, cedula, cargo, departamento";
				$queryTotal.=" AND fecha='{$fecha}'";
			}
			if ($_POST['slcOrdenar'])
			{
				$concatenacion .= " ORDER BY {$_POST['slcOrdenar']} ";
				$custom = true;					
			}	

		}	
		$rscustom = sqlsrv_query($_SESSION['con'],$concatenacion, $params, $options);
		$rsTotal = sqlsrv_query($_SESSION['con'],$queryTotal, $params, $options);
		$_SESSION['rs'] =$rscustom;	
		//echo $concatenacion;
	}
}
?>

<html>
	<head>
		<title>Reportes de Transporte</title>
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
				window.open('reporteTransporte.php','_blank');
			}
		</script>
	</head>
	<body>
		<?php include("../menu.html");?>
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method='post' action='generadorTransporte.php'>
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
										<input type='text' name='txtTitulo' class="form-control" placeholder="Ej: Reporte de Transporte 2013">
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
										<button class="btn btn-default btn-block" type="submit" onclick='abrirConsolidado()' value="">Consolidado</button>
									</div><br><br>
									<div class="form-group">
										<button class="btn btn-default btn-block" type='submit' name='btnReporte' value=''>Crear Reporte</button>
									</div><br><br>
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
											<th>Nombre</th> <th>Cedula</th><th>Cargo</th><th>Departamento</th><th>Fecha</th><th>Pago</th>	
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
															$datos[] = $fila['nombre'].";".$fila['cedula'].";".$fila['cargo'].";".$fila['departamento'].";".$fila['fecha']->format('d/m/Y').";".$fila['pago'];								
															echo "<tr >
																		<td><input id='txt' style='width: 100%;' type='text' name='txtNombre' value='{$fila['nombre']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtCedula' value='{$fila['cedula']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtCargo' value='{$fila['cargo']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtDepartamento' value='{$fila['departamento']}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtFecha' value='{$fila['fecha']->format('d/m/Y')}' readonly></input></td>
																		<td><input id='txt' style='width: 100%;' type='text' name='txtPago' value='{$fila['pago']} RD$' readonly></input></td>
																 </tr>";
														}
														
														if($rsTotal)
														{
															$fila = sqlsrv_fetch_array($rsTotal, SQLSRV_FETCH_ASSOC);
															echo "<tr ><td colspan=5>Total:</td><td><input id='txt' style='width: 100%;' type='text' name='txtTotalPago' value='{$fila['totalpago']} RD$' readonly></input></td></tr>";
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