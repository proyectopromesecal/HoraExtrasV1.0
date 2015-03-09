<?php 
	include('lib/motor.php');
	
	if(!isset($_SESSION)){
		session_start();
	}
	$s = new Seguridad();
	if($s->verificar())
	{
		if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0)
		{	
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
		$_SESSION['nombre'] = ManejadorTransporte::obtenerNombre($_GET['f']);
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
					if(isset($_SESSION['form']) && $_SESSION['form']!=0)
					{
						ManejadorTransporte::agregarEmpleados($_SESSION['form'], $seleccionados);
					}
				}
				else
				{
					echo "Seleccione un empleado para agregarlo";
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
					if(isset($_SESSION['form']) && $_SESSION['form']!=0)
					{
						ManejadorTransporte::eliminarEmpleados($_SESSION['form'], $seleccionados);
					}
				}
				else
				{
					echo "Seleccione un empleado para Eliminarlo";
				}
			}
			else if (isset($_POST['btnVolver']))
			{
				header('Location:solicitudestransp.php');	
			}
		}	
	}
?>
<html>
	<head>
		<title>Administrar Empleados</title>
		<style type="text/css">
			legend
			{
				background: #FD9;
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
			}
		</style>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
	</head>
	<body class="   ext-webkit ext-chrome">
	<?php include("menu.html")?>
		<div id='pag'>
			<form method="post" action="emptrans.php">
				<center>
					<h3 style="color:black;">Formulario: <?php if(isset($_SESSION['nombre'])) echo $_SESSION['nombre'];?></h3>
					<fieldset border style="width:44.5%; float:left;">
						<legend style="color:black; font-weight:bold;">Empleados Del Formulario</legend>
						<div style="overflow: auto; width: 100%; height: 58%; ">
							<table style='border-collapse: collapse;' border class='tab_cadre_fixe'>
								<?php 
									$rs =ManejadorTransporte::obtenerEmpleados($_SESSION['form']);
									if(!$rs)
									{
										if(!mysql_num_rows($rs) >0)
										{
											echo "Hubo un problema al cargar los empleados de la base de datos.";									
										}
									}
									else
									{
										while($fila=mysql_fetch_assoc($rs))
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
					</fieldset>
					
					<fieldset style="width:50%; float:right;">
						<legend align="center" style="color:black;font-weight:bold;">Empleados Existentes</legend>
						<div style="overflow: auto; width: 100%; height: 58%;">
							<table style='border-collapse: collapse;' border class='tab_cadre_fixe'>
								<?php 
									$rs;
									if(strstr($_SESSION['tipo'],"SuperAdmin"))
									{
										$rs =Manejador::obtenerEmpleados();
									}
									else
									{
										$rs =ManejadorTransporte::obtenerEmpleadosDpto($_SESSION['dpto']);
									}
									
									if(!$rs)
									{
										echo "Hubo un problema al cargar los empleados de la base de datos.";
									}
									else
									{
										while($fila=mysql_fetch_assoc($rs))
										{
											echo "	<tr>
														<td class='tab_bg_1'> 
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
						<input type="submit" name="btnVolver" value="Volver al Formulario" class='submit'></input>				
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