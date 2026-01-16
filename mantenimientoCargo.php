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
		$cargo = new Cargo();
		$_SESSION['rutaActual']="Mantenimiento > Cargos";
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
		$cargo->setId($_POST['txtID']);
		$cargo->setNombre($_POST['txtNombre']);
		$cargo->setSueldo_min($_POST['txtSueldoMin']);
		$cargo->setSueldo_max($_POST['txtSueldoMax']);
		$cargo->guardar();
	}
	if(isset($_POST['btnEditar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para editar seleccione solo un cargo');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:mantenimientoCargo.php?edit={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione un cargo para editar);</script>";
		}
	}
	else if(isset($_POST['btnEliminar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para eliminar seleccione solo un cargo');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:mantenimientoCargo.php?del={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione un cargo para eliminar);</script>";
		}	
	}
}
else if (isset($_GET['edit']))
{
	$cargo->setID($_GET['edit']);
	$cargo->cargar();
}
else if (isset($_GET['del']))
{
	$cargo->eliminar($_GET['del']);
}
?>
<html>
	<header>
		<title>Mantenimiento de Cargos</title>
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
		<script>
			$(document).ready(function() {
				$("#sueldo").mask("999999");
				$("#sueldo2").mask("999999");
			});
		</script>
	</header>
	<body>
		<?php include("menu.html");?>
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method="post" action="mantenimientoCargo.php">
					<div class="row">
						<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
							<legend>Opciones</legend>
							<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
								<button type="submit" name="btnNuevo" title="Nuevo Cargo" class="btn btn-info btn-block"><img src='pics/add.png'></button>
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
								<button type="submit" name="btnEditar" title="Editar Cargo" class="btn btn-warning btn-block"><img src='pics/edit.png'></button> 
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
								<button type="submit" name="btnEliminar" title="Eliminar Cargo" class="btn btn-warning btn-danger btn-block"><img src='pics/delete.png' height="16" width="16"></button>
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
								<div style="width:100%;height: 40%;overflow: auto;">
									<table class='table table-striped' style="width:90%">
										<tr>
											<th width='5%'>Seleccion</th><th width='15%'>Cargo</th><th width='8%'>Sueldo Minimo</th><th width='8%'>Sueldo Maximo</th>
										</tr>
										<?php 
											 $rs = ManejadorCargo::obtenerCargos();
											 while($fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
											 {
												echo "
												<tr class='tab_bg_2'>
													<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
													<td>{$fila['nombre']}</td>
													<td>{$fila['sueldo_min']}</td>
													<td>{$fila['sueldo_max']}</td>
												</tr>";
											 }
										?>
									</table>									
								</div>
							</fieldset>
						</div>
					</div>
					<div class="row">
						<input type="hidden" name="txtID" value="<?php echo $cargo->getId()?>">
						<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
							<legend>Datos</legend>
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<label><b>Cargo:</b> </label>
										<input type="text" name="txtNombre" class="form-control" value="<?php echo $cargo->getNombre();?>" placeholder="Nombre del cargo">
									</div>										
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<label><b>Sueldo M&iacute;nimo:</b></label>
										<input type="number" id="sueldo" name="txtSueldoMin" class="form-control" value="<?php echo $cargo->getSueldo_min();?>">
									</div>									
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<div class="form-group">
										<label><b>Sueldo M&aacute;ximo:</b> </label>
										<input type="number" id="sueldo2" name="txtSueldoMax" class="form-control" value="<?php echo $cargo->getSueldo_max();?>">
									</div>		
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