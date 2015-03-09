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
		if(!empty($_POST['txtNombre']) && !empty($_POST['txtSueldoMin']) && !empty($_POST['txtSueldoMax']))
		{
			$cargo->setId($_POST['txtID']);
			$cargo->setNombre($_POST['txtNombre']);
			$cargo->setSueldo_min($_POST['txtSueldoMin']);
			$cargo->setSueldo_max($_POST['txtSueldoMax']);
			$cargo->guardar();
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
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<script src="css/jquery-2.0.3.min.js" type="text/javascript"></script>
		<script src="css/jquery.maskedinput.js" type="text/javascript"></script>
		<script>
			jQuery(function($){
			$("#sueldo").mask("9999?99");
			$("#sueldo2").mask("9999?99");
			});
		</script>
	</header>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<center></br>
				<fieldset style="width:50%;border-radius:8px;"><br>
					<form method="post" action="mantenimientoCargo.php">
						<div style="width:100%;">
							<div style="width:100%;" >
								<button type="submit" name="btnNuevo" title="Nuevo"><img src='pics/add.png'></button> &nbsp 
								<button type="submit" name="btnEditar" title="Editar"><img src='pics/edit.png'></button> &nbsp 
								<button type="submit" name="btnEliminar" title="Eliminar"><img src='pics/delete.png' height="16" width="16"></button>&nbsp 
								<button type="submit" name="btnGuardar" title="Guardar Cambios"><img src='pics/sauvegardes.png' height="16" width="16"></button> &nbsp 
							</div><br>
							<div style="height:50%;overflow:auto;width:95%;">
								<table class='tab_cadre_fixe' style="width:100%;">
									<tr class='tab_bg_2'>
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
						</div><br>
						<div style="width:100%;">
							<table class="tab_cadre_fixe" style='width:100%;' >
								<tr>
									<input type="hidden" name="txtID" value="<?php echo $cargo->getId()?>">
									<td colspan=4> 
										<label><b>Cargo:</b> </label>
										<input type="text" name="txtNombre" style="width:200px;display:inline-block;" value="<?php echo $cargo->getNombre();?>">
									</td>
									<td><label><b>Sueldo Minimo:</b></label></td> 
									<td><input type="text" id="sueldo" name="txtSueldoMin" style="width:100px;" value="<?php echo $cargo->getSueldo_min();?>"></td>
									<td><label><b>Sueldo Maximo:</b> </label></td>
									<td><input type="text" id="sueldo2" name="txtSueldoMax" style="width:100px;" value="<?php echo $cargo->getSueldo_max();?>"></td>
								</tr>
							</table>
						</div>
					</form>
				</fieldset>	
			</center>
		</div>
		<?php include("footer.html");?>
	</body>
</html>