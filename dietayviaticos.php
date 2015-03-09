<?php 
include("lib/motor.php");

$s = new Seguridad();
if(!isset($_SESSION)){
	session_start();
}
$s = new Seguridad();
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
		$dv->setObjetivo($_POST['txtObjetivo']);
		$dv->setDescripcion($_POST['txtDescripcion']);
		$dv->setHora($hora);
		$dv->setUsr($_SESSION['usuario']);
		$dv->guardar();	
		//metodo que guarda empleados	
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
	echo "<script>location.href='/horasextra/solicitudesviaticos.php'</script>";
}
?>
<html>
	<head>
		<title>Dieta y Viaticos</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<script language='javascript'>	
			function abrirBeneficiarios()
			{
				window.open('empvia.php');
			}
		</script>
		<style>
			textarea
			{
				resize: none;
			}
		</style>
	</head>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<div style="width:50%;height:20%;"  class="tab_cadre_fixe">
				<form method="post" action="dietayviaticos.php">
					<div style="width:100%;border-radius:4px;margin-left:10px;">
						<span style="float:left;"><label>Área o Dpto. Solicitante: &nbsp </label><input style="width:270px;" type="text" name="txtDepartamento" value="<?php echo $dv->getDepartamento();?>" required readonly></input>
							<input type="submit" name="btnGuardar" value="Siguiente" class="submit"></input>
						</span><br><br>
						<span style="float:left;margin-right:10%;"><label>Objetivo del trabajo: </label><br><textarea name="txtObjetivo" rows="5" cols="50" required><?php echo $dv->getObjetivo();?></textarea></span>
						<span style=""><label>Breve Descripcion: </label><br><textarea name="txtDescripcion" required rows="5" cols="50"><?php echo $dv->getDescripcion();?></textarea></span>
					</div>	
					<input type="hidden" name="txtID" value="<?php echo $dv->getID();?>"></input>		
				</form>				
			</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>