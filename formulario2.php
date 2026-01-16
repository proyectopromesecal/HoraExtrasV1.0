<?php 
	include('lib/motor.php');
	
	$s = new Seguridad();
	if(!isset($_SESSION)){
		session_start();
	}

	$domain = $_SERVER['HTTP_HOST'];  
	$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
	$includes = $_SESSION['m']->obtenerIncludes($url);

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
		<meta charset='utf-8'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo $includes;?>
		<script src="css/jquery.maskedinput.js" type="text/javascript"></script>
		<script>
			$(function(){
				$("#hora").mask("99:99");

				$("form").submit(function() {
					$('#btnSave').hide();
				});
			});
		</script>

		<style>
			#contenido{
				position: fixed;
			    top: 120px;
			    bottom: 100px;
			    left: 0;
			    right: 0;
			    overflow: auto;
			}
		</style>
	</head>
	<body>
		<header>
			<?php include("menu.html");?>
		</header>
		
		<div id='contenido'>
			<div class="container-fluid body-content">
				<fieldset class="well bs-component">
					<form action="" method="POST" role="form">
						<input type='hidden' name='txtID' value='<?php echo $sq->getID();?>'></input>
						<input type='hidden' name='txtIDT' value='<?php echo $tr->getID();?>'></input>
						<legend>Formulario de Solicitud</legend>
						<div class="row">
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
								<div class="form-group">
									<label for="">No. Solicitud:</label>
									<input class="form-control" id='txtForm' type="text" name='txtOficio' value='<?php echo $sq->getNoOficio();?>' readonly required>
								</div>

								<div class="form-group">
									<label for="">Objetivo del per&iacute;odo de trabajo extraordinario:</label>
									<textarea id='txtArea' name="txtObjetivo" placeholder="Cu&aacute;l es el motivo por el cual hace esta solicitud" class="form-control" required><?php echo $sq->getObjetivo();?></textarea>
								</div>

								<div class="form-group">
									<label for="">Alcance de la jornada extraordinaria:</label>
									<textarea id='txtArea' name="txtAlcance" placeholder="Si es un trabajo que se divide en varios procesos, cuales estar&aacute;n comprendidos en esta solicitud" class="form-control" required><?php echo $sq->getAlcance();?></textarea>
								</div>

								<div class="form-group">
									<label for="">Descripci&oacute;n del per&iacute;odo de trabajo extraordinario:</label>
									<textarea id='txtArea' name="txtDescripcion" placeholder="Breve descripci&oacute;n del trabajo a realizar" class="form-control" required><?php echo $sq->getDescripcion();?></textarea>
								</div>

							</div>
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
								<div class="form-group">
									<label for="">&Aacute;rea:</label>
									<input type="text" name="txtArea" placeholder="Area a la que pertenece" value="<?php echo $tr->getArea();?>" class="form-control" required ></input>
								</div>

								<div class="form-group">
									<label for="">Tipo de Actividad:</label>
									<select name="slcProgramado" class="form-control">
										<option value="No Programado" <?php if(strcmp($sq->getProgramado(), "No Programado")==0) echo "selected";?>>No Programado</option>
										<option value="Programado" <?php if(strcmp($sq->getProgramado(), "Programado")==0) echo "selected";?>>Programado</option>
										<option value="Urgente" <?php if(strcmp($sq->getProgramado(), "Urgente")==0) echo "selected";?>>Urgente</option>
									</select>
								</div>

								<div class="form-group">
									<label for="">Fecha:</label>
										<input required class="form-control" type='date' name='txtFecha' value='<?php 
											if($sq->getFecha()=='' || $sq->getFecha()=='0000-00-00')
											{
												echo '';
											}
											else
											{
												echo date("Y-m-d",strtotime($sq->getFecha()->format('Y-m-d')));
											}
											?>'>
								</div>

								<div class="form-group">
									<label for="">Tiempo Estimado:</label>
									<input id="hora" size="5" required type="text" class="form-control" name="txtHoras" value="<?php echo $sq->getTiempoEstimado();?>">
								</div>
							</div>
						</div>
						<button name="btnBeneficiarios" style="float:right;" class="btn btn-primary" value="Beneficiarios" title="Añadir Beneficiarios" id="btnSave">Agregar Empleados</button> 
					</form>
				</fieldset>
			</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>
