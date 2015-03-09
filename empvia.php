<?php 
	include('lib/motor.php');
	
	if(!isset($_SESSION)){
		session_start();
	}
	$s = new Seguridad();
	if($s->verificar())
	{
		if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0 or strcmp($s->verificarTipo(), "Asistente") ==0)
		{	
			$empleados;
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
	
	if (isset($_GET['f']))
	{			
		$_SESSION['form']= $_GET['f'];
	}
	else
	{
		if($_POST)
		{
			$seleccionados = array();
			if(isset($_POST['btnAgregar']))
			{
				if(isset($_POST['chkEmpleados']))
				{
					foreach($_POST["chkEmpleados"] as $valor)
					{
						$seleccionados[] = $valor;
					}					
				}
				if(count($seleccionados)>0)
				{
					ManejadorDietaViatico::agregarEmpleados($_SESSION['form'], $seleccionados);
				}
				else
				{
					echo "<script>alert('Seleccione un empleado para agregarlo');</script>";	
				}
			}
			else if(isset($_POST['btnEliminar']))
			{
				if(isset($_POST['chkEmpleadosF']))
				{
					foreach($_POST["chkEmpleadosF"] as $valor)
					{
						$seleccionados[] = $valor;
					}					
				}
				if(count($seleccionados)>0)
				{
					ManejadorDietaViatico::eliminarEmpleados($_SESSION['form'], $seleccionados);
				}
				else
				{
					echo "<script>alert('Seleccione un empleado para Eliminarlo');</script>";	
				}
			}
			else if (isset($_POST['btnVolver']))
			{
				echo "<script languaje='javascript' type='text/javascript'>window.close();</script>";
			}
		}	
	}
?>
<html>
	<head>
		<title>Administrar empleados</title>
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
			<form method="post" action="empvia.php">
				<center>
					<h3 style="color:black;">Solicitud Dieta y Viaticos</h3>
					<fieldset style="width:46.5%; float:right; ">
						<legend style="color:black; font-weight:bold;">Empleados Del Formulario</legend>
						<div style="overflow: auto; width: 100%; height: 40%;">
							<table style='border-collapse: collapse;width: 100%;' border class='tab_cadre_fixe'>
								<?php 
									$rs =ManejadorDietaViatico::obtenerEmpleados($_SESSION['form'] );
									if(!$rs)
									{
										echo "Hubo un problema al cargar los empleados de la base de datos.";
									}
									else
									{
										while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
										{
											echo "	<tr class='tab_bg_1'>
														<td> 
															<input type='checkbox' name='chkEmpleadosF[]' value='{$fila['id']}' <b>{$fila['nombre']}</b> - 
															{$fila['cedula']} - {$fila['cargo']}
														</td>
													</tr>
												";
										}
									}
								?>
							</table>
						</div></br>
						<input align="right" type="submit" name="btnEliminar" value="Eliminar" class='submit'></input>
						<input type="submit" name="btnVolver" value="Salir" class='submit'></input>	
					</fieldset>
					
					<fieldset style="width:48%; float:left;">
						<legend style="color:black;font-weight:bold;">Empleados Existentes</legend>
						<div style="overflow: auto; width: 100%; height: 40%;">
							<table style='border-collapse: collapse;width: 100%;' border class='tab_cadre_fixe'>
								<?php 
									$rs;
									$rs=Manejador::obtenerEmpleados();
									if(!$rs)
									{
										echo "Hubo un problema al cargar los empleados de la base de datos.";
									}
									else
									{
										while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
										{
											echo "	<tr class='tab_bg_1'>
														<td> 
															<input type='checkbox' name='chkEmpleados[]' value='{$fila['id']}' <b>{$fila['nombre']}</b> - 
															{$fila['cedula']} - {$fila['departamento']}
														</td>
													</tr>
												";
										}
									}
								?>
							</table>
						</div></br>
						<input align="right" type="submit" name="btnAgregar" value="Agregar" class='submit'></input>			
					</fieldset>			
				</center>
			</form>	
		</div>
		<div id='footer'>
			<table width='100%'>
				<tr>
					<td class='left'>
						<span class='copyright'><a href='http://promesecal.gob.do/'>PROMESE CAL</a></span>
					</td>
					<td class='copyright'>
						<span class='copyright'>Version actual: 0.14</span>
					</td>
					<td class='right'>
						<span class='copyright'>SCHE  0.14 Copyright (C) 2013 by NiosX PromeseCal.</span>
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>