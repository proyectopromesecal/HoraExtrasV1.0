<?php 
include("lib/motor.php");

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
	ManejadorDietaViatico::calcularViatico($_GET['edit']);
}
?>
<html>
	<head>
		<title>Formulario Dieta y viaticos</title>
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
		<?php include("menu.html");?>
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method="post" action="dietayviaticos2.php">		
					<div class="row" style="margin-top:10px;">
						<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
							<button name="btnAgregarEmpleados" class="btn btn-primary btn-block">Agregar Empleados</button>
						</div>
						<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
							<button name="btnAgregarDestinos" class="btn btn-primary btn-block" >Agregar Lugares</button> 
						</div>				
					</div><br>
					
					<fieldset style="width:100%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
						<legend>Empleados</legend>
						<div style="width:100%;border-radius:8px;height:30%;overflow: auto;">
							<table class="table table-striped table-hover">
								<th width="8%">Cedula</th> <th width="15%">Nombre y Apellido</th> <th width="13%">Cargo</th> <th width="5%">Concepto a pagar</th> <th width="5%">Total a pagar</th>
								<?php 
									ManejadorDietaViatico::obtenerBeneficiarios($_SESSION['dvID']);
								?>
							</table>
							<br>
						</div>					
					</fieldset><br>
					<fieldset style="width:100%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
						<legend>Lugares</legend>
						<div style="width:100%;border-radius:8px;height:30%;overflow: auto;">
							<table class="table table-hover table-striped">
								<th width="10%">Acciones</th><th width="12%">Fecha de Salida</th> <th width="13%">Fecha de Entrada</th> <th width="12%">Hora de Salida</th> <th width="12%">Hora de Entrada</th> <th width="15%">Lugar</th>
								<?php 
									$query="SELECT destinos_viaticos.id as id,fecha_entrada as fecha_entrada, fecha_salida as fecha_salida,
											hora_entrada as hora_entrada, hora_salida as hora_salida, lugar as lugar
											FROM destinos_viaticos
											WHERE id_viatico ={$_SESSION['dvID']}";
									$params = array();
									$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
									$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
									if($rs)
									{
										while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
										{
											echo "
												<tr>
													<td><a href='dietayviaticos2.php?edit={$fila['id']}'>Eliminar</a></td>
													<td>{$fila['fecha_entrada']->format('d/m/Y')}</td>
													<td>{$fila['fecha_salida']->format('d/m/Y')}</td>
													<td>{$fila['hora_entrada']->format('H:i:s')}</td>
													<td>{$fila['hora_salida']->format('H:i:s')}</td>
													<td>{$fila['lugar']}</td>
												</tr>
											";									
										}
									}
								?>
							</table>
						<div class="text-center">
							<input type="submit" name="btnRefresh" value="Actualizar" class="btn btn-info"></input>
							<input type="submit" name="btnTuristico" value="Turistico" class="btn btn-danger"></input>
							<input type="submit" name="btnVolver" value="Volver" class="btn btn-warning"></input>
						</div>					
						</div>					
					</fieldset>
				</form>				
			</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>