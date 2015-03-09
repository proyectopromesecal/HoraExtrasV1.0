<?php 
include('lib/motor.php');

if(!isset($_SESSION)){
	session_start();
}
$s = new Seguridad();
if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Administrador") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0)
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
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</header>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<center></br>
				<fieldset style="width:40%;border-radius:8px;"><br>
					<form method="post" action="diasFeriados.php">
						<div style="width:100%;">
							<div style="width:100%;" >
								<button type="submit" name="btnNuevo" title="Nuevo"><img src='pics/add.png'></button> &nbsp 
								<button type="submit" name="btnEditar" title="Editar"><img src='pics/edit.png'></button> &nbsp 
								<button type="submit" name="btnEliminar" title="Eliminar"><img src='pics/delete.png' height="16" width="16"></button>&nbsp 
								<button type="submit" name="btnGuardar" title="Guardar Cambios"><img src='pics/sauvegardes.png' height="16" width="16"></button> &nbsp 
							</div><br>
							<div style="height:50%;overflow:auto;width:100%;">
								<table class='tab_cadre_fixe' style="width:100%;">
									<tr class='tab_bg_2'>
										<th>Seleccion</th><th>Fecha</th><th>Motivo</th>
									</tr>
									<?php 
										ManejadorDiasFeriados::obtenerDiasFeriados();
									?>
								</table>					
							</div>								
						</div>
						<div style="width:100%;">
							<input type="hidden" name="txtID" value="<?php echo $dia->getID();?>"></input>
							<label><b>Fecha:</b> </label>
							<input type="date" name="txtFecha" value='<?php echo date("Y-m-d",strtotime($dia->getFecha()));?>'> &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp
							<label><b>Motivo:</b> </label><input type="text" name="txtMotivo" value="<?php echo $dia->getMotivo();?>"></input><br><br>
						</div>
					</form>
				</fieldset>	
			</center>
		</div>
		<?php include("footer.html")?>
	</body>
</html>