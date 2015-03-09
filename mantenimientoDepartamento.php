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
		$_SESSION['rutaActual']="Mantenimiento > Departamentos";
		$dep = new Departamento();
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
		if(!empty($_POST['txtDepartamento']))
		{
			$dep->setId($_POST['txtID']);
			$dep->setNombre($_POST['txtDepartamento']);
			$dep->guardar();
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
				echo "<script>alert('Para editar seleccione solo un departamento');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:mantenimientoDepartamento.php?edit={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione un departamento para editar);</script>";
		}
	}
	else if(isset($_POST['btnEliminar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para eliminar seleccione solo un departamento');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:mantenimientoDepartamento.php?del={$valor}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione un departamento para eliminar);</script>";
		}	
	}
	else if (isset($_POST['btnCargos']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para eliminar seleccione solo un departamento');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:departamentos.php?d={$valor}");
				}			
			}
		}	
	}
}
else if (isset($_GET['edit']))
{
	$dep->setID($_GET['edit']);
	$dep->cargar();
}
else if (isset($_GET['del']))
{
	$dep->eliminar($_GET['del']);
}
?>
<html>
	<header>
		<title>Mantenimiento de Departamentos</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</header>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<center></br>
				<fieldset style="width:50%;border-radius:8px;"><br>
					<form method="post" action="mantenimientoDepartamento.php">
						<div style="width:100%;">
							<div style="width:100%;" >
								<button style="vertical-align: top;" type="submit" name="btnCargos" value="Agregar Cargos" title="Agregar Cargos">Agregar Cargos</button> &nbsp 
								<button type="submit" name="btnNuevo" title="Nuevo"><img src='pics/add.png'></button> &nbsp 
								<button type="submit" name="btnEditar" title="Editar"><img src='pics/edit.png'></button> &nbsp 
								<button type="submit" name="btnEliminar" title="Eliminar"><img src='pics/delete.png' height="16" width="16"></button>&nbsp 
								<button type="submit" name="btnGuardar" title="Guardar Cambios"><img src='pics/sauvegardes.png' height="16" width="16"></button> &nbsp 
							</div><br>
							<div style="height:50%;overflow:auto;width:100%;">
								<table class='tab_cadre_fixe' style="width:100%;">
									<tr class='tab_bg_2'>
										<th width="10px">Seleccion</th><th>Departamento</th>
									</tr>
									<?php 
										ManejadorDepartamento::obtenerDepartamentos();
									?>
								</table>					
							</div>								
						</div></br>
						<div style="width:100%;">
							<table class="tab_cadre_fixe" style='width:100%;'>
								<tr>
									<input type="hidden" name="txtID" value="<?php echo $dep->getId()?>">
									<td width="100px"><label><b>Departamento:</b> </label></td>
									<td><input type='text' name="txtDepartamento" style="width:200px;" value="<?php echo $dep->getNombre();?>"></input></td> 
								</tr>
							</table>
						</div>
					</form>
				</fieldset>	
			</center>
		</div>
		<?php include("footer.html")?>
	</body>
</html>