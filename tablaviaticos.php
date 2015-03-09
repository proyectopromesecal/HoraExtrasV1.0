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
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</header>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<center></br>
				<fieldset style="width:45%;border-radius:8px;"><br>
					<form method="post" action="tablaviaticos.php">
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
										<th>Seleccion</th><th>Posicion</th><th>Categoria</th><th>Desayuno</th><th>Almuerzo</th><th>Cena</th><th>Dormitorio</th>
									</tr>
									<?php 
										ManejadorTablaViatico::obtenerViaticos();
									?>
								</table>					
							</div>								
						</div><br>
						<div style="width:100%;">
							<table class="tab_cadre_fixe" style="width:100%;margin-top:2px;">
								<tr>
									<input type="hidden" name="txtID" value="<?php echo $viatico->getID()?>">
									<td><label><b>Posicion:</b> </label></td>
									<td><input type="text" name="txtCargo" style="width:150px;display:inline-block;" value="<?php echo $viatico->getPosicion()?>"></input></td>
									<td><label><b>Desayuno:</b> </label></td>
									<td><input type="text" name="txtDesayuno" style="width:150px;display:inline-block;" value="<?php echo $viatico->getDesayuno();?>"></input></td>
								</tr>
								<tr>
									<td><label><b>Almuerzo:</b></label></td> 
									<td><input type="text" name="txtAlmuerzo" style="width:150px;" value="<?php echo $viatico->getAlmuerzo();?>"></td>
									<td><label><b>Cena:</b> </label></td>
									<td><input type="text" name="txtCena" style="width:150px;" value="<?php echo $viatico->getCena();?>"></td>
								</tr>
								<tr>
									<td><label><b>Dormitorio:</b></label></td>
									<td><input type="text" name="txtDormitorio" style="width:150px;display:inline-block;" value="<?php echo $viatico->getDormitorio()?>"></input></td></td>
									<td><label><b>Categoria:</b></label></td>
									<td><select style="width:150px;display:inline-block;" name="slcGrupo"> <?php echo ManejadorTablaViatico::obtenerGrupo($viatico->getGrupo());?></select></td>
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