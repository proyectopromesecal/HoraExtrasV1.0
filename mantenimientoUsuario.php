<?php 
	include('lib/motor.php');
	
	if(!isset($_SESSION)){
		session_start();
	}
	$s = new Seguridad();
	if($s->verificar())
	{
		if(strcmp($s->verificarTipo(), "SuperAdmin") ==0)
		{	
			$m = new ManejadorUsuario();
			$u = new Usuario();
			$_SESSION['rutaActual']="Mantenimiento > Usuarios";
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
		if(isset($_POST['btnNuevo']))
		{
			header('Location:mantenimientoUsuario.php');	
		}
		else if (isset($_POST['btnGuardar']))
		{
			if($_POST['txtUsuario']!='' && $_POST['txtTipo']!='Todos')
			{
				$u->setID($_POST['txtID']);
				$u->setUsuario($_POST['txtUsuario']);
				$u->setTipo($_POST['txtTipo']);
				$u->setPass($_POST['txtPass']);
				$u->setEmpleado($_POST['txtEmpleado']);
				$u->guardar();	
				$msjError ="";
				header('Location:mantenimientoUsuario.php');
			}
			else
			{
				$msjError= "Faltan campos por completar.";
			}		
		}
		if(isset($_POST['btnEditar']))
		{
			if(isset($_POST['check']))
			{
				$t = count($_POST["check"]);
				if($t>1)
				{
					echo "<script>alert('Para editar seleccione solo un usuario');</script>";	
				}
				else
				{
					foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
					{
						header("Location:mantenimientoUsuario.php?edit={$valor}");
					}			
				}
			}
			else
			{
				echo "<script>alert('Seleccione un usuario para editar);</script>";
			}
		}
		else if(isset($_POST['btnEliminar']))
		{
			if(isset($_POST['check']))
			{
				$t = count($_POST["check"]);
				if($t>1)
				{
					echo "<script>alert('Para eliminar seleccione solo un usuario');</script>";	
				}
				else
				{
					foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
					{
						header("Location:mantenimientoUsuario.php?del={$valor}");
					}			
				}
			}
			else
			{
				echo "<script>alert('Seleccione un usuario para editar);</script>";
			}	
		}
	}
	else if (isset($_GET['edit']))
	{
		$u->setID($_GET['edit']);
		$u->cargar();
	}
	else if (isset($_GET['del']))
	{
		$u->eliminar($_GET['del']);
	}
?>
<html>
	<head>
		<title>Mantenimiento de Usuarios</title>
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
		</style>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<br>
			<div id='form'>
				<form method='post' action='mantenimientoUsuario.php'>
					<center>
						<div style="width:100%;">
							<div style="width:50%;" >
								<button type="submit" name="btnNuevo" title="Agregar Usuario"><img src='pics/add.png'></button> &nbsp 
								<button type="submit" name="btnEditar" title="Editar Usuario"><img src='pics/edit.png'></button> &nbsp 
								<button type="submit" name="btnEliminar" title="Eliminar Usuario"><img src='pics/delete.png' height="16" width="16"></button>&nbsp 
								<button type="submit" name="btnGuardar" title="Guardar Cambios"><img src='pics/sauvegardes.png' height="16" width="16"></button> &nbsp 
							</div>
							<br>
							<div style="overflow: auto; width: 50%; height: 38%; ">
								<table class="tab_cadre_fixe" style="width:100%;">
								<th>Seleccion</th><th>Usuario</th><th>Tipo</th><th>Empleado</th>
									<?php 
										$rs =ManejadorUsuario::obtenerUsuarios();
										if(!$rs)
										{
											echo "Hubo un problema al cargar los empleados de la base de datos.";
										}
										else
										{
											while($fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
											{
												echo "	<tr class='tab_bg_2'>
															<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
															<td>{$fila['usuario']}</td>
															<td>{$fila['tipo']}</td>
															<td>{$fila['nombre']}</td>
														</tr>";
											}
										}
									?>
								</table>	
							</div><br><br>
						</div>				
					</center>
					<table width='100%' class="tab_cadre_fixe" >
						<tr class="tab_bg_1">
							<td width='50%'><?php echo $msjError;?></td>
							<td width='50%'><input type='hidden' name='txtID' value='<?php echo $u->getID();?>'></input></td>
						</tr>
						<tr class="tab_bg_1">
							<td><label id='lb'>Usuario:</label></td>
							<td><input id='txtMantenimiento' style='width:200px;' type="select" name='txtUsuario' value='<?php echo $u->getUsuario();?>'></input></td>
							<td><label id='lb'>Contraseña:</label></td>
							<td><input id='txtMantenimiento' style='width:200px;' type="password" name='txtPass' value='<?php echo $u->getPass(); ?>'></input></td>
						</tr>
						
						<tr class="tab_bg_1">
							<td><label id='lb'>Tipo:</label></td>
							<td><select id='txtMantenimiento' style='width:200px;' name='txtTipo'><?php echo $m->obtenerTipos($u->getTipo());?></select>
							<td><label id='lb'>Empleado:</label></td>
							<td><select id='txtMantenimiento' style='width:200px;' name='txtEmpleado'><?php echo $m->obtenerEmpleados($u->getEmpleado());?></select>
						</tr>
					</table>
				</form>			
			</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>