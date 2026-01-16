<?php 
	include('lib/motor.php');
	
	if(!isset($_SESSION)){
		session_start();
	}
	$s = new Seguridad();
	if($s->verificar())
	{
		if($s->verificar() == 'SuperAdmin' or in_array("Administrador", $_SESSION['permisos']))
		{	
			$t;$f;
			$m = new Manejador();
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
	
	if (isset($_GET['d']))
	{			
		$_SESSION['id_dpto'] = $_GET['d'];
	}
	else
	{
		if($_POST)
		{
			$seleccionados = array();
			if(isset($_POST['btnAgregar']))
			{
				if(isset($_POST['chkCargos']))
				{
					foreach($_POST["chkCargos"] as $valor)
					{
						$seleccionados[] = $valor;
					}
				}
				if(count($seleccionados)>0)
				{
					ManejadorDepartamento::agregarCargos($_SESSION['id_dpto'], $seleccionados);
				}
			}
			else if(isset($_POST['btnEliminar']))
			{
				if(isset($_POST['chkCargosD']))
				{
					foreach($_POST["chkCargosD"] as $valor)
					{
						$seleccionados[] = $valor;
					}					
				}
				if(count($seleccionados)>0)
				{
					ManejadorDepartamento::eliminarCargos($_SESSION['id_dpto'], $seleccionados);
				}
				else
				{
					echo "<script>alert('Seleccione un cargo para Eliminarlo');</script>";
				}
			}
			else if (isset($_POST['btnVolver']))
			{
				header("Location:mantenimientoDepartamento.php");	
			}
		}	
	}
?>
<html>
	<head>
		<title>Administrar Cargos</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<style type="text/css">
			#pag
			{
			    margin: 25px 25px;
				-moz-border-radius: 8px;
				-webkit-border-radius: 8px;
				border-radius: 8px;
				-moz-box-shadow: 0px 7px 10px;
				-webkit-box-shadow: 0px 7px 10px;
				box-shadow: 0px 7px 10px;
				padding: 8px 10px 500px 8px;
				background-image: url(pics/background.png);
				background-repeat:   repeat-y;
				background-position: left top;
				background-size: 100%;
			}
			legend
			{
				background: #EDE8E8;
				border: solid 1px black;
				-webkit-border-radius: 8px;
				-moz-border-radius: 8px;
				border-radius: 8px;
				padding: 6px;
				font-family:"Lucida Console", Monaco, monospace;
				font-size:16px;
				font-weight:bold;
			}
			fieldset
			{
				-moz-border-radius: 8px;
				-webkit-border-radius: 8px;
				border-radius: 8px;
			}
		</style>
	</head>
	<body class="   ext-webkit ext-chrome">
		<?php include("menu.html");?>
		<div id='pag'>
			<form method="post" action="departamentos.php">
				<center>
					<fieldset style="width:46.5%; float:left; ">
						<legend style="color:black; font-weight:bold;">Cargos Del Departamento</legend>
						<div style="overflow: auto; width: 100%; height: 50%;">
							<table style='border-collapse: collapse;width: 100%;' border class='tab_cadre_fixe'>
								<?php 
									$rs =ManejadorDepartamento::obtenerCargosDpto($_SESSION['id_dpto']);
									if(!$rs)
									{
										echo "Hubo un problema al cargar los datos.";
									}
									else
									{
										while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
										{
											echo "	<tr class='tab_bg_1'>
														<td> 
															<input type='checkbox' name='chkCargosD[]' value='{$fila['id_cargo']}' <b>{$fila['cargo']}</b>
														</td>
													</tr>
												";
										}
									}
								?>
							</table>
						</div></br>
						<input align="right" type="submit" name="btnEliminar" value="Eliminar" class='submit'></input>
					</fieldset>
					
					<fieldset style="width:48%; float:right;">
						<legend style="color:black;font-weight:bold;">Cargos Existentes</legend>
						<div style="overflow: auto; width: 100%; height: 50%;">
							<table style='border-collapse: collapse;width: 100%;' border class='tab_cadre_fixe'>
								<?php 
									$rs;
									$rs=ManejadorCargo::obtenerCargos();
									if(!$rs)
									{
										echo "Hubo un problema al cargar los datos.";
									}
									else
									{
										while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
										{
											echo "	<tr class='tab_bg_1'>
														<td> 
															<input type='checkbox' name='chkCargos[]' value='{$fila['id']}' <b>{$fila['nombre']}</b>
														</td>
													</tr>
												";
										}
									}
								?>
							</table>
						</div></br>
						<input align="right" type="submit" name="btnAgregar" value="Agregar" class='submit'></input>
						<input type="submit" name="btnVolver" value="Guardar y Salir" class='submit' style="width:150px;"></input>				
					</fieldset>			
				</center>
			</form>	
		</div>
		<?php include("footer.html");?>
	</body>
</html>