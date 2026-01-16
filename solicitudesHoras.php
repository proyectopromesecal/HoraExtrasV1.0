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
				echo "<script >alert('Para editar seleccione solo 1 solicitud');</script>";	
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
						echo "<script type='text/javascript'>alert('No puede editar una solicitud aprobada');</script>";	
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
		<meta charset='utf-8'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
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
		<script type='text/javascript'>
			$(function() {
				$('#dt').hide();

				$('#select').change(function(){
  					if( this.value =='estado'){
  						$('#slc').show();
  						$('#dt').hide();
  					} 
  					else
  					{
  						$('#slc').hide();
  						$('#dt').show();
  					}
				});				
			});
		</script>
	</header>
	<body>
		<header>
			<?php include('menu.html');?>
		</header>

		<div id="contenido">
			<div class="container-fluid body-content">
				<form action="solicitudeshoras.php" method="POST" role="form" class="form-horizontal">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<fieldset style="width:80%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
								<legend>Opciones y b&uacute;squeda</legend>
									<div class="form-group">
										<div class="row" style="margin: 10px 0px;">
											<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
												<button type="submit" class="btn btn-default btn-block" name="btnImprimir" title="Imprimir"><img src='pics/print.png'></button>
											</div>
											<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
												<button type="submit" class="btn btn-info btn-block" name="btnNuevo" title="Agregar nueva solicitud"><img src='pics/add.png'></button>
											</div>
											<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
												<button type="submit" class="btn btn-warning btn-block" name="btnEditar" title="Editar"><img src='pics/edit.png'></button>
											</div>
											<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
												<button type="submit" class="btn btn-danger btn-block" name="btnEliminar" title="Eliminar"><img src='pics/delete.png' width="16" height="16"></button>
											</div>								
										</div>
										<div class="row form-inline text-center" style="margin: 10px 0px;">
											<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
												<div class="form-group ">
													<label><h4>Criterio de b&uacute;squeda:</h4></label>
												</div>							
											</div>
											<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
												<div class="form-group">
													<select id="select" class="form-control" name='slcFiltro'><?php ManejadorSolicitud::obtenerFiltros();?></select>
												</div>							
											</div>
											<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
												<div class="form-group">
													<select id="slc" class="form-control" name='slcEstados'><?php ManejadorSolicitud::obtenerEstados();?></select>		
													<input id='dt' type="date" name="slcF" class='form-control'>
												</div>
											</div>	
											<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
												<div class="form-group">
													<button type="submit" name="btnBuscar" class="btn btn-primary btn-block">Buscar</button>
												</div>
											</div>	
										</div>
									</div>
							</fieldset>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
								<legend>Solicitudes de Hora Extra</legend>
								<div style="height:50%;overflow:auto;">
									<table class="table table-bordered table-hover">
										<thead>
											<tr>
												<th>Seleccion</th><th>No.Solicitud</th><th>Estado</th><th>Fecha de creaci&oacute;n</th><th>Fecha solicitada</th><th >Cant. empleados</th>
											</tr>
										</thead>
										<tbody>
											<tr>
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
																	<tr>
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
																		<tr>
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
														echo "<script type='text/javascript'>alert('Hubo un problema al cargar las solicitudes de la base de datos.')</script>";
													}										
												?>
											</tr>
										</tbody>
									</table>				
								</div>							
							</fieldset>
						</div>
					</div>
				</form>
			</div>
		</div>
		<footer>
			<?php include('footer.html');?>
		</footer>
	</body>
</html>