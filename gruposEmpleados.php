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
	
	if($_POST)
	{
		/*
		echo "<pre>";
		echo var_dump($_POST);
		echo "</pre>";*/


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
				GrupoEmpleado::agregarEmpleados($_SESSION['id'], $seleccionados);
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
				GrupoEmpleado::eliminarEmpleados($_SESSION['id'], $seleccionados);
			}
			else
			{
				echo "<script>alert('Seleccione un empleado para Eliminarlo');</script>";
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
				background: url(pics/background2.jpg) no-repeat;
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
			input{
				background-color: #E3E1B8; 
			    padding: 2px 4px;
			    font: 13px sans-serif;
			    text-decoration: none;
			    border: 1px solid #000;
			    border-color: #aaa #444 #444 #aaa;
			    color: #000;
			}
		</style>
	</head>
	<body class="   ext-webkit ext-chrome">
		<?php include("menu.html");?>
		<div id='pag'>
			<form method="post" action="">
				<center>
					<h3 style="color:white;">Grupos de empleados</h3>
					<fieldset style="width:46.5%; float:right; ">
						<legend style="color:black; font-weight:bold;">Empleados del grupo</legend>
						<div style="overflow: auto; width: 100%; height: 50%;">
							<table style='border-collapse: collapse;width: 100%;' border class='tab_cadre_fixe'>
								<?php 
									$rs =GrupoEmpleado::obtenerEmpleados($_SESSION['id'] );
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
					</fieldset>
					
					<fieldset style="width:48%; float:left;">
						<legend style="color:black;font-weight:bold;">Empleados Existentes</legend>
						<div style="overflow: auto; width: 100%; height: 50%;">
							<table style='border-collapse: collapse;width: 100%;' border class='tab_cadre_fixe'>
								<?php 
									Manejador::obtenerEmpTbl();
								?>
							</table>
						</div></br>
						<input align="right" type="submit" name="btnAgregar" value="Agregar" class='submit'></input>				
					</fieldset>			
				</center>
			</form>	
		</div>
		<?php include("footer.html");?>
	</body>
</html>