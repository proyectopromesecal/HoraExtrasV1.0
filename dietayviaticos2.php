<?php 
include("lib/motor.php");

$s = new Seguridad();
if(!isset($_SESSION)){
	session_start();
}
if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0 or strcmp($s->verificarTipo(), "Asistente") ==0)
	{	
		$_SESSION['rutaActual']="Solicitudes > Listado de formularios de dieta y viaticos > Crear formulario part. 2";
		#echo '<pre>';
		#print_r($_POST);
		#echo '</pre>';
		
		#echo '<pre>';
		#print_r($_GET);
		#echo '</pre>';
		$msjError="";		
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
	if(isset($_POST['btnRefresh']))
	{
		ManejadorDietaViatico::calcularViatico($_SESSION['dvID']);
		header("Location:dietayviaticos2.php");		
	}
	if (isset($_POST['btnVolver']))
	{
		header("Location:solicitudesviaticos.php");
	}
	else if(isset($_POST['btnAgregarEmpleados']))
	{
		echo "<script>window.open('empvia.php?f={$_SESSION['dvID']}','_blank', 'width=1300, height=700');</script>";
	}
	else if(isset($_POST['btnAgregarDestinos']))
	{
		echo "<script>window.open('destinosViaticos.php?f={$_SESSION['dvID']}','_blank', 'width=1300, height=700');</script>";
	}
}
else if (isset($_GET['f']))
{
	$_SESSION['dvID'] = $_GET['f'];
}
else if (isset($_GET['edit']) && !empty($_GET['edit']))
{
	ManejadorDietaViatico::eliminarDestino($_GET['edit']);
	ManejadorDietaViatico::calcularViatico($_SESSION['dvID']);
}
?>
<html>
	<head>
		<title>Formulario Dieta y viaticos</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<style>
			input{
				display:inline-block;
				margin-left:15px;
			}
			#th2
			{
				display:inline-block;
				margin-left:15px;
				width:130px;
			}
		</style>
	</head>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<div style="width:75%"  class="tab_cadre_fixe">
				<form method="post" action="dietayviaticos2.php">		
					<div>
						<center>
							<button name="btnAgregarEmpleados" ><img src='pics/add.png'></button> Agregar Empleados &nbsp 
							<button name="btnAgregarDestinos" ><img src='pics/add.png'></button> Agregar Lugares &nbsp 						
						</center>					
					</div><br>
					
					<fieldset>
						<legend>Empleados</legend>
						<div style="width:100%;border-radius:8px;">
							<table width="100%">
								<th width="8%">Cedula</th> <th width="15%">Nombre y Apellido</th> <th width="13%">Cargo</th> <th width="5%">Concepto a pagar</th> <th width="5%">Total a pagar</th>
								<?php 
									ManejadorDietaViatico::obtenerBeneficiarios($_SESSION['dpto'], $_SESSION['dvID']);
								?>
							</table>
							<br>
						</div>					
					</fieldset><br>
					<fieldset>
						<legend>Lugares</legend>
						<div style="width:100%;border-radius:8px;">
							<center>
								<table width="70%">
									<th width="10%">Acciones</th><th width="12%">Fecha de Salida</th> <th width="13%">Fecha de Entrada</th> <th width="12%">Hora de Salida</th> <th width="12%">Hora de Entrada</th> <th width="15%">Lugar</th>
									<?php 
										$query="SELECT destinos_viaticos.id as id,fecha_entrada as fecha_entrada, fecha_salida as fecha_salida,
												hora_entrada as hora_entrada, hora_salida as hora_salida, centro_salud.nombre as lugar
												FROM destinos_viaticos, centro_salud
												WHERE id_viatico ={$_SESSION['dvID']}
												AND centro_salud.id = destinos_viaticos.lugar";
										$params = array();
										$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
										$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
										if($rs)
										{
											while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
											{
												echo "
													<tr class='tab_bg_1'>
														<td class='tab_bg_2'><a href='dietayviaticos2.php?edit={$fila['id']}'>Eliminar</a></td>
														<td class='tab_bg_2'>{$fila['fecha_entrada']->format('d/m/Y')}</td>
														<td class='tab_bg_2'>{$fila['fecha_salida']->format('d/m/Y')}</td>
														<td class='tab_bg_2'>{$fila['hora_entrada']->format('H:i:s')}</td>
														<td class='tab_bg_2'>{$fila['hora_salida']->format('H:i:s')}</td>
														<td class='tab_bg_2'>{$fila['lugar']}</td>
													</tr>
												";									
											}
										}
									?>
									<tr>
										<td colspan=6 align="center">
											<input type="submit" name="btnRefresh" value="Actualizar" class="submit"></input>
											<input type="submit" name="btnVolver" value="Volver" class="submit"></input>
										</td>
									</tr>
								</table>					
							</center>
						</div>					
					</fieldset>
				</form>				
			</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>