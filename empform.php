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
	
	if (isset($_GET['f']) && isset($_GET['t']))
	{			
		$_SESSION['femp'] = $_GET['f'];
		$_SESSION['tremp'] = $_GET['t'];
		$_SESSION['noOficio'] = ManejadorSolicitud::obtenerNoOficio($_GET['f']);
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
					ManejadorSolicitud::agregarEmpleados($_SESSION['femp'], $seleccionados);
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
					ManejadorSolicitud::eliminarEmpleados($_SESSION['femp'], $seleccionados);
				}
				else
				{
					echo "<script>alert('Seleccione un empleado para Eliminarlo');</script>";
				}
			}
			else if (isset($_POST['btnVolver']))
			{
				header("Location:solicitudeshoras.php");	
			}
		}	
	}
?>
<html>
	<head>
		<title>Administrar empleados</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<script src="js/jquery-2.1.1.js"></script>
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
				background: url(pics/background.png) no-repeat;
				background-size: cover;
				background-position: center;
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

		<script>
			$(function() {
				$('#select_all').click(function() {
				    var c = this.checked;
				    $(":checkbox[name = 'chkEmpleados[]']").prop('checked',c);
				});
			});
		</script>

	</head>
	<body class="   ext-webkit ext-chrome">
		<?php include("menu.html");?>
		<div id='pag'>
			<form method="post" action="empform.php" name="form">
				<center>
					<h3 style="color:black;">Formulario: <?php if(isset($_SESSION['noOficio'])) echo $_SESSION['noOficio'];?></h3>
					<fieldset style="width:46.5%; float:right; ">
						<legend style="color:black; font-weight:bold;">Empleados Del Formulario</legend>
						<div style="overflow: auto; width: 100%; height: 50%;">
							<table style='border-collapse: collapse;width: 100%;' border class='tab_cadre_fixe'>
								<?php 
									$rs =ManejadorSolicitud::obtenerEmpleados($_SESSION['femp'] );
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
						<input type="submit" name="btnVolver" value="Guardar y Salir" class='submit' style="width:150px"></input>
					</fieldset>
					
					<fieldset style="width:46%; float:left;">
						<legend style="color:black;font-weight:bold;">Empleados Existentes</legend>
						<div style="overflow: auto; width: 100%; height: 50%;" id="empDisponibles">
							<input type='checkbox' name='chkSlc' id="select_all" style='float:left;'><b style='float:left;'>TODO</b>
							<br>
							<table style='border-collapse: collapse;width: 100%;' border class='tab_cadre_fixe' name="tblDisp">
								<?php 
									Manejador::obtenerEmpleadosDisponibles($_SESSION['id'],date('m'), $_SESSION['femp']);
								?>
							</table>
						</div></br>
						<input align="right" type="submit" name="btnAgregar" value="Agregar" class='submit' ></input>				
					</fieldset>			
				</center>
			</form>	
		</div>
		<?php include("footer.html");?>
	</body>
</html>