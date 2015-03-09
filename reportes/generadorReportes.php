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
if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Viewer") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0)
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
	$query="SELECT empleado.id as id, empleado.nombre as nombre, cedula, t_cargo.nombre as cargo, t_departamento.nombre as departamento, convert(varchar, fecha, 103) as fecha,
			convert(varchar,horadeentrada, 108) as horadeentrada,
			convert(varchar,horadesalida, 108) as horadesalida, convert(varchar,tiempo_extra, 108) as tiempo_extra, pago, sueldo
			FROM empleado, horario, historial_empleado, t_cargo, t_departamento
			WHERE empleado.id = horario.id_empleado
			AND t_cargo.id = empleado.cargo
			AND t_departamento.id = empleado.departamento
			and empleado.id = historial_empleado.id_empleado
			and horario.id = historial_empleado.id_horario ";

	$queryTotal="SELECT REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,sum(pago)),1), '.00','') as totalpago
				FROM empleado, horario, historial_empleado
				WHERE empleado.id = horario.id_empleado
				and empleado.id = historial_empleado.id_empleado
				and horario.id = historial_empleado.id_horario ";

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
			$concatenacion .= " and fecha = '{$_POST['slcFecha']}' and departamento = '{$_POST['slcDepartamento']}' and feriado = 1
								ORDER BY {$_POST['slcOrdenar']}";
			$queryTotal .= " and fecha = '{$_POST['slcFecha']}' and departamento = '{$_POST['slcDepartamento']}' and feriado = 1
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
				$concatenacion .= " and fecha BETWEEN '{$_POST['slcFecha']}' and '{$_POST['slcFecha2']}' ";
				$queryTotal .= " and fecha BETWEEN '{$_POST['slcFecha']}' and '{$_POST['slcFecha2']}' ";
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
				$concatenacion .= " and MONTH(fecha) = {$columna[1]} and YEAR(fecha) = {$columna[0]}";
				$queryTotal .= " and MONTH(fecha) = {$columna[1]} and YEAR(fecha) = {$columna[0]}";
				$custom = true;
			}
			if ($custom==false) {
				$fecha=date('Y-m-d');
				$concatenacion.=" AND fecha='{$fecha}'
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
		<style type='text/css'>
		#txt
		{
			border-width:0;
			background:#F6F8F9;
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
		<div id='page'>
			<form method='post' action='generadorReportes.php'>
				<div id='tabcontent' style='width:100%'>
					<div id='ext-gen91' class='x-panel-bwrap'>	
						<table width='100%' border class='tab_cadre_pager'>
							<tr>
								<td class="tab_bg_2 b" align='center' colspan=2><label>Filtrar por Departamento: </label><select style="width: 230px;" name='slcDepartamento'><?php Manejador::obtenerDepartamentos();?></select></td>
								<td class="tab_bg_2 b" align='center' ><label>Filtrar por Fecha: </label><input type="date" name="slcFecha" value=''></input> - <input type="date" name="slcFecha2" value=''></input> </td>
								<td class="tab_bg_2 b" align='center'><label>Ordenar por: </label><select name='slcOrdenar' style='width: 100;'><?php Manejador::obtenerOrdenamientos();?></select></td>
							</tr>
							<tr>
								<td class="tab_bg_2 b" align='center' colspan=2><label>Filtrar por Mes: </label><input type="month" name="slcMes" value=''></input></td>
								<td class="tab_bg_2 b" align='center'><label>Filtrar por Empleado: </label><input type="text" name="txtEmpleado" value='Cedula Num' onclick="if(this.value=='Cedula Num') this.value=''" onblur="if(this.value=='') this.value='Cedula Num' " maxlength='11'></input></td>
								<td class="tab_bg_2 b" align='center' ><label>Feriado: </label><input type="checkbox" name="chkferiado" value='si'></input></td>
													
							</tr>
							<tr>
								<td class="tab_bg_2 b" align='center' colspan=2><label>Titulo del Reporte: </label><input type='text' name='txtTitulo' style="width:300;" placeholder="Ej: Reporte de Horas Extra Octubre 2013"></input></td>
								<td class="tab_bg_2 b" align='center'><button style='float:left;' type="submit" onclick='abrirConsolidado()' value="">Consolidado</button>  <button type='submit' name='btnBuscar' value=''>Buscar resultados</button></td>
								<td class="tab_bg_2 b" center ><button style='float:right;' type='submit' name='btnReporte' value=''>Crear Reporte</button></td>
							</tr>
						</table>
					</div>
					<br>
					<div class='center' style="overflow: auto; height:60%;">	
						<table style="width:100%;" border='1' class='tab_cadre_fixe'>
							<th>Nombre</th> <th width="9%">Cedula</th><th>Cargo</th><th>Departamento</th>
							<th width="8%">Fecha</th><th width="7%">Hora de Entrada</th><th width="7%">Hora de Salida</th>  
							<th width="7%">Tiempo extra</th><th width="9%">Pago</th>
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
												echo "<tr class='tab_bg_2'>
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
												echo "<tr class='tab_bg_2'><td colspan=8>Total:</td><td><input id='txt' style='width: 100%;' type='text' name='txtTotalPago' value='{$fila['totalpago']} RD$' readonly></input></td></tr>";
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
						</table>		
					</div>				
				</div>
			</form>			
		</div>
		<?php include("../footer.html");?>
	</body>
</html>