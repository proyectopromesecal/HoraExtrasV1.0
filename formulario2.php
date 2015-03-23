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
			$sq = new SolicitudHE();
			$tr = new Transporte();
			$sq->setNoOficio(ManejadorSolicitud::generarNoOficio());
			$sq->setUsuario($_SESSION['usuario']);
			
			$_SESSION['rutaActual']="Solicitudes > Formulario de hora extra";
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
			header('Location:formulario2.php');
		}
		else if($_POST['btnBeneficiarios'])
		{
			if ($_POST['txtID'] >0) {
				$s = $_POST['txtID'];
				$t = $_POST['txtIDT'];
				header("Location:empform.php?f={$s}&t={$t}");	
			}	
			else
			{
				$hora = explode(':',$_POST['txtHoras']);
				if($hora[0]== 0 && $hora[1]<30)
				{
					echo "<script>alert('El tiempo de trabajo estimado no puede ser menor 30 minutos');</script>";	
				}
				else
				{
					$dia = date('Y-m-d',time()+172800);
					$hora = date('H:i:s');
					$crear = true;

					if(strcmp($_POST['slcProgramado'], "Programado")==0)
					{
						if($_POST['txtFecha'] < $dia)
						{
							$crear = false;
							echo "<script>alert('Si la actividad es programada debe solicitarse con 2 dias de anticipacion');</script>";
						}
					}
					if ($crear) {
						$sq->setProgramado($_POST['slcProgramado']);
						$sq->setID($_POST['txtID']);
						$tr->setID($_POST['txtIDT']);
						if($sq->getID()==0 ||$sq->getID()=='')
						{
							$sq->setFechaCreacion(date("Y-m-d"));
							$tr->setFechaCreacion(date("Y-m-d"));
						}
						$sq->setNoOficio($_POST['txtOficio']);
						$sq->setObjetivo($_POST['txtObjetivo']);
						$sq->setDescripcion($_POST['txtDescripcion']);
						$sq->setAlcance($_POST['txtAlcance']);
						$sq->setFecha($_POST['txtFecha']);
						$sq->setDepartamento($_SESSION['dpto']);
						$sq->setTiempoEstimado($_POST['txtHoras']);
						$sq->setHora($hora);
						$sq->guardar();	
						$x = $tr->getNoOficio();
						if(empty($x))
						{
							$tr->setNoOficio(ManejadorTransporte::generarNoOficio());
						}
						$tr->setArea($_POST['txtArea']);
						$tr->setFecha($_POST['txtFecha']);
						$tr->setDepartamento($_SESSION['dpto']);
						$tr->guardar();	
						if(ManejadorTransporte::verificarHeTrans($tr->getID()))
						{
							ManejadorTransporte::asignarTransporte($tr->getID(), $sq->getID());
						}
						header("Location:empform.php?f={$sq->getID()}&t={$tr->getID()}");							
					}

				}
			}		
		} 
	}
	else if (isset($_GET['edit']) && isset($_GET['t']))
	{
		$sq->setID($_GET['edit']);
		$tr->setID($_GET['t']);
		$sq->cargar();
		$tr->cargar();
	}
	else if (isset($_GET['del'])&& isset($_GET['t']))
	{
		$sq->eliminar($_GET['del']);
		$tr->eliminar($_GET['t']);
		ManejadorTransporte::eliminarRelacionHETR($_GET['t'],$_GET['del']);
		header('Location:solicitudeshoras.php');
	}
?>
<html>
	<head>
		<title>Formulario de Hora Extra</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<script src="js/jquery-1.11.0.min.js"></script>
		<script src="css/jquery-2.0.3.min.js" type="text/javascript"></script>
		<script src="css/jquery.maskedinput.js" type="text/javascript"></script>
		<script>
			jQuery(function($){
				$("#hora").mask("99:99");
				$("form").submit(function() {
				    $('#btnSave').prop("hidden", true);
				});
		        function cambiarpantalla() {
		            x = screen.width;
		            
		            if (x <=1024) 
		            {
		            	document.getElementById("divContainer").style.width = "94%";
		            }
		            else if (x>=1200 && x<1600)
		            {
		            	document.getElementById("divContainer").style.width = "75%";
		            }
		            else if (x>=1600)
		            {
		            	document.getElementById("divContainer").style.width = "60%";
		            }     
		        }
		        window.onload = cambiarpantalla;
			});
		</script>

		<style>
			textarea
			{
				resize: none;
				margin: 10px;
			}
			#input {
				margin: 10px;
				padding: 20px;
			}
			.vam {
			    vertical-align: middle;
			    display: inline-block;
			    float:left;
			    padding-right: 20px;
			    padding-bottom: 10px;
			}
		</style>
	</head>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<div style="width:90%" class="tab_cadre_fixe" id="divContainer">
				<form method="post" action="formulario2.php" id="form">
					<center><br>
						<input type='hidden' name='txtID' value='<?php echo $sq->getID();?>'></input>
						<input type='hidden' name='txtIDT' value='<?php echo $tr->getID();?>'></input>
						<div id="divIzquierdo" style="float:left;width:40%;border-radius:4px;padding-left:30px;height:40%;">
							<span class="vam"><label>No. Solicitud: &nbsp </label><input readonly id='txtForm' type="text" name='txtOficio' value='<?php echo $sq->getNoOficio();?>' required></input></span>
							<span class="vam"><label>Objetivo del periodo de trabajo extraordinario: </label><br><textarea id='txtArea' name="txtObjetivo" rows="6" cols="50" required><?php echo $sq->getObjetivo();?></textarea></span>					
							<span class="vam"><label>Alcance de la jornada extraordinaria: </label><br><textarea id='txtArea' name="txtAlcance" rows="6" cols="50" required><?php echo $sq->getAlcance();?></textarea></span>
							<span class="vam"><label>Area: &nbsp &nbsp &nbsp </label><input type="text" name="txtArea" value="<?php echo $tr->getArea();?>" required ></input></span>
						</div>
						
						<div id="divDerecho" style="float:right;width:40%;border-radius:4px; padding-right:30px;height:40%;">
							<span class="vam"><label>Breve descripcion del trabajo extraordinario: </label><br><textarea required id='txtArea' name="txtDescripcion" rows="6" cols="50"><?php echo $sq->getDescripcion();?></textarea><br></span>
							<span class="vam"><label>Tipo Actividad: &nbsp &nbsp &nbsp</label> 
							<select name="slcProgramado">
								<option value="No Programado" <?php if(strcmp($sq->getProgramado(), "No Programado")==0) echo "selected";?>>No Programado</option>
								<option value="Programado" <?php if(strcmp($sq->getProgramado(), "Programado")==0) echo "selected";?>>Programado</option>
								<option value="Urgente" <?php if(strcmp($sq->getProgramado(), "Urgente")==0) echo "selected";?>>Urgente</option>
							</select><br><br></span>
							<span class="vam"><label>Fecha: &nbsp &nbsp &nbsp </label> 
								<input required type='date' name='txtFecha' value='<?php 
									if($sq->getFecha()=='' || $sq->getFecha()=='0000-00-00')
									{
										echo '';
									}
									else
									{
										echo date("Y-m-d",strtotime($sq->getFecha()->format('Y-m-d')));
									}
									?>'>
							<br><br>
							</span>
							<span class="vam">
								<label>Tiempo Estimado: &nbsp &nbsp </label>
								<input id="hora" size="5" required type="text" name="txtHoras" value="<?php echo $sq->getTiempoEstimado();?>">
								<br><br>
							</span>
							
							<span class="vam">
								<button name="btnBeneficiarios" value="Beneficiarios" title="Añadir Beneficiarios" id="btnSave"><img src='pics/add.png'></button> BENEFICIARIOS &nbsp 
							</span>
						</div>
					</center>		
					<div style="width:100%;border-radius:8px;height:25%;overflow:auto;">
						<center>
							</br><br><br>
							<table width="70%" >
								<th width="8%">Cedula</th> <th width="15%">Nombre y Apellido</th> <th width="13%">Cargo</th>
								<?php 
									$rs =ManejadorSolicitud::obtenerBeneficiarios($sq->getID());
									if($rs)
									{
										if(sqlsrv_num_rows($rs) >0)
										{
											while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
											{
												echo "
													<tr class='tab_bg_2'>
														<td>{$fila['cedula']}</td>
														<td>{$fila['nombre']}</td>
														<td>{$fila['cargo']}</td>
													</tr>
												";												
											}
										}
									}
								?>
								<tr>
									<td colspan=2><input style="margin-left:65%;width:160px;"type="reset" name="btnNuevo" value="Limpiar Pantalla" class="submit"></input></td>
								</tr>
							</table>						
						</center>
						<br>
					</div>	
				</form>				
			</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>