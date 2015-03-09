<?php 
	include("lib/motor.php");
	
	$s = new Seguridad();
	
	if(!isset($_SESSION)){
		session_start();
	}
	
	if($s->verificar())
	{
		if(strcmp($s->verificarTipo(), "Autorizador") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0)
		{	
			$_SESSION['rutaActual']="Autorizaciones > Hora Extra";
			$sa = new SolicitudAutorizada();
			$nombre ="";
			$modificacion=false;
			$modo="";			
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
			$sa->setId($_POST['txtID']);
			$sa->setId_solicitud($_POST['txtSolicitud']);
			$sa->setAutorizado($_POST['radAutorizado']);
			$sa->setNoOficio($_POST['txtNoOficio']);
			$sa->setComentario($_POST['txtComentario']);
			$sa->setTipo($_POST['txtTipo']);		
			$sa->setFecha_c(date('Y-m-d'));
			$sa->guardar();
			/*
			echo "<pre>";
			echo var_dump($_POST);
			echo "</pre>";
			*/
			if($modificacion)
			{
				header('Location:AutorizacionHE.php?mode');
			}
			else
			{
				header('Location:AutorizacionHE.php');
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
						echo "<script> window.open('SolicitudPDF.php?s={$valor}','_blank');</script>";
					}			
				}	
			}
			else
			{
				echo "<script>alert('Seleccione una solicitud para imprimir);</script>";
			}
		}
		else if(isset($_POST['btnEditar']))
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
						if($_SESSION['modoMod'])
						{
							header("Location:AutorizacionHE.php?mode=mod&s={$valor}&typ=HoraExtra");
						}
						else
						{
							header("Location:AutorizacionHE.php?s={$valor}&t=she");
						}
					}			
				}
			}
			else
			{
				echo "<script>alert('Seleccione una solicitud para editar);</script>";
			}
		}
		$nombre ="";

	}
	elseif (isset($_GET['s']) && isset($_GET['t']))
	{
		if(!empty($_GET['s']) && !empty($_GET['t']))
		{
			if($_GET['t']=="she")
			{
				$sa->setId_solicitud($_GET['s']);
				$nombre = ManejadorSolicitud::obtenerNoOficio($_GET['s']);
				$sa->setTipo("HoraExtra");
			}
		}
	}
	if (isset($_GET['mode']))
	{
		$modificacion = true;
		$_SESSION['modoMod']=$modificacion;
		if($modificacion)
		{
			$modo= "<a href='autorizacionhe.php'><img src='pics/left.png' height=16 width=16 title='Solicitudes Pendientes'></a>";
		}
		if(isset($_GET['typ']))
		{
			if ($_GET['typ'] =="HoraExtra")
			{
				$nombre = ManejadorSolicitud::obtenerNoOficio($_GET['s']);
			}
			$sa->setId($_GET['s']);
			$sa->cargar($_GET['typ']);
		}	
	}
	else
	{
		$modificacion = false;
		$_SESSION['modoMod']=$modificacion;
		$modo = "<a href='autorizacionhe.php?mode'><img src='pics/right.png' height=16 width=16 title='Solicitudes Modificadas'></a>";	
	}
?>
<html>
	<head>
		<title>Autorizador de Horas Extra</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<style>
			textarea
			{
				resize: none;
			}
		</style>
	</head>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<center>
				<form method="post" action="Autorizacionhe.php">
					<fieldset style="width:80%;border-radius:8px;">
						<legend><?php if($modificacion){echo "<h2>Solicitudes de Horas Extra modificadas</h2>";}else{echo "<h2>Solicitudes de Horas Extra pendientes</h2>";}?></legend>
						<br>
						<div id="horaextra" style="width: 100%;height: 40%;overflow: auto;border-radius:5px;">	
							<table class="tab_cadre_fixe" style='width:80%;'>
								<th width="2%">Seleccionar</th><th width="20%">No.Solicitud</th><th width="10%">Fecha de Solicitud</th><th width="20%">Tipo de Actividad</th><?php if($modificacion) echo "<th width='10%'>Autorizado</th>";?><th width="20%">Departamento</th><th>Usuario</th>
								<?php 
									if($modificacion)
									{
										$rs = ManejadorSAutorizadas::obtenerHorasExtra();
										if($rs)
										{
											while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
											{
												$autorizado;
												if($fila['autorizado'])
												{
													$autorizado = "Si";
												}
												else
												{
													$autorizado = "No";
												}
												$username = explode("@",$fila['usr']);
												echo "
												<tr class='tab_bg_2'>
													<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
													<td>". ManejadorSolicitud::obtenerNoOficio($fila['id'])."</td>
													<td>{$fila['fecha']->format('d/m/Y')}</td>
													<td>{$fila['programado']}</td>
													<td>{$autorizado}</td>
													<td>{$fila['departamento']}</td>
													<td>{$username[0]}</td>
												</tr>";
											}
										}
										else
										{
											echo "No hay solicitudes disponibles";
										}	
									}
									else
									{
										$rs =ManejadorSAutorizadas::obtenerSolicitudesHE();
										if($rs)
										{
											while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
											{
												$username = explode("@",$fila['usr']);
												echo "
												<tr class='tab_bg_2'>
													<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
													<td>{$fila['noOficio']}</td>
													<td>{$fila['fecha']->format('d/m/Y')}</td>
													<td>{$fila['programado']}</td>
													<td>{$fila['departamento']}</td>
													<td>{$username[0]}</td>
												</tr>";
											}
										}
										else
										{
											echo "No hay solicitudes disponibles";
										}
									}
								?>				
							</table>
							
						</div><br><br>
						<div id="form" style="width:100%;">
							<table class="tab_cadre_fixe" style='width:50%;' >
								<tr class="tab_bg_1">
									<td><input type="hidden" name="txtID" value="<?php echo $sa->getId();?>"></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td colspan=2 align='center'><?php echo $nombre;?></td>			
								</tr>
									<td><input type="hidden" name="txtSolicitud" value="<?php echo $sa->getId_solicitud();?>"  readonly></input></td>					
								</tr>
								<tr class="tab_bg_1">
									<td>Tipo Solic:</td>
									<td><input type="text" name="txtTipo" value="<?php echo $sa->getTipo();?>" readonly></input></td>
									<td>No. Oficio:</td>
									<td><input type="text" name="txtNoOficio" value="<?php echo $sa->getNoOficio(); ?>"></input></td>					
								</tr>
								<tr class="tab_bg_1">
									<td>Autorizado:</td>
									<td>
										<?php 
											if($modificacion)
											{
												if($sa->getAutorizado()==true)
												{
													echo "
													Si<input type='radio' name='radAutorizado' value='1' checked='checked'></input>
													/ No<input type='radio' name='radAutorizado' value='0'></input>	";										
												}
												else
												{
													echo "
													Si<input type='radio' name='radAutorizado' value='1'></input>
													/ No<input type='radio' name='radAutorizado' value='0' checked='checked'></input>	";																				
												}
											}
											else
											{
												echo "
												Si<input type='radio' name='radAutorizado' value='1'></input>
												/ No<input type='radio' name='radAutorizado' value='0'></input>	";										
											}
										?>	
									</td>
									<td>Comentario:</td>
									<td><textarea rows="4" cols="30" name="txtComentario"><?php echo $sa->getComentario();?></textarea></td>
								</tr>
								<tr>
									<td align="center">
										<?php echo $modo;?>
									</td>
									<td align="center">
										<button type="submit" name="btnImprimir" class='submit' title="Imprimir"><img src='pics/print.png'></button> 
									</td>
									<td align="center">
										<button type="submit" name="btnEditar" class='submit' title="Editar"><img src='pics/edit.png'></button>
									</td>
									<td align="center">
										<button type="submit" name="btnGuardar" class='submit' title="Guardar Cambios"><img src='pics/sauvegardes.png' height="16" width="16"></button>
									</td>
								</tr>
							</table>						
						</div>					
					</fieldset>
				</form>
			</center>	
		</div>
		<?php include("footer.html");?>
	</body>
</html>