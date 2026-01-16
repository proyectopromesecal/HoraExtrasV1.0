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
		$m = new Manejador();
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
	/*
	echo "<pre>";
	echo var_dump($_POST);
	echo "</pre>";*/


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
			GrupoEmpleado::agregarEmpleados($_SESSION['id'], $seleccionados);
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
			GrupoEmpleado::eliminarEmpleados($_SESSION['id'], $seleccionados);
		}
		else
		{
			echo "<script>alert('Seleccione un empleado para Eliminarlo');</script>";
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
			legend{
				position: relative !important;
				width: auto !important;
			}
		</style>
	</head>
	<body>
		<header>
			<?php include("menu.html");?>
		</header>
		
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method="post" action="">
						<h2>Grupos de Empleados</h2>
						<fieldset style="width:46.5%; float:right;border-radius:8px;border: 3px solid;" class="well bs-component">
							<legend >Empleados del Grupo</legend>
							<div style="overflow: auto; width: 100%; height: 50%;">
								<table style='width: 100%;' border class="table table-bordered table-striped">
									<?php 
										$rs =GrupoEmpleado::obtenerEmpleados($_SESSION['id'] );
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
						</fieldset>
						
						<fieldset style="width:48%; float:left;border-radius:8px;border: 3px solid;" class="well bs-component">
							<legend >Empleados Existentes</legend>
							<div style="overflow: auto; width: 100%; height: 50%;">
								<table style='width: 100%;' border class="table table-bordered table-striped">
									<?php 
										Manejador::obtenerEmpTbl();
									?>
								</table>
							</div></br>
							<input align="right" type="submit" name="btnAgregar" value="Agregar" class='btn btn-primary btn-block'></input>				
						</fieldset>			
				</form>	
			</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>