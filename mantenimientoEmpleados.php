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
			$_SESSION['rutaActual']="Mantenimiento > Empleados";
			$m = new Manejador();
			$e = new Empleado();
			$dpt;
			
			#echo '<pre>';
			#print_r($_POST);
			#echo '</pre>';
			
			#echo '<pre>';
			#print_r($_GET);
			#echo '</pre>';
			$msjError="";		
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
		if(isset($_POST['btnEditar']))
		{
			if(isset($_POST['check']))
			{
				$t = count($_POST["check"]);
				if($t>1)
				{
					echo "<script>alert('Para editar seleccione solo 1 empleado');</script>";	
				}
				else
				{
					foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
					{
						header("Location:mantenimientoEmpleados.php?edit={$valor}");
					}			
				}
			}
			else
			{
				echo "<script>alert('Seleccione un empleado para editar);</script>";
			}
		}
		else if(isset($_POST['btnEliminar']))
		{
			if(isset($_POST['check']))
			{
				$t = count($_POST["check"]);
				if($t>1)
				{
					echo "<script>alert('Para eliminar seleccione solo 1 empleado');</script>";	
				}
				else
				{
					foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
					{
						header("Location:mantenimientoEmpleados.php?del={$valor}");
					}			
				}
			}
			else
			{
				echo "<script>alert('Seleccione un empleado para editar);</script>";
			}	
		}
		if(isset($_POST['btnNuevo']))
		{
			header('Location:mantenimientoEmpleados.php');	
		}
		if(isset($_POST['btnGuardar']))
		{
			if($_POST['txtNombre']!='' && $_POST['txtDepartamento']!='todos' && $_POST['txtCargo']!='' && $_POST['txtCedula']!='' && $_POST['txtCodigoEmpleado']!='' && $_POST['txtSueldo']!='')
			{
				if(!is_numeric($_POST['txtCedula']) )
				{
					$msjError = "El campo cedula debe ser numerico, sin guiones ni espacios.";
				}
				else if(!is_numeric($_POST['txtSueldo']))
				{
					$msjError = "El campo sueldo debe ser numerico, sin guiones no espacios.";
				}
				else if(ManejadorCargo::evaluarSueldo($_POST['txtCargo'], $_POST['txtSueldo']))
				{
					$e->setID($_POST['txtID']);
					$e->setNombre($_POST['txtNombre']);
					$e->setDepartamento($_POST['txtDepartamento']);
					$e->setCargo($_POST['txtCargo']);
					$e->setCedula($_POST['txtCedula']);
					$e->setCodigoEmpleado($_POST['txtCodigoEmpleado']);
					$e->setSueldo($_POST['txtSueldo']);
					$e->setHorarioEspecial($_POST['radHorario']);
					$e->setTipoViatico($_POST['slcViatico']);
					$e->guardar();	
					$msjError ="";
					header('Location:mantenimientoEmpleados.php');				
				}
				else
				{
					echo "<script>alert('El sueldo correspondiente a ese cargo no se encuentra en el rango establecido');</script>";	
				}
			}
			else
			{
				$msjError= "Faltan campos por completar.";
			}		
		}
	}
	else if (isset($_GET['edit']))
	{
		$e->setID($_GET['edit']);
		$e->cargar();
	}
	else if (isset($_GET['del']))
	{
		$e->eliminar($_GET['del']);
	}
?>
<html>
	<head>
		<title>Mantenimiento de Empleados</title>
		<style type="text/css">
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
		</style>
		<script>			
			function getDpto()
			{
				var d;
				d = document.getElementById('dep').value;
				$.post('getCargos.php',{id:d}, function(datos)
				{
					document.getElementById('carg').innerHTML = datos;
				});
			}
			
			function buscar(obj)
			{
				v = obj.value;
				$.post('getEmpleados.php',{valor:v}, function(datos)
				{
					document.getElementById('tabla').innerHTML = datos;
				});
			}
		</script>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<script src="css/jquery-2.0.3.min.js" type="text/javascript"></script>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<br>
			<center>
				<?php echo $msjError;?>
				<div style="width:70%;height:70%" class="tab_cadre_fixe">
					<form method='post' action='mantenimientoEmpleados.php' class="formee">
						<center>											
							<br>
							<div style="width:100%;">	
								<div style="width:100%;float:left;" >
									<button type="submit" name="btnNuevo" title="Nuevo Empleado"><img src='pics/add.png'></button> &nbsp 
									<button type="submit" name="btnEditar" title="Editar Empleado"><img src='pics/edit.png'></button> &nbsp 
									<button type="submit" name="btnEliminar" title="Eliminar Empleado"><img src='pics/delete.png' height="16" width="16"></button>&nbsp 
									<button type="submit" name="btnGuardar" title="Guardar Cambios"><img src='pics/sauvegardes.png' height="16" width="16"></button> &nbsp 
									<input type="search" name="txtBuscar" onkeyup="buscar(this);" placeholder="Buscar empleado">
								</div><br><br>
								<div style="height:325px;overflow:auto;width:100%;"><br>
									<table class='tab_cadre_fixe' style="width:100%;" >
										<tr class='tab_bg_2'>
											<th>Seleccion</th><th>Cedula</th><th>Nombres y Apellidos</th><th>Cargo</th><th>Departamento</th><th>Sueldo</th>
										</tr>
										<tbody id='tabla'>
											<?php 
												$rs = Manejador::obtenerEmpleados();
												if($rs)
												{
													while($fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
													{
														echo "
														<tr class='tab_bg_2'>
															<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
															<td>{$fila['cedula']}</td>
															<td>{$fila['nombre']}</td>
															<td>{$fila['cargo']}</td>
															<td>{$fila['departamento']}</td>
															<td>{$fila['sueldo']}</td>
														</tr>";
													}
												}
												else
												{
													echo "Hubo un problema cargando los empleados de la base de datos.";
												}
											?>										
										</tbody>

									</table>					
								</div>								
							</div>
						</center>
						<br>
						<div style="width:48%;float:left;">
							<table style="width:60%;" class="tab_cadre_fixe" >
								<tr class="tab_bg_1">
									<td><input type='hidden' name='txtID' value='<?php echo $e->getID();?>'></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td><label>Nombres y Apellidos:</label></td>
									<td><input id='tn' type="text" name='txtNombre' style='width:200px;' value='<?php echo $e->getNombre();?>'></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td><label >Departamento:</label></td>
									<td><select id='dep' name='txtDepartamento'  onChange="getDpto()" style='width:200px;'><?php echo $m->obtenerDepartamentos($e->getDepartamento());?></select>
								</tr>
								<tr class="tab_bg_1">
									<td><label >Cargo:</label></td>
									<td><select id='carg' name='txtCargo' style='width:200px;'><?php ManejadorCargo::obtenerCargo($e->getCargo());?> </select></td>
								</tr>
								<tr class="tab_bg_1">
									<td><label >Cedula:</label></td>
									<td><input id='txtMantenimiento' type="text" name='txtCedula' style='width:200px;' value='<?php echo $e->getCedula();?>' maxlength="11"></input></td>
								</tr>
							</table>						
						</div>
						<div style="width:48%;float:right;margin-top:6px;">
							<table style="width:80%;" class="tab_cadre_fixe" >
								<tr class="tab_bg_1" width="150px">
									<td><label >Codigo de empleado:</label></td>
									<td><input id='txtMantenimiento' type="text" name='txtCodigoEmpleado' value='<?php echo $e->getCodigoEmpleado();?>'></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td><label><label id='lb'>Sueldo:</label></label></td>
									<td><input id='txtMantenimiento' type="text" name='txtSueldo' value='<?php echo $e->getSueldo();?>'></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td><label ><label id='lb'>Horario Especial:</label></label></td>
									<td>
										<?php 
											if($e->getHorarioEspecial())
											{
												echo "
												Si<input type='radio' name='radHorario' value='1' checked='checked'></input>
												/ No<input type='radio' name='radHorario' value='0'></input>	";										
											}
											else
											{
												echo "
												Si<input type='radio' name='radHorario' value='1'></input>
												/ No<input type='radio' name='radHorario' value='0' checked='checked'></input>	";																				
											}
										?>
									</td>
								</tr>
								<tr>
									<td><label>Perfil Viatico: </label></td>
									<td><select name="slcViatico"><?php ManejadorDietaViatico::obtenerPerfiles($e->getTipoViatico())?></select></td>
								</tr>					
							</table>									
						</div>					
					</form>
				</div>			
			</center>
		</div>
		<?php include("footer.html");?>
	</body>
</html>