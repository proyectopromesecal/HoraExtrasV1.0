<?php 
include('lib/motor.php');

$s = new Seguridad();

if(!isset($_SESSION)){
	session_start();
}

if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0 or strcmp($s->verificarTipo(), "Asistente") ==0)
	{
		$_SESSION['rutaActual']="Solicitudes > Listado de formularios de hora extra";
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
					$t = ManejadorSolicitud::obtenerTransporte($idSl);
					switch($est)
					{
						case "Nueva":  
						header("Location:formulario2.php?edit={$idSl}&t={$t}");
						break;
						case "Enviada":
						header("Location:formulario2.php?edit={$idSl}&t={$t}");
						break;
						case "Aprobada":
						echo "<script>alert('No puede editar una solicitud aprobada');</script>";	
						//header("Location:formulario2.php?edit={$idSl}&t={$t}");
						break;
						case "Rechazada":
						header("Location:formulario2.php?edit={$idSl}&t={$t}");
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
		header('Location:formulario2.php');
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
					$t = ManejadorSolicitud::obtenerTransporte($idSl);
					switch($est)
					{
						case "Nueva":  
						header("Location:formulario2.php?del={$idSl}&t={$t}");
						break;
						case "Enviada":
						header("Location:formulario2.php?del={$idSl}&t={$t}");
						break;
						case "Aprobada":
						echo "<script>alert('No puede eliminar una solicitud aprobada');</script>";	
						break;
						case "Rechazada":
						header("Location:formulario2.php?del={$idSl}&t={$t}");	
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
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		if($_POST['slcFiltro']=='estado')
		{
			$est = $_POST['slcEstados'];
			$_SESSION['est']=$est;		
		}
		else if ($_POST['slcFiltro']=='fecha2')
		{
			if(strcmp($s->verificarTipo(), "SuperAdmin") ==0)
			{
				$query="SELECT * 
						FROM solicitudhe
						WHERE fecha = '{$_POST['slcF']}'";
			}
			else
			{
				$query="SELECT * 
						FROM solicitudhe
						WHERE fecha = '{$_POST['slcF']}'
						AND usr='{$_SESSION['usuario']}'";			
			}

			$query.=" order by fecha desc";
			$rsCustom = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		}
		else if ($_POST['slcFiltro']=='fecha')
		{
			if(strcmp($s->verificarTipo(), "SuperAdmin") ==0)
			{
				$query="SELECT * 
						FROM solicitudhe
						WHERE fecha_creacion = '{$_POST['slcF']}'";				
			}
			else
			{
				$query="SELECT * 
						FROM solicitudhe
						WHERE fecha_creacion = '{$_POST['slcF']}'
						AND usr='{$_SESSION['usuario']}'";			
			}
			$query.=" order by fecha_creacion desc";
			$rsCustom = sqlsrv_query($_SESSION['con'],$query, $params, $options);	
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
					$t = ManejadorSolicitud::obtenerTransporte($idSl);
					echo "<script>window.open('solicitudPDF.php?s={$idSl}')</script>";
					//echo "<script>window.open('TransportePDF.php?s={$t}')</script>";
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
		<title>Solicitudes de Horas Extra</title>
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
					<form method="post" action="solicitudeshoras.php">
						<div style="width:100%;" >
							<button type="submit" name="btnImprimir" title="Imprimir"><img src='pics/print.png'></button> &nbsp 
							<button type="submit" name="btnNuevo" title="Agregar Nueva Solicitud"><img src='pics/add.png'></button> &nbsp 
							<button type="submit" name="btnEditar" title="Editar"><img src='pics/edit.png'></button> &nbsp 
							<button type="submit" name="btnEliminar" title="Eliminar" ><img src='pics/delete.png' height='16' width='16'></button>&nbsp 
							<select onchange="cambiar()" id="select" name='slcFiltro'><?php ManejadorSolicitud::obtenerFiltros();?></select> <label style="color:white;">ES</label>
							<select id="slc" class="" name='slcEstados'><?php ManejadorSolicitud::obtenerEstados();?></select>
							<input id='dt' type="date" name="slcF" class='oculto'>&nbsp 
							<button type='submit' name='btnBuscar' class='submit'>Buscar</button>
						</div><br>
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
												$rs =ManejadorSolicitud::obtenerSolicitudes("");
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
												$rs =ManejadorSolicitud::obtenerSolicitudes($_SESSION['dpto'], $_SESSION['usuario']);
											}
										}
										if($rs)
										{
											while($fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
											{
												$enlace="";
												$estado = ManejadorSolicitud::verEstado($fila['id']);
												$comentario= ManejadorSAutorizadas::obtenerComentario($fila['id']);
												if(empty($est) || strcmp($est, 'todos')==0)
												{
													$cantidad = ManejadorSolicitud::cantidadEmpleados($fila['id']);
													if($cantidad <= 0) 
													{
														$t = ManejadorSolicitud::obtenerTransporte($fila['id']);
														$enlace = "<a href='empform.php?f={$fila['id']}&t={$t}'>{$cantidad}</a>";
													}
													echo "
														<tr class='tab_bg_2'>
															<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}-{$estado}' readonly></input></td>
															<td>{$fila['noOficio']}</td>
															<td title='{$comentario}'>{$estado}</td>
															<td>{$fila['fecha_creacion']->format('d/m/Y')}</td>
															<td>{$fila['fecha']->format('d/m/Y')}</td>
															<td>"; if(empty($enlace)){echo $cantidad;}else{echo $enlace;}echo "</td>
														</tr>
													";
												}
												else
												{
													if(strcmp($est, $estado)==0)
													{
														$cantidad = ManejadorSolicitud::cantidadEmpleados($fila['id']);
														if($cantidad <= 0) 
														{
															$t = ManejadorSolicitud::obtenerTransporte($fila['id']);
															$enlace = "<a href='empform.php?f={$fila['id']}&t={$t}'>{$cantidad}</a>";
														}
														echo "
															<tr class='tab_bg_2'>
																<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}-{$estado}' readonly></input></td>
																<td>{$fila['noOficio']}</td>
																<td title='{$comentario}'>{$estado}</td>
																<td>{$fila['fecha_creacion']->format('d/m/Y')}</td>
																<td>{$fila['fecha']->format('d/m/Y')}</td>
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