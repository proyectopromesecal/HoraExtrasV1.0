<?php 
include("lib/motor.php");

if(!isset($_SESSION)){
	session_start();
}

$s = new Seguridad();
$domain = $_SERVER['HTTP_HOST'];  
$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
$includes = $_SESSION['m']->obtenerIncludes($url);
if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0 or strcmp($s->verificarTipo(), "Asistente") ==0) 
	{	
		$_SESSION['rutaActual']="Viaticos > Asignacion de Transporte";
		$nombre ="";
		$modificacion=false;
		$modo="";			
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
	$seleccionados = array();
	$solicitud=0;
	$chofer=0;
	if(isset($_POST['btnAgregarChofer']))
	{
		if(isset($_POST['chkChofer']))
		{
			foreach($_POST["chkChofer"] as $valor)
			{
				$seleccionados[] = $valor;
				$chofer = $valor;
			}					
		}
		if (isset($_POST['chkSolicitud'])) {
			if (count($_POST['chkSolicitud'])> 1) {
				echo "<script>alert('Seleccione solo 1 solicitud');</script>";	
			}
			else
			{
				$solicitud = $_POST['chkSolicitud'][0];
			}
		}
		
		if(count($seleccionados)>0 && $solicitud !=0)
		{
			ManejadorDietaViatico::agregarEmpleados($solicitud, $seleccionados);
			ManejadorDietaViatico::asignarChofer($chofer, $solicitud);
		}
		else
		{
			echo "<script>alert('Debe seleccionar un chofer y una solicitud');</script>";	
		}
		
	}
	else if (isset($_POST['btnImprimir']))
	{
		if(isset($_POST['chkSolicitud']))
		{
			$t = count($_POST["chkSolicitud"]);
			if($t>1)
			{
				echo "<script>alert('Para imprimir seleccione solo 1 solicitud');</script>";	
			}
			else
			{
				foreach($_POST["chkSolicitud"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					echo "<script> window.open('DietaViaticoPDF.php?s={$valor}','_blank');</script>";
				}			
			}	
		}
		else
		{
			echo "<script>alert('Seleccione una solicitud para imprimir);</script>";
		}
	}
}
?>
<html>
	<head>
		<title>Asignacion de Transporte</title>
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
				<form method="post" action="asignacionTransporte.php">
					<div style="width:100%;height: 95%;margin: 0 auto;">
						<div id="izquierdo" style="width:48%;height:95%;float:left;">
							<fieldset style="border-radius:8px;border: 3px solid;" class="well bs-component">
								<legend>Choferes</legend>
								<div style="width:100%;height:90%;overflow-y: auto;overflow-x:scroll;border-radius:5px;">
									<table class="table table-hover table-striped" style='width:95%;'>
										<th>Selec</th><th>Nombre</th><th>Estado</th><th>Solicitud</th><th>Fecha Salida</th><th>Fecha Entrada</th>
										<?php 
											ManejadorDietaViatico::maquetarChoferesDisponibles();
										?>
									</table>
								</div>
							</fieldset>							
						</div>

						<div id="derecho" style="width:50%;height:95%;border-radius:8px;float:right;">
							<fieldset style="border-radius:8px;border: 3px solid;" class="well bs-component">
								<legend><h3>Solicitudes Pendientes de asignaci&oacute;n</h3></legend>
								<div style="width:100%;height:90%;overflow: auto;border-radius:5px;">
									<button type="submit" name="btnImprimir" title="Imprimir" class="btn btn-info"><img src='pics/print.png'></button>  
									<select name="slcFiltro" class="form-input"><option value='fecha'>Fecha de Creacion</option></select> <b>ES</b>
									<input type="date" name="slcFecha" value='' class="form-input"></input>
									<button type="submit" name="btnBuscar" class="btn btn-primary">Buscar</button>
									<button type="submit" name="btnAgregarChofer" class="btn btn-primary">Asignar Chofer</button>
									<br><br>

									<table class="table table-hover table-striped" style='width:98%;'>
										<th>Selec</th><th width="100px;">No.Solicitud</th><th>Fecha de Solicitud</th><th>Departamento</th><th>Usuario</th>		
										<?php 
											$query="SELECT dv.id, dv.no_oficio, dv.fecha_creacion, dv.departamento, dv.usr
													FROM dietaviatico dv
													WHERE dv.transporte = 1 AND dv.id not in (
														SELECT id_viatico 
														FROM transporte_viatico
														WHERE transporte_viatico.id_viatico = dv.id) ";
											$params = array();
											$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
											$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
											if ($rs) {
												while ($fila = sqlsrv_fetch_array($rs,SQLSRV_FETCH_ASSOC)) {
													$usr = explode("@", $fila['usr']);
													echo "	<tr>
																<td><input type='checkbox' name='chkSolicitud[]' value='{$fila['id']}'></td>
																<td>{$fila['no_oficio']}</td>
																<td>{$fila['fecha_creacion']->format('Y-m-d')}</td>
																<td>{$fila['departamento']}</td>
																<td>{$usr[0]}</td>
															</tr>";
												}
											}
										?>
									</table>
								</div>								
							</fieldset>
						</div>
					</div>
				</form>
			</div>	
		</div>
		<?php include("footer.html");?>
	</body>
</html>