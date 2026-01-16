<?php 
include("lib/motor.php");

$s = new Seguridad();



if(!isset($_SESSION)){
	session_start();
}

$domain = $_SERVER['HTTP_HOST'];  
$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
$includes = $_SESSION['m']->obtenerIncludes($url);

if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Autorizador") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0 or $_SESSION['usuario']=='mejia.yissette@promesecal.lan' or in_array("Especial", $_SESSION['permisos']))
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
		$modo= "<a href='autorizacionhe.php' class='btn btn-default btn-block'><img src='pics/left.png' height=16 width=16 title='Solicitudes Pendientes'></a>";
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
	$modo = "<a href='autorizacionhe.php?mode' class='btn btn-default btn-block'><img src='pics/right.png' height=16 width=16 title='Solicitudes Modificadas'></a>";	
}
?>
<html>
	<head>
		<title>Autorizador de Horas Extra</title>
		<meta charset='utf-8'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo $includes;?>
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
				<form method="post" action="Autorizacionhe.php">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<fieldset style="width:100%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
								<legend><?php if($modificacion){echo "<h2>Solicitudes de Horas Extra modificadas</h2>";}else{echo "<h2>Solicitudes de Horas Extra pendientes</h2>";}?></legend>
								<div id="horaextra" style="width: 100%;height: 40%;overflow: auto;border-radius:5px;">
									<table class="table table-bordered table-striped" style='width:100%;'>
										<thead>
											<tr>
												<th>Seleccionar</th><th>No.Solicitud</th><th>Fecha de Solicitud</th><th>Tipo de Actividad</th><?php if($modificacion) echo "<th>Autorizado</th>";?><th>Departamento</th><th>Usuario</th>
											</tr>
										</thead>
										<tbody>
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
															<tr >
																<td><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
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
															<tr>
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
										</tbody>
									</table>
								</div>
							</fieldset>
						</div>
					</div>	
					<div class="row ">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<fieldset style="width:100%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
								<legend>Opciones</legend>
								<input type="hidden" name="txtID" value="<?php echo $sa->getId();?>"></input>
								<input type="hidden" name="txtSolicitud" value="<?php echo $sa->getId_solicitud();?>"  readonly>
								<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
									<div class="form-group">
										<label>No. Oficio:</label>
										<input type="text" name="txtNoOficio" value="<?php echo $nombre; ?>" class="form-control"></input>
									</div>
									<div class="form-group">
										<label>Tipo de Solicitud:</label>
										<input type="text" name="txtTipo" value="<?php echo $sa->getTipo();?>" readonly class="form-control"></input>
									</div>									
								</div>
								<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
									<div class="form-group form-inline">
										<label>Autorizado:</label>
										<?php 
											if($modificacion)
											{
												if($sa->getAutorizado()==true)
												{
													echo "
													Si<input class='form-control' type='radio' name='radAutorizado' value='1' checked='checked'></input>
													/ No<input class='form-control' type='radio' name='radAutorizado' value='0'></input>	";										
												}
												else
												{
													echo "
													Si<input class='form-control' type='radio' name='radAutorizado' value='1'></input>
													/ No<input class='form-control' type='radio' name='radAutorizado' value='0' checked='checked'></input>	";																				
												}
											}
											else
											{
												echo "
												Si<input class='form-control' type='radio' name='radAutorizado' value='1'></input>
												/ No<input class='form-control' type='radio' name='radAutorizado' value='0'></input>	";										
											}
										?>
									</div>
									<div class="form-group">
										<label>Comentario:</label>
										<textarea name="txtComentario" class="form-control"><?php echo $sa->getComentario();?></textarea>
									</div>									
								</div>
								<div class="row">
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
										<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
											<div class="form-group">
												<?php echo $modo;?>
											</div>								
										</div>
										<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
											<div class="form-group">
												<button type="submit" name="btnImprimir" class='btn btn-info btn-block' title="Imprimir"><img src='pics/print.png'></button> 
											</div>								
										</div>
										<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
											<div class="form-group">
												<button type="submit" name="btnEditar" class='btn btn-warning btn-block' title="Editar"><img src='pics/edit.png'></button>
											</div>								
										</div>
										<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
											<div class="form-group">
												<button type="submit" name="btnGuardar" class='btn btn-primary btn-block' title="Guardar Cambios"><img src='pics/sauvegardes.png' height="16" width="16"></button>
											</div>								
										</div>
									</div>
								</div>	
							</fieldset>
						</div>
					</div>	
				</form>
			</div>	
		</div>
		<?php include("footer.html");?>
	</body>
</html>