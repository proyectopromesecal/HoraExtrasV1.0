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
		$_SESSION['rutaActual']="Mantenimiento > Empleados";
		$m = new Manejador();
		$e = new Empleado();
		$dpt;
		
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
if ($_POST)
{
	if(isset($_POST['btnEditar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para editar seleccione solo 1 empleado');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:mantenimientoEmpleados.php?edit={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione un empleado para editar);</script>";
		}
	}
	else if(isset($_POST['btnEliminar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para eliminar seleccione solo 1 empleado');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:mantenimientoEmpleados.php?del={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione un empleado para editar);</script>";
		}	
	}
	if(isset($_POST['btnNuevo']))
	{
		header('Location:mantenimientoEmpleados.php');	
	}
	if(isset($_POST['btnGuardar']))
	{
		if($_POST['txtNombre']!='' && $_POST['txtDepartamento']!='todos' && $_POST['txtCargo']!='' && $_POST['txtCedula']!='' && $_POST['txtSueldo']!='')
		{
			if(!is_numeric($_POST['txtCedula']) )
			{
				$msjError = "El campo cedula debe ser numerico, sin guiones ni espacios.";
			}
			else if(!is_numeric($_POST['txtSueldo']))
			{
				$msjError = "El campo sueldo debe ser numerico, sin guiones no espacios.";
			}
			else{
				$e->setID($_POST['txtID']);
				$e->setNombre($_POST['txtNombre']);
				$e->setDepartamento($_POST['txtDepartamento']);
				$e->setCargo($_POST['txtCargo']);
				$e->setCedula($_POST['txtCedula']);
				$e->setCodigoEmpleado($_POST['txtCodigoEmpleado']);
				$e->setSueldo($_POST['txtSueldo']);
				$e->setHorarioEspecial($_POST['radHorario']);
				$e->setTipoViatico($_POST['slcViatico']);
				$e->setNivel($_POST['slcPerfil']);
				$e->guardar();	
				$msjError ="";
				header('Location:mantenimientoEmpleados.php');					
			}
		}
		else
		{
			$msjError= "Faltan campos por completar.";
		}		
	}
}
else if (isset($_GET['edit']))
{
	$e->setID($_GET['edit']);
	$e->cargar();
}
else if (isset($_GET['del']))
{
	$e->eliminar($_GET['del']);
}
?>
<html>
	<head>
		<title>Mantenimiento de Empleados</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo $includes;?>
		<script src="css/jquery.maskedinput.js" type="text/javascript"></script>
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
		<script>		
			$(function(){
				$('#sueldo').mask("999999.99");
				$('#cedula').mask("99999999999");
			});

			function getDpto()
			{
				var d;
				d = document.getElementById('dep').value;
				$.post('getCargos.php',{id:d}, function(datos)
				{
					document.getElementById('carg').innerHTML = datos;
				});
			}
			
			function buscar(obj)
			{				
				v = obj.value;
				$.post('getEmpleados.php',{valor:v}, function(datos)
				{
					document.getElementById('tabla').innerHTML = datos;
				});
			}
		</script>
	</head>
	<body>
		<header>
			<?php include("menu.html");?>
		</header>
		
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method='post' action='mantenimientoEmpleados.php'>
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<?php echo $msjError;?>
						</div>
					</div>
					<div class="row">
						<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
							<legend>Opciones</legend>
							<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
								<button type="submit" name="btnNuevo" title="Nuevo Empleado" class="btn btn-info btn-block"><img src='pics/add.png'></button>
							</div>
							<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
								<button type="submit" name="btnEditar" title="Editar Empleado" class="btn btn-warning btn-block"><img src='pics/edit.png'></button> 
							</div>
							<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
								<button type="submit" name="btnEliminar" title="Eliminar Empleado" class="btn btn-warning btn-danger btn-block"><img src='pics/delete.png' height="16" width="16"></button>
							</div>
							<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
								<button type="submit" name="btnGuardar" title="Guardar Cambios" class="btn btn-primary btn-block"><img src='pics/sauvegardes.png' height="16" width="16"></button> 
							</div>
							<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
								<input type="search" name="txtBuscar" onkeyup="buscar(this);" placeholder="Buscar empleado por nombre o apellido" class="form-control">
							</div>
						</fieldset> 		
					</div><br>
					<div class="row">
						<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
							<legend>Lista</legend>
							<div style="height:325px;overflow:auto;width:95%;margin: 0 auto;"><br>
								<table id='tabla' class='table table-striped' style="width:100%;" >
									<tr>
										<th>Seleccion</th><th>Cedula</th><th>Nombres y Apellidos</th><th>Cargo</th><th>Departamento</th><th>Sueldo</th>
									</tr>																
								</table>					
							</div>	
						</fieldset>							
					</div>
					<br>
					<div class="row">
						<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
							<legend>Datos</legend>
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
								<input type='hidden' name='txtID' value='<?php echo $e->getID();?>'>
								<div class="form-group">
									<label>Nombres y Apellidos:</label>
									<input id='tn' type="text" name='txtNombre' class="form-control" value='<?php echo $e->getNombre();?>' placeholder="Nombres y apellidos completos">
								</div>
								<div class="form-group">
									<label>Departamento:</label>
									<select id='dep' name='txtDepartamento'  onChange="getDpto()" class="form-control"><?php echo $m->obtenerDepartamentos($e->getDepartamento());?></select>
								</div>
								<div class="form-group">
									<label>Cargo:</label>
									<select id='carg' name='txtCargo' class="form-control"><?php ManejadorCargo::obtenerCargo($e->getCargo());?> </select>
								</div>
								<div class="form-group">
									<label >C&eacute;dula:</label>
									<input id='cedula' type="text" name='txtCedula' class="form-control" value='<?php echo $e->getCedula();?>' maxlength="11" placeholder="ej. 10236548788"></input>
								</div>
								<div class="form-group">
									<label>C&oacute;digo de empleado</label>
									<input type="text" name='txtCodigoEmpleado' value='<?php echo $e->getCodigoEmpleado();?>' class='form-control' placeholder="Codigo de Punch"></input>
								</div>
							</div>
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

								<div class="form-group">
									<label>Sueldo</label>
									<input id='sueldo' type="text" name='txtSueldo' value='<?php echo $e->getSueldo();?>' class='form-control' placeholder="ej. 10000 RD$"></input>
								</div>
								<div class="form-group">
									<label>Horario Especial:</label>
										<?php 
											if($e->getHorarioEspecial())
											{
												echo "
												Si <input type='radio' name='radHorario' value='1' checked='checked' ></input>
												/ No <input type='radio' name='radHorario' value='0' ></input>	";										
											}
											else
											{
												echo "
												Si <input type='radio' name='radHorario' value='1' ></input>
												/ No <input type='radio' name='radHorario' value='0' checked='checked' ></input>	";																				
											}
										?>
								</div>
								<div class="form-group">
									<label>Perfil Vi&aacute;tico: </label>
									<select name="slcViatico" class="form-control"><?php ManejadorDietaViatico::obtenerPerfiles($e->getTipoViatico())?></select>
								</div>
								<div class="form-group">
									<label>Nivel: </label>
									<select name="slcPerfil" class="form-control">
										<?php Manejador::obtenerNivelSlc($e->getID())?>
										<option value="0">0</option>
										<option value="1">1</option>
										<option value="2">2</option>
									</select>
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