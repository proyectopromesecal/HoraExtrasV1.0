<?php 
include('lib/motor.php');

$s = new Seguridad();

if(!isset($_SESSION)){
	session_start();
}

if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0 or strcmp($s->verificarTipo(), "Asistente") ==0)
	{
		$_SESSION['rutaActual']="Solicitudes > Listado de formularios de dieta y viaticos ";
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
		if ($_POST['slcFiltro']=='fecha')
		{	
			$rsCustom = ManejadorDietaViatico::obtenerFormularios($_SESSION['dpto'], $_POST['slcFecha'],"");
		}
	}
	else if (isset($_POST['btnNuevo']))
	{
		header("Location:dietayviaticos.php");	
	}
	else if (isset($_POST['btnEditar']))
	{
		$t = count($_POST["check"]);
		if($t>1)
		{
			echo "<script>alert('Para editar seleccione solo 1 solicitud');</script>";	
		}
		else
		{
			foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
			{
				header("Location:dietayviaticos.php?edit={$valor}");						
			}			
		}
	}
	else if (isset($_POST['btnEliminar']))
	{
		$t = count($_POST["check"]);
		if($t>1)
		{
			echo "<script>alert('Para eliminar seleccione solo 1 solicitud');</script>";	
		}
		else
		{
			foreach($_POST["check"] as $valor)
			{
				header("Location:dietayviaticos.php?del={$valor}");						
			}			
		}		
	}
	else if (isset($_POST['btnImprimir']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para imprimir seleccione solo un formulario');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					echo "<script language='javascript'>window.open('ReporteDetalleViatico.php?f={$valor}','_blank');</script>";
					echo "<script language='javascript'>window.open('DietaViaticoPDF.php?s={$valor}','_blank');</script>";
					//header("Location:DietaViaticoPDF.php?s={$valor}");
				}			
			}	
		}
		else
		{
			echo "<script>alert('Seleccione un formulario para imprimir);</script>";
		}		
	}
}
?>
<html>
	<header>
		<title>Dieta y viaticos</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</header>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<center></br>
				<fieldset style="width:60%;border-radius:8px;"><br>
					<form method="post" action="solicitudesViaticos.php">
						<div style="width:100%;">
							<button type="submit" name="btnImprimir" title="Imprimir"><img src='pics/print.png'></button> &nbsp 
							<button type="submit" name="btnNuevo" title="Agregar Nueva Solicitud"><img src='pics/add.png' ></button> &nbsp 
							<button type="submit" name="btnEditar" title="Editar"><img src='pics/edit.png'></button> &nbsp 
							<button type="submit" name="btnEliminar" title="Eliminar"><img src='pics/delete.png' width="16" height="16"></button>&nbsp 
							<select name="slcFiltro"><option value='fecha'>Fecha de Creacion</option></select> &nbsp <b>ES</b> &nbsp 
							<input type="date" name="slcFecha" value=''></input>&nbsp 
							<button type="submit" name="btnBuscar" class="submit">Buscar</button>
						</div>
						<br>
						<div class='center'>
							<table class='tab_cadre_fixe' style="width:72%;">
								<tr class='tab_bg_2'>
									<th width="10%">Seleccion</th><th width="20%">No.Solicitud</th><th width="20%">Fecha de creacion</th><th width="20%">Cantidad de empleados</th>
								</tr>
								<?php 
									$rs;
									if(strstr($_SESSION['tipo'], "SuperAdmin"))
									{
										$rs = ManejadorDietaViatico::obtenerFormularios($_SESSION['dpto'],"","");
									}
									else
									{
										if(!empty($rsCustom))
										{
											$rs = $rsCustom;
										}
										else
										{
											$rs =ManejadorDietaViatico::obtenerFormularios($_SESSION['dpto'],"","");
										}							
									}
									
									if($rs)
									{
										while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
										{
											$cantidad = ManejadorDietaViatico::cantidadEmpleados($fila['id']);
											echo "
												<tr class='tab_bg_2'>
													<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
													<td>{$fila['no_oficio']}</td>
													<td>{$fila['fecha_creacion']->format('d/m/Y')}</td>
													<td>"; echo $cantidad ."</td>
												</tr>";
										}
									}
									else
									{
										echo "<script language='javascript' type='text/javascript'>alert('Hubo un problema al cargar las solicitudes de la base de datos.')</script>";
									}
								?>
							</table>					
						</div>					
					</form>
				</fieldset>	
			</center>
		</div>
		<?php include("footer.html");?>
	</body>
</html>