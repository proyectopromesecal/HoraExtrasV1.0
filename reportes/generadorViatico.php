<?php 
include('../lib/motor.php');

if(!isset($_SESSION)){
	session_start();
}
$s = new Seguridad();
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Viewer") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0)
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
		<style type='text/css'>
		#txt
		{
			border-width:0;
			background:F6F8F9;
		}
		</style> 
		<link rel="stylesheet" href="../css/styles.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<script src="../css/jquery-2.0.3.min.js"></script>
	</head>
	<body>
		<?php include("../menu.html");?>
		<div id='page'>
			<form method='post' action='generadorViatico.php'>
				<div id='tabcontent' style='width:100%'>
					<div id='ext-gen91' class='x-panel-bwrap'>	
						<table width='100%' border class='tab_cadre_pager'>
							<tr>
								<td class="tab_bg_2 b" align='center' colspan=2><label>Filtrar por Departamento: </label><select style="width: 230px;" name='slcDepartamento'><?php Manejador::obtenerDepartamentos();?></select></td>
								<td class="tab_bg_2 b" align='center'><label>Filtrar por Fecha de Creacion: </label><input type="date" name="slcFecha" value=''></input></td>
								<td class="tab_bg_2 b" align='center'>Filtrar por Solicitud: <select name="slcSolicitudes"><?php ManejadorDietaViatico::obtenerFormulariosSlc();?></select></td>
							</tr>	
							<tr>
								<td class="tab_bg_2 b" align='center' colspan=2><label>Filtrar por Mes: </label><input type="month" name="slcMes" value=''></input></td>
								<td class="tab_bg_2 b" align='center'><label>Filtrar por Empleado: </label><input type="text" name="txtEmpleado" value='Cedula Num' onclick="if(this.value=='Cedula Num') this.value=''" onblur="if(this.value=='') this.value='Cedula Num' " maxlength='11'></input></td>	
								<td class="tab_bg_2 b" align='center'><label>Ordenar por: </label><select name='slcOrdenar' style='width: 100;'><?php Manejador::obtenerOrdenamientosViaticos();?></select></td>								
							</tr>															
							<tr>
								<td class="tab_bg_2 b" align='center' colspan=2><label>Titulo del Reporte: </label><input type='text' name='txtTitulo' style="width:300;" placeholder="Ej: Reporte de Dieta y Viaticos"></input></td>
								<td class="tab_bg_2 b" align='center'><input type='submit' name='btnBuscar' value='Buscar resultados'></td>
								<td class="tab_bg_2 b" align='center'><input type='submit' name='btnReporte' value='Crear Reporte'></input></td>
							</tr>
						</table>
					</div>
					<br>
					<div class='center' style="overflow: auto; height:60%;">	
						<table style="width:100%;" border='1' class='tab_cadre_fixe'>
							<th>Nombre</th> <th width="9%">Cedula</th><th>Cargo</th><th>Departamento</th>
							<th width="8%">No. Solicitud</th><th width="9%">Fecha de Salida</th>
							<th width="8%">Fecha de Entrada</th><th width="8%">Hora de Salida</th>
							<th width="8%">Hora de Entrada</th>
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
												echo "<tr class='tab_bg_2'>
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
						</table>		
					</div>				
				</div>
			</form>			
		</div>
		<?php include("../footer.html");?>
	</body>
</html>