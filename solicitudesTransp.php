<?php 
include('lib/motor.php');
if(!isset($_SESSION)){
	session_start();
}
if($_POST)
{
	$rsCustom;
	$est;
	//echo '<pre>';
	//echo print_r($_POST);
	//echo '</pre>';
	if(isset($_POST['btnEditar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para editar seleccione solo 1 solicitud');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					$valores = explode("-", $valor);
					$idSl = $valores[0];
					$est = $valores[1];
					switch($est)
					{
						case "Nueva":  
						header("Location:formularioTransporte.php?edit={$idSl}");
						break;
						case "Enviada":
						header("Location:formularioTransporte.php?edit={$idSl}");
						break;
						case "Aprobada":
						echo "<script>alert('No puede editar una solicitud aprobada');</script>";	
						break;
						case "Rechazada":
						echo "<script>alert('No puede editar una solicitud rechazada');</script>";	
						break;						
					}
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione una solicitud para editar);</script>";
		}
	}
	else if(isset($_POST['btnNuevo']))
	{
		header('Location:formularioTransporte.php');
	}
	else if(isset($_POST['btnEliminar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para eliminar seleccione solo 1 solicitud');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					$valores = explode("-", $valor);
					$idSl = $valores[0];
					$est = $valores[1];
					switch($est)
					{
						case "Nueva":  
						header("Location:formularioTransporte.php?del={$idSl}");
						break;
						case "Enviada":
						echo "<script>alert('No puede eliminar una solicitud enviada');</script>";	
						break;
						case "Aprobada":
						echo "<script>alert('No puede eliminar una solicitud aprobada');</script>";	
						break;
						case "Rechazada":
						echo "<script>alert('No puede eliminar una solicitud rechazada');</script>";	
						break;						
					}
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione una solicitud para editar);</script>";
		}	
	}
	else if(isset($_POST['btnBuscar']))
	{
		if($_POST['slcFiltro']=='estado')
		{
			$est = $_POST['slcEstados'];
			$_SESSION['est']=$est;		
		}
		else if ($_POST['slcFiltro']=='fecha2')
		{
			$query="SELECT * 
					FROM formulario_transporte
					WHERE departamento ='{$_SESSION['dpto']}'
					AND fecha = '{$_POST['slcF']}'";
			$rsCustom = mysql_query($query);
		}
		else if ($_POST['slcFiltro']=='fecha')
		{
			$query="SELECT * 
					FROM formulario_transporte
					WHERE departamento ='{$_SESSION['dpto']}'
					AND fecha_creacion = '{$_POST['slcF']}'";
			$rsCustom = mysql_query($query);	
		}
	}
	else if (isset($_POST['btnImprimir']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para imprimir seleccione solo 1 solicitud');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					$valores = explode("-", $valor);
					$idSl = $valores[0];
					$est = $valores[1];
					switch($est)
					{
						case "Nueva":  
						header("Location:transportePDF.php?s={$idSl}");
						break;
						case "Enviada":
						header("Location:transportePDF.php?s={$idSl}");
						break;
						case "Aprobada":
						header("Location:transportePDF.php?s={$idSl}");
						break;
						case "Rechazada":
						header("Location:transportePDF.php?s={$idSl}");
						break;						
					}
				}			
			}	
		}
		else
		{
			echo "<script>alert('Seleccione una solicitud para imprimir);</script>";
		}
	}
}
?>
<html>
	<header>
		<title>Solicitudes de Transporte</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<style>
			.oculto{
				display:none;
			}
		</style>
		<script>
			function cambiar()
			{
				var select = document.getElementById("select").value;
				if(select == 'estado')
				{
					var dt = document.getElementById("dt");
					dt.className = "oculto";	
					var slc = document.getElementById("slc");
					slc.className = "";					
				}
				else
				{
					var slc = document.getElementById("slc");
					slc.className = "oculto";
					var dt = document.getElementById("dt");
					dt.className = "";						
				}
			}
		</script>
	</header>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<center></br>
				<fieldset style="width:70%;border-radius:8px;"><br>
					<form method="post" action="solicitudestransp.php">
						<div style="width:100%;" >
							<button type="submit" name="btnImprimir" class='submit'><img src='pics/print.png'></button> &nbsp 
							<button type="submit" name="btnNuevo"><img src='pics/add.png'></button> &nbsp 
							<button type="submit" name="btnEditar"><img src='pics/edit.png'></button> &nbsp 
							<button type="submit" name="btnEliminar" ><img src='pics/delete.png'></button>&nbsp 
							<select onchange="cambiar()" id="select" name='slcFiltro'><?php ManejadorTransporte::obtenerFiltros();?></select> <b>ES</b> 
							<select id="slc" class="" name='slcEstados'><?php ManejadorTransporte::obtenerEstados();?></select>
							<input id='dt' type="date" name="slcF" class='oculto'>&nbsp 
							<button type='submit' name='btnBuscar' class='submit'>Buscar</button>
						</div>
						<div class='center' style="height:50%;overflow:auto;">
							<table class='tab_cadre_fixe' style="width:100%;">
								<tr class='tab_bg_2'>
									<th>Seleccion</th><th>No.Solicitud</th><th>Estado</th><th>Fecha de creacion</th><th>Fecha solicitada</th><th >Cant. empleados</th>
									<?php 
										$rs;
										if(strstr($_SESSION['tipo'], "SuperAdmin"))
										{
											if(!empty($rsCustom))
											{
												$rs = $rsCustom;
											}	
											else
											{
												$rs =ManejadorTransporte::obtenerFormularios("");
											}
										}
										else
										{
											if(!empty($rsCustom))
											{
												$rs = $rsCustom;
											}
											else
											{
												$rs =ManejadorTransporte::obtenerFormularios($_SESSION['dpto']);
											}
										}
										if($rs)
										{
											while($fila = mysql_fetch_assoc($rs))
											{
												$enlace="";
												$nombre = ManejadorTransporte::obtenerNombre($fila['id']);
												$estado = ManejadorTransporte::verEstado($fila['id']);
												if(empty($est) || strcmp($est, 'todos')==0)
												{
													$cantidad = ManejadorTransporte::cantidadEmpleados($fila['id']);
													if($cantidad <= 0) 
													{
														$enlace = "<a href='emptrans.php?f={$fila['id']}'>{$cantidad}</a>";
													}
													echo "
														<tr class='tab_bg_2'>
															<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}-{$estado}' readonly></input></td>
															<td>{$nombre}</td>
															<td>{$estado}</td>
															<td>{$fila['fecha_creacion']}</td>
															<td>{$fila['fecha']}</td>
															<td>"; if(empty($enlace)){echo $cantidad;}else{echo $enlace;}echo "</td>
														</tr>
													";
												}
												else
												{
													if(strcmp($est, $estado)==0)
													{
														$nombre = ManejadorTransporte::obtenerNombre($fila['id']);
														$cantidad = ManejadorTransporte::cantidadEmpleados($fila['id']);
														if($cantidad <= 0) 
														{
															$enlace = "<a href='emptrans.php?f={$fila['id']}'>{$cantidad}</a>";
														}
														echo "
															<tr class='tab_bg_2'>
																<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}-{$estado}' readonly></input></td>
																<td>{$nombre}</td>
																<td>{$estado}</td>
																<td>{$fila['fecha_creacion']}</td>
																<td>{$fila['fecha']}</td>
																<td>"; if(empty($enlace)){echo $cantidad;}else{echo $enlace;}echo "</td>
															</tr>
														";														
													}													
												}
											}
										}
										else
										{
											echo "<script language='javascript' type='text/javascript'>alert('Hubo un problema al cargar las solicitudes de la base de datos.')</script>";
										}										
									?>
							</table>					
						</div>					
					</form>
				</fieldset>	
			</center>
		</div>
		<?php include("footer.html")?>
	</body>
</html>