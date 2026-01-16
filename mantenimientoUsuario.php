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
	if(strcmp($s->verificarTipo(), "SuperAdmin") ==0)
	{	
		$m = new ManejadorUsuario();
		$u = new Usuario();
		$_SESSION['rutaActual']="Mantenimiento > Usuarios";
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
	if(isset($_POST['btnNuevo']))
	{
		header('Location:mantenimientoUsuario.php');	
	}
	else if (isset($_POST['btnGuardar']))
	{
		if($_POST['txtUsuario']!='' && $_POST['txtTipo']!='Todos')
		{
			$u->setID($_POST['txtID']);
			$u->setUsuario($_POST['txtUsuario']);
			$u->setTipo($_POST['txtTipo']);
			$u->setPass($_POST['txtPass']);
			$u->setEmpleado($_POST['txtEmpleado']);
			$u->guardar();	
			$msjError ="";
			header('Location:mantenimientoUsuario.php');
		}
		else
		{
			$msjError= "Faltan campos por completar.";
		}		
	}
	if(isset($_POST['btnEditar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para editar seleccione solo un usuario');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:mantenimientoUsuario.php?edit={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione un usuario para editar);</script>";
		}
	}
	else if(isset($_POST['btnEliminar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para eliminar seleccione solo un usuario');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:mantenimientoUsuario.php?del={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione un usuario para editar);</script>";
		}	
	}
}
else if (isset($_GET['edit']))
{
	$u->setID($_GET['edit']);
	$u->cargar();
}
else if (isset($_GET['del']))
{
	$u->eliminar($_GET['del']);
}
?>
<html>
	<head>
		<title>Mantenimiento de Usuarios</title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
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
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>
	<body>
		<?php include("menu.html");?>
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method='post'>
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<?php echo $msjError;?>
						</div>
					</div>
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
					<div class="row" style="margin-top:10px;">
						<fieldset style="overflow: auto; width: 90%; height: 38%;margin: 0 auto; border: 3px solid;border-radius:8px;">
							<legend>Lista</legend>
							<table class="table table-striped" style="width:95%;margin: 0 auto;">
								<th>Seleccion</th><th>Usuario</th><th>Tipo</th><th>Empleado</th>
								<?php 
									$rs =ManejadorUsuario::obtenerUsuarios();
									if(!$rs)
									{
										echo "Hubo un problema al cargar los empleados de la base de datos.";
									}
									else
									{
										while($fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
										{
											echo "	<tr class='tab_bg_2'>
														<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
														<td>{$fila['usuario']}</td>
														<td>{$fila['tipo']}</td>
														<td>{$fila['nombre']}</td>
													</tr>";
										}
									}
								?>
							</table>	
						</fieldset>	
					</div><br>

					<div class="row">
						<?php echo $msjError;?>
						<input type='hidden' name='txtID' value='<?php echo $u->getID();?>'></input>
					</div>
					<div class="row">
						<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
							<legend>Datos</legend>
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
								<div class="form-group">
									<label >Usuario:</label>
									<input id='txtMantenimiento' class="form-control" type="select" name='txtUsuario' value='<?php echo $u->getUsuario();?>'>
								</div>
								<div class="form-group">
									<label >Empleado:</label>
									<select id='txtMantenimiento' class="form-control" name='txtEmpleado'><?php echo $m->obtenerEmpleados($u->getEmpleado());?></select>
								</div>
							</div>
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
								<div class="form-group">
									<label >Contraseña:</label>
									<input id='txtMantenimiento' class="form-control" type="password" name='txtPass' value='<?php echo $u->getPass(); ?>'>
								</div>
								<div class="form-group">
									<label >Tipo:</label>
									<select id='txtMantenimiento' class="form-control" name='txtTipo'><?php echo $m->obtenerTipos($u->getTipo());?></select>
								</div>
							</div>
						</fieldset>
				</form>			
			</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>