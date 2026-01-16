<?php 
include('lib/motor.php');

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
		$empleados;
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

if (isset($_GET['f']))
{			
	$_SESSION['form']= $_GET['f'];
}
else
{
	if($_POST)
	{
		$seleccionados = array();
		if(isset($_POST['btnAgregar']))
		{
			if(isset($_POST['chkEmpleados']))
			{
				foreach($_POST["chkEmpleados"] as $valor)
				{
					$seleccionados[] = $valor;
				}					
			}
			if(count($seleccionados)>0)
			{
				ManejadorDietaViatico::agregarEmpleados($_SESSION['form'], $seleccionados);
			}
			else
			{
				echo "<script>alert('Seleccione un empleado para agregarlo');</script>";	
			}
		}
		else if(isset($_POST['btnEliminar']))
		{
			if(isset($_POST['chkEmpleadosF']))
			{
				foreach($_POST["chkEmpleadosF"] as $valor)
				{
					$seleccionados[] = $valor;
				}					
			}
			if(count($seleccionados)>0)
			{
				ManejadorDietaViatico::eliminarEmpleados($_SESSION['form'], $seleccionados);
			}
			else
			{
				echo "<script>alert('Seleccione un empleado para Eliminarlo');</script>";	
			}
		}
		else if (isset($_POST['btnVolver']))
		{
			echo "<script languaje='javascript' type='text/javascript'>window.close();</script>";
		}
	}	
}
?>
<html>
	<head>
		<title>Administrar empleados</title>
		<meta charset='utf-8'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo $includes;?>
		<style type="text/css">
			#contenido{
				position: fixed;
			    top: 120px;
			    bottom: 100px;
			    left: 0;
			    right: 0;
			    overflow: auto;
			}
		</style>
		<script>
			$(function() {
				$('#select_all').click(function() {
				    var c = this.checked;
				    $(":checkbox[name = 'chkEmpleados[]']").prop('checked',c);
				});
			});
		</script>
	</head>
	<body>
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method="post" action="empvia.php">
					<fieldset style="width:46.5%; float:right;border-radius:8px;border: 3px solid" class="well bs-component">
						<legend>En este Formulario</legend>
						<div style="overflow: auto; width: 100%; height: 40%;">
							<table style='width: 100%;' class="table table-bordered table-striped table-hover" border>
								<?php 
									$rs =ManejadorDietaViatico::obtenerEmpleados($_SESSION['form'] );
									if(!$rs)
									{
										echo "Hubo un problema al cargar los empleados de la base de datos.";
									}
									else
									{
										while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
										{
											echo "	<tr>
														<td> 
															<input type='checkbox' name='chkEmpleadosF[]' value='{$fila['id']}' <b>{$fila['nombre']}</b> - 
															{$fila['cedula']} - {$fila['cargo']}
														</td>
													</tr>
												";
										}
									}
								?>
							</table>
						</div></br>
						<input align="right" type="submit" name="btnEliminar" value="Eliminar" class='btn btn-danger btn-block'></input>
						<input type="submit" name="btnVolver" value="Guardar y Salir" class='btn btn-primary btn-block'></input>
					</fieldset>		

					<fieldset style="width:46%; float:left;border-radius:8px;border: 3px solid" class="well bs-component">
						<legend>Empleados Existentes</legend>
						<div style="overflow: auto; width: 100%; height: 50%;" id="empDisponibles">
							<input type='checkbox' name='chkSlc' id="select_all" style='float:left;'><b style='float:left;'>TODO</b>
							<br>
							<table style='width: 100%;' border class="table table-bordered table-striped table-hover" border name="tblDisp">
								<?php 
									ManejadorDietaViatico::obtenerEmpleadosDisponiblesDV();
								?>
							</table>
						</div></br>
						<button align="right" type="submit" name="btnAgregar" class='btn btn-primary btn-block' >Agregar</button>				
					</fieldset>			
				</form>	
			</div>
		</div>
		<?php include('footer.html');?>
	</body>
</html>