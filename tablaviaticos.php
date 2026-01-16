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
		$viatico = new TablaViatico();
		$_SESSION['rutaActual']="Mantenimientos > Tabla de Dieta y Viaticos";
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
		if(!empty($_POST['txtCargo']) && !empty($_POST['txtDesayuno']) && !empty($_POST['txtAlmuerzo']) && !empty($_POST['txtCena']) && !empty($_POST['txtDormitorio']) )
		{
			$viatico->setID($_POST['txtID']);
			$viatico->setGrupo($_POST['slcGrupo']);
			$viatico->setPosicion($_POST['txtCargo']);
			$viatico->setDesayuno($_POST['txtDesayuno']);
			$viatico->setAlmuerzo($_POST['txtAlmuerzo']);
			$viatico->setCena($_POST['txtCena']);
			$viatico->setDormitorio($_POST['txtDormitorio']);
			$viatico->guardar();
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
				echo "<script>alert('Para editar seleccione solo un perfil');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:tablaviaticos.php?edit={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione un perfil para editar);</script>";
		}
	}
	else if(isset($_POST['btnEliminar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para eliminar seleccione solo un perfil');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:tablaviaticos.php?del={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione un perfil para editar);</script>";
		}	
	}
}
else if (isset($_GET['edit']))
{
	$viatico->setID($_GET['edit']);
	$viatico->cargar();
}
else if (isset($_GET['del']))
{
	$viatico->eliminar($_GET['del']);
}
?>
<html>
	<header>
		<title>Mantenimiento de la Tabla de Viaticos</title>
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
		<script type="text/javascript">
			$(document).ready(function() {
				$('#num').mask('999999');
			});
		</script>
	</header>
	<body>
		<?php include("menu.html");?>
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method="post" action="tablaviaticos.php">
					<div class="row">
						<fieldset style="width:95%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
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
							<fieldset style="width:95%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
								<legend>Lista</legend>
								<div style="height:30%;overflow:auto;width:95%;margin:0 auto;">
									<table class='table table-striped' style="width:100%;">
										<tr>
											<th>Seleccion</th><th>Posici&oacute;n</th><th>Categoria</th><th>Desayuno</th><th>Almuerzo</th><th>Cena</th><th>Dormitorio</th>
										</tr>
										<?php 
											ManejadorTablaViatico::obtenerViaticos();
										?>
									</table>					
								</div>
							</fieldset>
						</div>
					</div><br>
					<div class="row">
						<fieldset style="width:95%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
							<legend>Datos</legend>
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<input type="hidden" name="txtID" value="<?php echo $viatico->getID()?>">
								<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
									<div class="form-group">
										<label><b>Posici&oacute;n:</b> </label>
										<input type="text" name="txtCargo" class="form-control" value="<?php echo $viatico->getPosicion()?>" placeholder="ej. Chofer">
									</div>
									<div class="form-group">
										<label><b>Desayuno:</b> </label>
										<input id="num" type="text" name="txtDesayuno" class="form-control" value="<?php echo $viatico->getDesayuno();?>" placeholder="00.00 RD$">
									</div>
									<div class="form-group">
										<label><b>Cena:</b></label>
										<input id="num" type="text" name="txtAlmuerzo" class="form-control" value="<?php echo $viatico->getAlmuerzo();?>" placeholder="00.00 RD$">
									</div>

								</div>
								<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
									<div class="form-group">
										<label><b>Almuerzo:</b></label>
										<input id="num" type="text" name="txtCena" class="form-control" value="<?php echo $viatico->getCena();?>" placeholder="00.00 RD$">
									</div>
									<div class="form-group">
										<label><b>Dormitorio:</b></label>
										<input id="num" type="text" name="txtDormitorio" class="form-control" value="<?php echo $viatico->getDormitorio()?>" placeholder="00.00 RD$">
									</div>
									<div class="form-group">
										<label><b>Categoria:</b></label>
										<select class="form-control" name="slcGrupo"> <?php echo ManejadorTablaViatico::obtenerGrupo($viatico->getGrupo());?></select>
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