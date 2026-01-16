<?php 
include('lib/motor.php');
error_reporting(E_ERROR | E_PARSE);
$s = new Seguridad();
$domain = $_SERVER['HTTP_HOST'];  
$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
$includes = $_SESSION['m']->obtenerIncludes($url);
if(!isset($_SESSION)){
	session_start();
}
if($s->verificar())
{
	global $rs;
	global $buscar;
	$buscar=false;
}
else
{
	header('Location:Login.php');
}
$_SESSION['rutaActual']="Inicio";

if ($_POST) {
	if (isset($_POST['btnBuscar'])) {
		if (!empty($_POST['txtCodigo'])) {
			$rs = Manejador::obtenerHorario($_POST['slcFecha'], $_POST['slcFecha2'], '', $_POST['txtCodigo']); 
		}
		else 
		{
			if (strcmp($_POST['slcDepartamento'], 'todos')==0) {
				$rs = Manejador::obtenerHorario($_POST['slcFecha'], $_POST['slcFecha2'], 'todos' , ''); 	
			}
			else
			{
				$rs = Manejador::obtenerHorario($_POST['slcFecha'], $_POST['slcFecha2'], $_POST['slcDepartamento'] , ''); 	
			}
			
		}
		$buscar = true;
	}
	else if (isset($_POST['btnModificar'])) {
		if (isset($_POST['chkEmpleados'])) {
			$x=false;
			foreach ($_POST['chkEmpleados'] as $value) {
				if (isset($_POST['txtHoraEntrada']) && isset($_POST['txtHoraSalida'])) 
				{
					$x = Manejador::cambiarHorario($value, $_POST['txtHoraEntrada'], $_POST['txtHoraSalida']);
				}
				else if ((isset($_POST['txtHoraEntrada']) && !empty($_POST['txtHoraEntrada'])) && !isset($_POST['txtHoraSalida']))
				{
					$x = Manejador::cambiarHorario($value, $_POST['txtHoraEntrada'], '');
				}
				else if (!isset($_POST['txtHoraEntrada']) && (isset($_POST['txtHoraSalida']) && !empty($_POST['txtHoraSalida'])))
				{
					$x = Manejador::cambiarHorario($value, '', $_POST['txtHoraSalida']);
				}
			}
			if ($x) {
				echo "<script>alert('Se han modificado los datos');</script>";
			}
			else{
				echo "<script>alert('No se ha podido modificar');</script>";
			}
			//var_dump($_POST);
		}
		else
		{
			echo "<script>alert('Debes seleccionar un empleado para modificar');</script>";
		}
	}
	if (isset($_POST['btnAgregar'])) {
		if (!empty($_POST['txtCodigo'])) {
			$respuesta = Manejador::ingresarHorario($_POST['txtCodigo'],$_POST['slcFecha'], $_POST['txtHoraEntrada'], $_POST['txtHoraSalida']); 
			if ($respuesta==1) {
				echo "<script>alert('Los datos se han agregado')</script>";
			}
			else
			{
				echo "<script>alert('No se han agregado los datos ')</script>";
			}
		}
	}
	if (isset($_POST['btnNuevo'])) {
		header("omisionpunch.php");
	}
		//echo "<pre>";
	//var_dump($_POST);
	//echo "</pre>";
}
?>
<html>
	<header>
		<title>Omisiones de Punch</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
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
			.btn {
				margin-top: 20px;
				margin-bottom: 20px;
				padding: 20px;
			}
		</style>
		<script type="text/javascript">
			$(function() {
			 	$(document).ready(function(){
			    	//$( "#dialog" ).dialog();
			    	//$( "#dialog" ).load('archivos/aviso.html').dialog('open');
				});
				$('#select_all').click(function() {
				    var c = this.checked;
				    $(":checkbox[name = 'chkEmpleados[]']").prop('checked',c);
				});
			});
		</script>
	</header>
	<body>
		<?php include("menu.html");?>
		<form method="post">
			<div id='contenido'>
				<div class="container-fluid body-content">
					<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<label>Filtrar por Departamento: </label>
										<select name='slcDepartamento' class="form-control">
											<?php Manejador::obtenerDepartamentos();?>
										</select>
									</div>
									<div class="form-group">
										<label>Desde: </label>
										<input type="date" name="slcFecha" value='' class="form-Control"> 
									</div>
									<div class="form-group">
										<label>Entrada</label> 
										<input type="time" class='form-control' name="txtHoraEntrada">
									</div>
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<label>Filtrar por C&oacute;digo: </label>
										<input type="text" name="txtCodigo" value="" class="form-control">
									</div>
									<div class="form-group">
										<label>Hasta: </label>
										<input type="date" name="slcFecha2" value='' class="form-control">
									</div>
									<div class="form-group">
										<label>Salida</label> 
										<input type="time" class="form-control" name="txtHoraSalida">
									</div>
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<input type="submit" name="btnBuscar" value="Buscar" class="btn btn-primary btn-block" >
									</div>
									<div class="form-group">
										<input type="submit" name="btnModificar" value="Modificar" class="btn btn-warning btn-block" >
									</div>
									<div class="form-group">
										<input type="submit" name="btnAgregar" value="Agregar" class="btn btn-info btn-block" >
									</div>
									<div class="form-group">
										<input type="submit" name="btnNuevo" value="Nuevo" class="btn btn-default btn-block" >
									</div>
								</div>
							</div>
						</div>
					</fieldset>
					<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
						<legend>Lista de resultados</legend>
						<div style="overflow:auto;width:100%; height:70%; margin:0 auto;">
							<table class='table table-striped' style="width:100%;">
								<th ><input type='checkbox' name='chkSlc' id="select_all"><b>TODO</b> </th><th>Nombre</th><th>Codigo</th><th>Departamento</th><th>Fecha</th><th>Entrada</th><th>Salida</th>
								<tr>
									<?php 
										if ($buscar) 
										{
											if($rs)
											{
												if(sqlsrv_num_rows($rs)>0)	
												{
													while ($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) 
													{
														echo "
															<tr>
																<td><input type='checkbox' name='chkEmpleados[]' value='{$fila['horario']}'</td>
																<td><input type='text' name='txtNombre' readonly value='{$fila['nombre']}'</td>
																<td><input type='text' name='txtCodigo' readonly value='{$fila['codigo_empleado']}'</td>
																<td><input type='text' name='txtDepto' readonly value='{$fila['departamento']}'</td>
																<td><input type='text' name='txtFecha' readonly value='{$fila['fecha']->format('Y-m-d')}'</td>
																<td><input type='text' name='txtEntrada' readonly value='{$fila['horadeentrada']}'</td>
																<td><input type='text' name='txtSalida' readonly value='{$fila['horadesalida']}'</td>
															</tr>
														";
													}	
												}
												else
												{
													echo "<script>alert('No hay resultados en su busqueda');</script>";
												}		
											}
											else
											{
												echo "<script>alert('No ha establecido los parametros necesarios');</script>";
											}	
											$rs=null;							
										}
									?>
								</tr>
							</table>					
						</div>
					</fieldset>
				</div>
			</div>			
		</form>
		<?php include("footer.html");?>
	</body>
</html>