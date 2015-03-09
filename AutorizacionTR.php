<?php 
	include("lib/motor.php");
	
	$s = new Seguridad();
	
	if(!isset($_SESSION)){
		session_start();
	}
	
	if($s->verificar())
	{
		if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0)
		{	
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
			$sa->guardar();
			if($modificacion)
			{
				header('Location:AutorizacionTR.php?mode');
			}
			else
			{
				header('Location:AutorizacionTR.php');
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
						echo "<script> window.open('TransportePDF.php?s={$valor}','_blank');</script>";
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
							header("Location:AutorizacionTR.php?mode=mod&s={$valor}&typ=Transporte");
						}
						else
						{
							header("Location:AutorizacionTR.php?s={$valor}&t=ft");
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
			if ($_GET['t']=="ft")
			{
				$sa->setId_solicitud($_GET['s']);
				$nombre = ManejadorTransporte::obtenerNombre($_GET['s']);
				$sa->setTipo("Transporte");
			}
		}
	}
	if (isset($_GET['mode']))
	{
		$modificacion = true;
		$_SESSION['modoMod']=$modificacion;
		if($modificacion)
		{
			$modo= "<a href='autorizaciontr.php'><img src='pics/left.png' height=16 width=16 title='Solicitudes Pendientes'></a>";
		}
		if(isset($_GET['typ']))
		{
			if($_GET['typ']=="Transporte")
			{
				$nombre = ManejadorTransporte::obtenerNombre($_GET['s']);
			}
			$sa->setId($_GET['s']);
			$sa->cargar($_GET['typ']);
		}	
	}
	else
	{
		$modificacion = false;
		$_SESSION['modoMod']=$modificacion;
		$modo = "<a href='autorizaciontr.php?mode'><img src='pics/right.png' height=16 width=16 title='Solicitudes Modificadas'></a>";	
	}
?>
<html>
	<head>
		<title>Autorizador de Transporte</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<style>
			textarea
			{
				resize: none;
			}
		</style>
	</head>
	<body>
		<?php include("menu.html")?>
		<div id='page'>
			<center>
				<form method="post" action="AutorizacionTR.php">
					<fieldset style="width:50%;border-radius:8px;">
						<div id="form" style="width:100%;">
							<table class="tab_cadre_fixe" style='width:80%;' >
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
													Si<input type='radio' name='radAutorizado' value='true' checked='checked'></input>
													/ No<input type='radio' name='radAutorizado' value='false'></input>	";										
												}
												else
												{
													echo "
													Si<input type='radio' name='radAutorizado' value='true'></input>
													/ No<input type='radio' name='radAutorizado' value='false' checked='checked'></input>	";																				
												}
											}
											else
											{
												echo "
												Si<input type='radio' name='radAutorizado' value='true'></input>
												/ No<input type='radio' name='radAutorizado' value='false'></input>	";										
											}
										?>	
									</td>
									<td>Comentario:</td>
									<td><textarea rows="4" cols="30" name="txtComentario"><?php echo $sa->getComentario();?></textarea></td>
								</tr>
							</table>
							<br>
							<div style="width:100%;" >
								<?php echo $modo;?>
								<button type="submit" name="btnImprimir" class='submit' title="Imprimir"><img src='pics/print.png'></button> &nbsp 
								<button type="submit" name="btnEditar" title="Editar"><img src='pics/edit.png'></button> &nbsp 
								<button type="submit" name="btnGuardar" title="Guardar Cambios"><img src='pics/sauvegardes.png' height="16" width="16"></button> &nbsp 
							</div>						
						</div>
						<div id="horaextra" style="width: 100%;height: 40%;display:inline-block;overflow: auto;">
							<legend><?php if($modificacion){echo "<b>Solicitudes de Transporte modificadas</b>";}else{echo "<b>Solicitudes de Transporte pendientes</b>";}?></legend>
							<br>
							<table class="tab_cadre_fixe" style='width:90%;'>
								<th width="5%">Seleccionar</th><th width="15%">No.Solicitud</th><th width="10%">Fecha de Solicitud</th><th width="15%">Formulario de Hora Extra</th>
								<?php 
									if($modificacion)
									{
										$rs = ManejadorSAutorizadas::obtenerTransporte();
										if($rs)
										{
											while($fila=mysql_fetch_assoc($rs))
											{
												echo "
												<tr class='tab_bg_2'>
													<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
													<td>{$fila['no_oficio']}</td>
													<td>{$fila['fecha']}</td>
													<td>{$fila['HEnooficio']}</td>
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
										$rs =ManejadorSAutorizadas::obtenerFormulariosTransporte();
										if($rs)
										{
											while($fila=mysql_fetch_assoc($rs))
											{
												echo "
												<tr class='tab_bg_2'>
													<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
													<td>{$fila['no_oficio']}</td>
													<td>{$fila['fecha']}</td>
													<td>{$fila['HEnooficio']}</td>
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
						</div>					
					</fieldset>
				</form>
			</center>	
		</div>
		<?php include("footer.html")?>
	</body>
</html>