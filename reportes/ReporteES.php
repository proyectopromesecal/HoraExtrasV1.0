<?php 
include('../lib/motor.php');

if(!isset($_SESSION)){
	session_start();
}

$domain = $_SERVER['HTTP_HOST'];  
$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
$includes = $_SESSION['m']->obtenerIncludes($url);
$s = new Seguridad();

if($s->verificar())
{
	$mortal=true;
	if (in_array("Viewer", $_SESSION['permisos']) || $s->verificarTipo() == "SuperAdmin")
	{
		$mortal = false;
	}
	$_SESSION['rutaActual']="Reportes > Entrada y Salida";
}
else
{
	header('Location:Login.php');
}

if($_POST)
{
	if(isset($_POST['btnBuscar']))
	{
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		
		$query="SELECT emp.nombre nombre, emp.codigo_empleado, cargo.nombre cargo, depto.nombre departamento, convert(varchar, hor.fecha,  103 ) AS fecha, convert(varchar, hor.horadeentrada,  108 ) AS horadeentrada, convert(varchar, hor.horadesalida,  108 ) AS horadesalida
				from empleado emp
				inner join horario hor on emp.id = hor.id_empleado
				inner join t_cargo cargo on cargo.id = emp.cargo
				inner join t_departamento depto on depto.id = emp.departamento 
				inner join grupo_empleados grups on grups.id_empleado = emp.id
				WHERE ";

		//concatenacion es el query personalizado
		$concatenacion = "";
		$concatenacion .= $query;

		if($_POST['slcFecha'] !='' && $_POST['slcFecha2'] !='')
		{
			$concatenacion .= " hor.fecha BETWEEN '{$_POST['slcFecha']}' and '{$_POST['slcFecha2']}' ";

			if($_POST['txtEmpleado']!='')
			{
				if(is_numeric($_POST['txtEmpleado']))
				{
					$concatenacion .= " and emp.codigo_empleado = '{$_POST['txtEmpleado']}'";				
				}
				else
				{
					if(strstr($_POST['txtEmpleado'], 'Codigo')== false)
					{
						echo "El codigo solo debe contener numeros.";
					}
				}
			}
			if (!$mortal) {
				$concatenacion = str_replace("inner join grupo_empleados grups on grups.id_empleado = emp.id", "", $concatenacion);
				if ($_POST['slcDepartamento'] != 'todos')
				{
					$concatenacion .= " and depto.id = '{$_POST['slcDepartamento']}'";
				}
			}
			else
			{
				$concatenacion.= "AND grups.id_secretaria = {$_SESSION['id']}";
			}
			if ($_POST['slcOrdenar'])
			{
				$concatenacion .= " ORDER BY {$_POST['slcOrdenar']} ";				
			}
			$rs = sqlsrv_query($_SESSION['con'],$concatenacion, $params, $options);	
			$_SESSION['rs'] = $rs;	
		}
		else
		{
			echo "<script>alert('Debe de seleccionar un rango de fecha')</script>";
		}

	}
	else if (isset($_POST['btnReporte']))
	{
		if (!isset($_SESSION['datos']) || empty($_SESSION['datos'])) {
			echo "<script>alert('No hay datos disponibles para el reporte.');</script>";	
		}
		else
		{
			echo "<script>window.open('reportePunch.php','_blank');</script>";	
		}
		
	}
}
?>
<html>
	<header>
		<title>Reportes de Entrada y Salida</title>
		<meta charset='utf-8'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo $includes;?>
		<style type="text/css">
			#contenido{
				position: fixed;
			    top: 120px;
			    bottom: 100px;
			    left: 0;
			    right: 0;
			    overflow: auto;
			}
		</style>
	</header>
	<body>
		<header>
			<?php include("../menu.html");?>
		</header>
		
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method="post" action="ReporteES.php">
					<fieldset class="well bs-component" style="width:95%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;">
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<div class="form-group">
										<label>Fecha Inicial: </label>
										<input type="date" name="slcFecha" value='' class="form-control"></input>
									</div>
								</div>
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<div class="form-group">
										<label>Fecha Final: </label>
										<input type="date" name="slcFecha2" value='' class="form-control"></input>
									</div>
								</div>
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<div class="form-group">
										<label>Ordenar por: </label>
										<select name='slcOrdenar' class="form-control"><?php Manejador::obtenerOrdenamientos();?></select>
									</div>									
								</div>
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<div class="form-group">
										<label>Filtrar por Empleado: </label><input class="form-control" type="text" name="txtEmpleado" value='Codigo' onclick="if(this.value=='Codigo') this.value=''" onblur="if(this.value=='') this.value='Codigo' " maxlength='4'></input>
									</div>	
								</div>						
							</div>
						</div>
						<div class="row text-center">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<div class="form-group">
										<input type='text' name='txtTitulo' class="form-control" placeholder="Nombre del reporte"></input>
									</div>							
								</div>
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<div class="form-group">
										<?php if (!$mortal) {
											echo "<label>Filtrar por Departamento: </label><select  name='slcDepartamento' class='form-control'>"; Manejador::obtenerDepartamentos(); echo "</select>";
										}
										else
										{
											echo "{$_SESSION['usuario']}";
										}
										?>	
									</div>				
								</div>
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<div class="form-group">
										<button type='submit' name='btnBuscar' value='' class="btn btn-primary btn-block">Buscar resultados</button>
									</div>	
								</div>
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<div class="form-group">
										<button type='submit' name='btnReporte' value='' class="btn btn-info btn-block">Crear Reporte</button>
									</div>									
								</div>
							</div>
						</div>
					</fieldset>
					<br>
					<div  style="overflow: auto; height:80%;">	
						<table style="width:100%;" class="table table-bordered table-hover">
							<th>Nombre</th> <th width="9%">Codigo</th><th>Cargo</th><th>Departamento</th>
							<th width="8%">Fecha</th><th width="7%">Hora de Entrada</th><th width="7%">Hora de Salida</th>
							<?php 
								if(isset($_SESSION['rs']))
								{
									$rs = $_SESSION['rs'];
									if($rs)
									{
										if(sqlsrv_num_rows($rs)<=0)
										{
											echo "<script language='javascript'>
												    alert('No hay resultados que coincidan con su criterio de busqueda.');
												  </script>";
										}
										else
										{
											while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
											{		
												$datos[] = $fila['nombre'].";".$fila['codigo_empleado'].";".$fila['cargo'].";".$fila['departamento'].";".$fila['fecha'].";".$fila['horadeentrada'].";".$fila['horadesalida'];								
												echo "<tr>
															<td>{$fila['nombre']}</td>
															<td>{$fila['codigo_empleado']}</td>
															<td>{$fila['cargo']}</td>
															<td>{$fila['departamento']}</td>
															<td>{$fila['fecha']}</td>
															<td>{$fila['horadeentrada']}</td>
															<td>{$fila['horadesalida']}</td>
													 </tr>";
											}
											$_SESSION['datos'] = $datos; 	
										}
									}
								}					
							?>
						</table>		
					</div>
				</form>
			</div>
		</div>

		<?php include("../footer.html");?>
	</body>
</html>