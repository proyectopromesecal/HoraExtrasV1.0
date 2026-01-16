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
	if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0 or strcmp($s->verificarTipo(), "Asistente") ==0)
	{	
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
	if(isset($_POST['btnGuardar']))
	{
		if(isset($_SESSION['bnfViaticos'] ))
		{
			if(isset($_POST['txtFechaE']))
			{
				ManejadorDietaViatico::guardarDestinos($_SESSION['dvID'], $_POST['txtFechaE'], $_POST['txtFechaS'], $_POST['txtHoraE'], $_POST['txtHoraS'], $_POST['txtLugar']);	
				ManejadorDietaViatico::calcularViatico($_SESSION['dvID']);
				//echo "<script languaje='javascript' type='text/javascript'>window.close();</script>";
			}
		}
		else
		{
			
		}
	}
}
else if (isset($_GET['f']))
{
	$_SESSION['dvID'] = $_GET['f'];
}
?>
<html>
	<head>			
		<title>Destinos</title>
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
			input{

			}
		</style>
		<script type="text/javascript">
			var num = 0; 
			evento = function (evt) { 
			   return (!evt) ? event : evt;
			}
			agregaCampo = function ()
			{
			    div = document.createElement('div');
			    div.className = 'dest row';
			    div.id = 'destinos' + (++num);
				
			    d1 = document.createElement('div');
			    d1.className = 'col-xs-2 col-sm-2 col-md-2 col-lg-2';

			    d2 = document.createElement('div');
			    d2.className = 'col-xs-2 col-sm-2 col-md-2 col-lg-2';

			    d3 = document.createElement('div');
			    d3.className = 'col-xs-2 col-sm-2 col-md-2 col-lg-2';

			    d4 = document.createElement('div');
			    d4.className = 'col-xs-2 col-sm-2 col-md-2 col-lg-2';

			    d5 = document.createElement('div');
			    d5.className = 'col-xs-2 col-sm-2 col-md-2 col-lg-2';

			    d6 = document.createElement('div');
			    d6.className = 'col-xs-2 col-sm-2 col-md-2 col-lg-2';

			    campo1 = document.createElement('input');
			    campo1.name = 'txtFechaE[]';
			    campo1.type = 'date';
			    campo1.style.width = 160;

				campo1.className=' form-control';
				campo1.innerHTML ="required";
				
			    campo3 = document.createElement('input');
			    campo3.name = 'txtHoraE[]';
			    campo3.type = 'time';

				campo3.className=' form-control';		
				
			    campo2 = document.createElement('input');
			    campo2.name = 'txtFechaS[]';
			    campo2.type = 'date';
			    campo2.style.width = 160;

				campo2.className=' form-control';
			   
			    campo4 = document.createElement('input');
			    campo4.name = 'txtHoraS[]';
			    campo4.type = 'time';
				campo4.className=' form-control';

			    campo5 = document.createElement('input');
			    campo5.name = 'txtLugar[]';
			    campo5.type = 'text';
			    campo5.placeholder='Lugar de origen - Destino';
			    campo5.style.width = 160;

				campo5.className=' form-control';
			   
			    a = document.createElement('a');
			    a.name = div.id;
			    a.href = '#';
			    a.onclick = borraCampo;
			    a.innerHTML = '&nbsp &nbsp &nbsp         Eliminar';

			    d1.appendChild(campo1);	
				d2.appendChild(campo3);
			    d3.appendChild(campo2);
			    d4.appendChild(campo4);
				d5.appendChild(campo5);
				d6.appendChild(a);

			    div.appendChild(d1);	
				div.appendChild(d2);
			    div.appendChild(d3);
			    div.appendChild(d4);
			    div.appendChild(d5);
			    div.appendChild(d6);

			    contenedor = document.getElementById('destinos');
			    contenedor.appendChild(div);
			}
			borraCampo = function (evt){
			   evt = evento(evt);
			   campo = remObj(evt);
			   div = document.getElementById(campo.name);
			   div.parentNode.removeChild(div);
			}	
			remObj = function (evt) { 
			   return evt.srcElement ?  evt.srcElement : evt.target;
			}
		</script>
	</head>
	<body>
		<?php //include("menu.html")?>
		<div id='contenido'>
			<div class="container-fluid body-content">
				<div style="width:100%" >
					<form method="post" action="destinosviaticos.php">		
						<div class="form-inline" style="width: 100%; margin: 0 auto;">
							<a href="#" onclick="agregaCampo()" class="btn btn-info btn-block"><img src='pics/add.png'> Agregar Destinos </a> <br><br>
							<table class="table table-striped table-hover" style="width:100%;">
								<th>Fecha de salida</th><th>Hora de salida</th><th>Fecha de entrada</th><th>Hora de entrada</th><th>Lugar Origen - Destino</th><th>Acciones</th>
							</table>
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div id="destinos" class="dest text-center" style="margin-bottom: 25px; ">
									
								</div>									
							</div>

							<input type="submit" name="btnGuardar" value="Guardar y Salir" class="btn btn-primary btn-block"></input>				
						</div>
					</form>				
				</div>
			</div>
		</div>
	</body>
<html>