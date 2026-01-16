<?php 
include("lib/motor.php");

$s = new Seguridad();
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
		$_SESSION['rutaActual']="Solicitudes > Listado de formularios de dieta y viaticos > Crear formulario part. 1";
		$dv = new DietaViatico();
		$m = new ManejadorDietaViatico();
		$dv->setDepartamento($_SESSION['dpto']);
		$dv->setNoOficio(ManejadorDietaViatico::generarNoOficio());
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
if ($_POST)
{
	if(isset($_POST['btnGuardar']))
	{
		if($dv->getID()==0 ||$dv->getID()=='')
		{
			$dv->setFecha_creacion(date("Y-m-d"));
		}
		$hora = date('H:i:s');
		$dv->setID($_POST['txtID']);
		$dv->setObjetivo(utf8_decode($_POST['txtObjetivo']));
		$dv->setHora($hora);
		$dv->setUsr($_SESSION['usuario']);
		$dv->guardar();	
		//echo $dv->getID();
		header("Location:dietayviaticos2.php?f={$dv->getID()}");	
	}
}
else if (isset($_GET['edit']))
{
	$dv->setID($_GET['edit']);
	$dv->cargar();
}
else if (isset($_GET['del']))
{
	$dv->eliminar($_GET['del']);
	ManejadorDietaViatico::eliminarEmpleadosS($_GET['del']);
	ManejadorDietaViatico::eliminarDestino($_GET['del']);

	echo "<script>location.href='/horasextra/solicitudesviaticos.php'</script>";
}
else if (isset($_GET['asign']))
{
	ManejadorDietaViatico::requerirTransporte($_GET['asign']);
	echo "<script>location.href='/horasextra/solicitudesviaticos.php'</script>";
}
?>
<html>
	<head>
		<title>Dieta y Viaticos</title>
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
				<form method="post" action="dietayviaticos.php">
					<fieldset style="width:80%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<div class="form-group">
								<label>&Aacute;rea o Dpto. Solicitante: </label>
								<input type="text" name="txtDepartamento" class="form-control" value="<?php echo $dv->getDepartamento();?>" required readonly></input>
							</div>
							<div class="form-group">
								<label>Objetivo del trabajo: </label>
								<textarea name="txtObjetivo" rows="5" cols="60" required class="form-control"><?php echo $dv->getObjetivo();?></textarea>
							</div>
							<input type="submit" name="btnGuardar" value="Siguiente" class="btn btn-primary " style="float:right;"></input>
						</div>
					</fieldset>	
					<input type="hidden" name="txtID" value="<?php echo $dv->getID();?>"></input>	
					<br>	
				</form>				
			</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>