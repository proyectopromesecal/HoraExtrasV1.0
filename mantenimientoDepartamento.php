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
	if($s->verificar() == 'SuperAdmin' or in_array("Administrador", $_SESSION['permisos']))
	{
		$_SESSION['rutaActual']="Mantenimiento > Departamentos";
		$dep = new Departamento();
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
	if(isset($_POST['btnGuardar']))
	{
		if(!empty($_POST['txtDepartamento']))
		{
			$dep->setId($_POST['txtID']);
			$dep->setNombre($_POST['txtDepartamento']);
			$dep->guardar();
		}
		else 
		{
			echo "<script>alert('Complete los campos para guardar');</script>";
		}
	}
	if(isset($_POST['btnEditar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para editar seleccione solo un departamento');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:mantenimientoDepartamento.php?edit={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione un departamento para editar);</script>";
		}
	}
	else if(isset($_POST['btnEliminar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para eliminar seleccione solo un departamento');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:mantenimientoDepartamento.php?del={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione un departamento para eliminar);</script>";
		}	
	}
	else if (isset($_POST['btnCargos']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para agregar cargos seleccione solo un departamento');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:departamentos.php?d={$valor}");
				}			
			}
		}	
	}
}
else if (isset($_GET['edit']))
{
	$dep->setID($_GET['edit']);
	$dep->cargar();
}
else if (isset($_GET['del']))
{
	$dep->eliminar($_GET['del']);
}
?>
<html>
	<header>
		<title>Mantenimiento de Departamentos</title>
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
		</style>
	</header>
	<body>
		<?php include("menu.html");?>
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method="post" action="mantenimientoDepartamento.php">					
					<div class="row">
						<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
							<legend>Opciones</legend>
							<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
								<button type="submit" name="btnNuevo" title="Nuevo Empleado" class="btn btn-info btn-block"><img src='pics/add.png'></button>
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
								<button type="submit" name="btnEditar" title="Editar Empleado" class="btn btn-warning btn-block"><img src='pics/edit.png'></button> 
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
								<button type="submit" name="btnEliminar" title="Eliminar Empleado" class="btn btn-warning btn-danger btn-block"><img src='pics/delete.png' height="16" width="16"></button>
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
								<button type="submit" name="btnGuardar" title="Guardar Cambios" class="btn btn-primary btn-block"><img src='pics/sauvegardes.png' height="16" width="16"></button> 
							</div>
						</fieldset> 		
					</div><br>
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
								<legend>Lista</legend>
								<div style="height:50%;overflow:auto;width:100%;">
									<table class='table table-striped' style="width:100%;">
										<tr>
											<th width="10px">Seleccion</th><th>Departamento</th>
										</tr>
										<?php 
											ManejadorDepartamento::obtenerDepartamentos();
										?>
									</table>					
								</div>
							</fieldset>
						</div>
					</div></br>
					<div class="row">
						<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
							<legend>Datos</legend>
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<input type="hidden" name="txtID" value="<?php echo $dep->getId()?>"
								<div class="form-control">
									<label><b>Departamento:</b> </label>
									<input type='text' name="txtDepartamento" class="form-control" value="<?php echo $dep->getNombre();?>" placeholder="Nombre del departamento/division">
								</div>
							</div>
						</fieldset>
					</div>
				</form>
			</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>