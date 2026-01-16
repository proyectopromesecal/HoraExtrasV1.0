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
		$dia = new DiaFeriado();
		$_SESSION['rutaActual']="Mantenimiento > Dias Feriados";
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
		if($_POST['txtFecha']!='' && !empty($_POST['txtMotivo']))
		{
			$dia->setID($_POST['txtID']);
			$dia->setFecha($_POST['txtFecha']);
			$dia->setMotivo($_POST['txtMotivo']);
			$dia->guardar();
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
				echo "<script>alert('Para editar seleccione solo 1 dia');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:diasFeriados.php?edit={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione una solicitud para editar);</script>";
		}
	}
	else if(isset($_POST['btnEliminar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para eliminar seleccione solo 1 dia');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:diasFeriados.php?del={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione una solicitud para editar);</script>";
		}	
	}
}
else if (isset($_GET['edit']))
{
	$dia->setID($_GET['edit']);
	$dia->cargar();
}
else if (isset($_GET['del']))
{
	$dia->eliminar($_GET['del']);
}
?>
<html>
	<header>
		<title>Mantenimiento de Dias Feriados</title>
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
	</header>
	<body>
		<?php include("menu.html");?>
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method="post" action="diasFeriados.php">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
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
						</div>
					</div>
					<div class="row" style="margin-top: 10px;">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<div style="height:50%;overflow:auto;width:80%;margin: 0 auto;">
								<table class='table table-striped' style="width:100%;">
									<tr class='tab_bg_2'>
										<th>Seleccion</th><th>Fecha</th><th>Motivo</th>
									</tr>
									<?php 
										ManejadorDiasFeriados::obtenerDiasFeriados();
									?>
								</table>					
							</div>

						</div>
					</div>
					<div class="row" style="width:80%;margin:0 auto;">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<input type="hidden" name="txtID" value="<?php echo $dia->getID();?>"></input>
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
								<div class="form-group">
									<label><b>Fecha:</b> </label>
									<input type="date" name="txtFecha" class='form-control' value='<?php echo date("Y-m-d",strtotime($dia->getFecha()));?>'>
								</div>
							</div>
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
								<div class="form-group">
									<label><b>Motivo:</b></label> 
									<input type="text" name="txtMotivo" class='form-control' value="<?php echo $dia->getMotivo();?>"></input>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>