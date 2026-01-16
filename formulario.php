<?php 
	include('lib/motor.php');
	
	$s = new Seguridad();
	if(!isset($_SESSION)){
		session_start();
	}
	if($s->verificar())
	{
		if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0)
		{
			$sq = new SolicitudHE();
			$tr = new Transporte();
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
			header('Location:formulario.php');	
		}
		else
		{
			if($_POST['txtOficio']!='' && $_POST['txtObjetivo']!='' && $_POST['txtDescripcion']!='' && $_POST['txtAlcance']!='' && $_POST['txtMinutos']!='' && $_POST['txtHoras']!='' && $_POST['txtFecha']!='')
			{
				if(is_numeric($_POST['txtHoras']) )
				{
					if($_POST['txtHoras']==0 || $_POST['txtHoras']=='')
					{
						$sq->setTiempoEstimado("00".":".$_POST['txtMinutos']);
					}
					else
					{
						$sq->setTiempoEstimado($_POST['txtHoras'].":".$_POST['txtMinutos']);					
					}
					
					if(isset($_POST['chkProgramado']) )
					{
						$sq->setProgramado('s');
					}
					else
					{
						$sq->setProgramado('n');
					}
					$sq->setID($_POST['txtID']);
					$tr->setID($_POST['txtID']);
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
					$sq->guardar();	
					$tr->setArea($_POST['txtArea']);
					$tr->setFecha($_POST['txtFecha']);
					$tr->setDepartamento($_SESSION['dpto']);
					$tr->guardar();
					ManejadorTransporte::asignarTransporte($tr->getID(),$sq->getID());
					$msjError ="";
					header('Location:solicitudeshoras.php');						
				}
				else
				{
					$msjError = "El campo Hora debe ser numerico.";
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
		$sq->setID($_GET['edit']);
		$sq->cargar();
	}
	else if (isset($_GET['del']))
	{
		$sq->eliminar($_GET['del']);
		$tr->eliminar($_GET['del']);
		header('Location:solicitudeshoras.php');
	}
?>
<html>
	<head>
		<title>Formulario de Solicitud Horas Extra</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
	</head>
	<body class="   ext-webkit ext-chrome">
		<?php include("menu.html");?>
		<div id='page'>
			<center>
				<div id='form' style="width: 40%">
					<br>
					<form method='post' action='formulario.php'>
						<fieldset style='border:solid;border-width:1px; border-color: #000;-moz-border-radius: 8px;-webkit-border-radius: 8px;-o-border-radius: 8px;'> 
							<legend style='font-weight:bold;'>Datos del Formulario</legend>
							<table width='100%' class="tab_cadre_fixe">
								<tr class="tab_bg_1">
									<td width='100%'><?php echo $msjError;?></td>
									<td width='100%'><input type='hidden' name='txtID' value='<?php echo $sq->getID();?>'></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td><label id='lb'>No. Oficio:</label></td>
									<td><input id='txtForm' type="text" name='txtOficio' value='<?php echo $sq->getNoOficio();?>'></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td><label id='lb'>Objetivo del periodo de trabajo extraordinario:</label></td>
									<td><textarea id='txtArea' name="txtObjetivo" rows='4' cols='25'><?php echo $sq->getObjetivo();?></textarea></td>
								</tr>
								<tr class="tab_bg_1">
									<td><label id='lb'>Breve descripcion del trabajo extraordinario:</label></td>
									<td><textarea id='txtArea' name="txtDescripcion" rows='4' cols='25'><?php echo $sq->getDescripcion();?></textarea></td>
								</tr>
								<tr class="tab_bg_1">
									<td><label id='lb'>Alcance de la jornada extraordinaria:</label></td>
									<td><textarea id='txtArea' name="txtAlcance" rows='4' cols='25'><?php echo $sq->getAlcance();?></textarea></td>
								</tr>
								<tr class="tab_bg_1">
									<td><label id='lb'>Actividad programada:</label></td>
									<td><input type="checkbox" name='chkProgramado' <?php
									if($sq->getProgramado()=='s') 
									{
										echo 'checked';
									}?>></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td><label id='lb'>Tiempo Estimado:</label></td>
									<td><input type="text" name="txtHoras" style='width:30px;' <?php 
									global $tiempo;
									$tiempo = $sq->getTiempoEstimado();
									if($tiempo!='')
									{
										$valor = explode(":",$tiempo);
										echo "value='{$valor[0]}'";
									}
									else
									{
										echo "value='00'";
									}
									?>>:<input type="number" name="txtMinutos" min="0" max="59" <?php 
									if($tiempo!='')
									{
										$valor = explode(":",$tiempo);
										echo "value='{$valor[1]}'";
									}
									else
									{
										echo "value='00'";
									}
									?>></td>
								</tr>
								
								<tr class="tab_bg_1">
									<td><label id='lb'>Fecha:</label></td>
									<td><input type='date' name='txtFecha' value='<?php 
									if($sq->getFecha()=='' || $sq->getFecha()=='0000-00-00')
									{
										echo '';
									}
									else
									{
										echo date("Y-m-d",strtotime($sq->getFecha()));
									}
									?>'></input></td>
								</tr>
								<tr>
									<td>Area:<td><input type="text" name="txtArea" value="" required></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td align='right' colspan =2><input type='submit' name='btnNuevo' value='Nuevo' class='submit'></input><input type='submit' name='btnGuardar' value='Guardar' class='submit'></input></td>
								</tr>
							</table>
						</fieldset>			
					<br>
				</div>	
					</form>
			</center>
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