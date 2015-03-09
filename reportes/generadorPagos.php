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
		$_SESSION['rutaActual']="Reportes > Pagos Realizados";
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
	global $queryTotal;
	global $rsTotal;
	$tipo = $_POST['slcTipo'];
	switch($tipo)
	{
		case "he":
			$query="SELECT solicitudhe.departamento as departamento, tipo, solicitudhe.noOficio as no_oficio, solicitudhe.fecha_creacion as fecha_creacion, horasextra_pagadas.fecha as fecha_pago
					FROM solicitudhe, horasextra_pagadas, solicitudes_autorizadas
					WHERE solicitudhe.id = horasextra_pagadas.id_solicitud
					AND solicitudhe.id = solicitudes_autorizadas.id_solicitud
					AND tipo='HoraExtra'
					AND autorizado =1 ";
			break;
			
		case "tr":
			$query="SELECT formulario_transporte.departamento as departamento, 'Transporte' as tipo, formulario_transporte.no_oficio as no_oficio, formulario_transporte.fecha_creacion as fecha_creacion, transporte_pagado.fecha as fecha_pago
					FROM solicitudhe, solicitudes_autorizadas, formulario_transporte, transporte_pagado, horaextra_transporte
					WHERE formulario_transporte.id = transporte_pagado.id_formulario_transporte
					AND horaextra_transporte.id_formulario_transporte = formulario_transporte.id
					AND horaextra_transporte.id_solicitudhe = solicitudhe.id
					AND solicitudes_autorizadas.id_solicitud = solicitudhe.id
					AND tipo =  'HoraExtra'
					AND autorizado =1 ";
			break;
		
		case "dv":
			$query="SELECT dietaviatico.departamento as departamento, tipo, dietaviatico.no_oficio as no_oficio, dietaviatico.fecha_creacion as fecha_creacion, viatico_pagado.fecha as fecha_pago
					FROM dietaviatico, viatico_pagado, solicitudes_autorizadas
					WHERE dietaviatico.id = viatico_pagado.id_dietaviatico
					AND dietaviatico.id = solicitudes_autorizadas.id_solicitud
					AND tipo='Viatico'
					AND autorizado =1 ";
			break;
	}
	if(isset($_POST['btnReporte']))
	{
		if(isset($_SESSION['rs']) && $_POST['txtTitulo']!='')
		{
			echo "<script language='javascript'>
						window.open('pagCustom.php','_blank');
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
		
		if($_POST['slcFechaC'] !='')
		{
			switch($_POST['slcTipo'])
			{
				case "he":
					$concatenacion .= " and solicitudhe.fecha_creacion ='{$_POST['slcFechaC']}' ";
				break;
				
				case "tr":
					$concatenacion .= " and formulario_transporte.fecha_creacion ='{$_POST['slcFechaC']}' ";
				break;
				
				case "dv":
					$concatenacion .= " and dietaviatico.fecha_creacion ='{$_POST['slcFechaC']}' ";
				break;
			}
			$custom = true;
		}
		
		if($_POST['slcFechaP'] !='')
		{
			switch($_POST['slcTipo'])
			{
				case "he":
					$concatenacion .= " and horasextra_pagadas.fecha ='{$_POST['slcFechaP']}' ";
				break;
				
				case "tr":
					$concatenacion .= " and transporte_pagado.fecha ='{$_POST['slcFechaP']}' ";
				break;
				
				case "dv":
					$concatenacion .= " and viatico_pagado.fecha ='{$_POST['slcFechaP']}' ";
				break;
			}
			$custom = true;
		}
		
		if ($_POST['slcDepartamento'] != 'todos')
		{
			$concatenacion .= " and departamento = '{$_POST['slcDepartamento']}'";
			$custom = true;
		}
		if($_POST['slcMes']!= '')
		{
			$columna = explode("-",$_POST['slcMes']);
			switch($_POST['slcTipo'])
			{
				case "he":
					$concatenacion .= " and MONTH(solicitudhe.fecha_creacion) = {$columna[1]} and YEAR(solicitudhe.fecha_creacion) = {$columna[0]} ";
				break;
				
				case "tr":
					$concatenacion .= " and MONTH(formulario_transporte.fecha_creacion) = {$columna[1]} and YEAR(formulario_transporte.fecha_creacion) = {$columna[0]} ";
				break;
				
				case "dv":
					$concatenacion .= " and MONTH(dietaviatico.fecha_creacion) = {$columna[1]} and YEAR(dietaviatico.fecha_creacion) = {$columna[0]} ";
				break;
			}
			$custom = true;
		}
		if ($_POST['slcOrdenar'])
		{
			$concatenacion .= " ORDER BY {$_POST['slcOrdenar']} ";
			$custom = true;					
		}		
		$rscustom = sqlsrv_query($_SESSION['con'],$concatenacion, $params, $options);
		$_SESSION['rs'] =$rscustom;	
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
			background:F6F8F9;
		}
		</style>
		<link rel="stylesheet" href="../css/styles.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>
	<body>
		<?php include("../menu.html");?>
		<div id='page'>
			<form method='post' action='generadorPagos.php'>
				<div id='tabcontent' style='width:100%'>
					<div id='ext-gen91' class='x-panel-bwrap'>	
						<table width='100%' border class='tab_cadre_pager'>
							<tr>
								<td class="tab_bg_2 b" align='left' colspan=2><label>Filtrar por Departamento: </label><select style="width: 230px;" name='slcDepartamento'><?php Manejador::obtenerDepartamentos();?></select></td>
								<td class="tab_bg_2 b" align='left' ><label>Filtrar por Fecha de creacion: </label><input type="date" name="slcFechaC" value=''></input></td>
								<td class="tab_bg_2 b" align='left' ><label>Filtrar por Fecha de pago: </label><input type="date" name="slcFechaP" value=''></input></td>
							</tr>
							<tr>
								<td class="tab_bg_2 b" align='left' colspan=2><label>Filtrar por Mes: </label><input type="month" name="slcMes" value=''></input></td>	
								<td class="tab_bg_2 b" align='left' ><label>Tipo Solicitud: </label><select name='slcTipo'><option value='he'>Horas Extra</option><option value='tr'>Transporte</option><option value='dv'>Dieta y Viaticos</option></select></td>		
								<td class="tab_bg_2 b" align='left'><label>Ordenar por: </label><select name='slcOrdenar' style='width: 100;'><option value='departamento'>Departamento</option><option value='no_oficio'>No. Solicitud</option><option></option></select></td>								
							</tr>
							<tr>
								<td class="tab_bg_2 b" align='left' colspan=2><label>Titulo del Reporte: </label><input type='text' name='txtTitulo' style="width:250;" placeholder="Ej: Pagos Realizados"></input></td>	
								<td class="tab_bg_2 b" colspan=2><input style='float: right;' type='submit' name='btnReporte' value='Crear Reporte'>&nbsp &nbsp <input style='float: right;' type='submit' name='btnBuscar' value='Buscar resultados'></td>
							</tr>
						</table>
					</div>
					<br>
					<div class='center' style="overflow: auto; height:60%;">	
						<table style="width:75%;" border='1' class='tab_cadre_fixe'>
							<th width="20%">Departamento</th><th width="15%">Tipo de Solicitud</th><th width="8%">No. Solicitud</th><th width="8%">Fecha de Creacion</th><th width="8%">Fecha de Pago</th>
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
												$datos[] = $fila['departamento'].";".$fila['tipo'].";".$fila['no_oficio'].";".$fila['fecha_creacion']->format('d/m/Y').";".$fila['fecha_pago']->format('d/m/Y');						
												echo "<tr class='tab_bg_2'>
															<td><input id='txt' style='width: 100%;' type='text' name='txtDepartamento' value='{$fila['departamento']}' readonly></input></td>
															<td><input id='txt' style='width: 100%;' type='text' name='txtTipo' value='{$fila['tipo']}' readonly></input></td>
															<td><input id='txt' style='width: 100%;' type='text' name='txtNoSolicitud' value='{$fila['no_oficio']}' readonly></input></td>
															<td><input id='txt' style='width: 100%;' type='text' name='txtFechaC' value='{$fila['fecha_creacion']->format('d/m/Y')}' readonly></input></td>
															<td><input id='txt' style='width: 100%;' type='text' name='txtFechaP' value='{$fila['fecha_pago']->format('d/m/Y')}' readonly></input></td>
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