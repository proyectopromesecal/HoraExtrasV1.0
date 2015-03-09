<?php 
	include('lib/motor.php');
	
	$s = new Seguridad();
	if(!isset($_SESSION))
	{
		session_start();
	}
	
	if($s->verificar())
	{
		if(strcmp($s->verificarTipo(), "SuperAdmin") ==0)
		{
			$_SESSION['rutaActual']="Utilidades > Correo";
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
	global $mensaje;
	$archivos = array();
	$destinatarios = Array();
	if($_POST)
	{
		if($_POST['btnEnviar'])
		{
			if(isset($_FILES['archivos']))
			{
				$total = count($_FILES["archivos"]["name"]);
					for ($i = 0; $i < $total; $i++)
					{ 
						$name = $_FILES["archivos"]["name"][$i];
						$tmp_name = $_FILES["archivos"]["tmp_name"][$i];
						$err = $_FILES["archivos"]["error"][$i];
						 
						if($err == 0)
						{
							$archivos[]=$tmp_name.",".$name;
						}
						//echo "<br>";
						//echo $tmp_name."-".$name;
					}
			}
			if(isset($_POST['txtAdress']))
			{
				$total = count($_POST["txtAdress"]);
				for ($i = 0; $i < $total; $i++)
				{ 
					$destinatarios[]=$_POST["txtAdress"][$i];
				}
			}
			$mensaje = ManejadorCorreo::enviar($_POST['txtUsuario'],$_POST['txtPass'],$_POST['txtFrom'], $_POST['txtFromName'], $destinatarios, $_POST['txtCC'], $_POST['txtBCC'], $_POST['txtSubject'], $_POST['txtBody'],$archivos);
		}
	}
	//var_dump($destinatarios);
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<script type="text/javascript" src="css/multipletxt.js" ></script>
		<title>Archivos</title>	
		<script type="text/javascript">
			var num = 0; 
			evento = function (evt) { 
			   return (!evt) ? event : evt;
			}
			agregaCampo = function () { 
			   div = document.createElement('div');
			   div.className = 'archivo';
			   div.id = 'file' + (++num);
			   campo = document.createElement('input');
			   campo.name = 'archivos[]';
			   campo.type = 'file';
			   campo.style.width = 120;
			   a = document.createElement('a');
			   a.name = div.id;
			   a.href = '#';
			   a.onclick = borraCampo;
			   a.innerHTML = '      Eliminar';
			   div.appendChild(campo);
			   div.appendChild(a);
			   contenedor = document.getElementById('adjuntos');
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
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
	</head>
	<body>
		<?php include("menu.html");?>
		<div id="page">
			<form method="post" action="archivos.php" enctype='multipart/form-data'>
				<center>
					<div style="width:70%;" class="tab_cadre_fixe">
						<div style="width:45%;float:left;">
							<table class="tab_cadre_fixe">
								<tr class="tab_bg_1">
									<td>Usuario:</td>
									<td><input type="text" name="txtUsuario" value="" required></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td>Contraseña:</td>
									<td><input type="password" name="txtPass" value="" required></input></td>
								</tr>
								
								<tr class="tab_bg_1">
									<td>Correo del Remitente:</td>
									<td><input type="text" name="txtFrom" value="" required></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td>Nombre del Remitente:</td>
									<td><input type="text" name="txtFromName" value="" required></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td>Correo Destinatario:</td>
									<td>
										<div id="multptxt">
											<input type="text" name="txtAdress[]" value="@promesecal.gob.do" required></input>
										</div>
									</td>
									<td><a href="#" onClick="agregaCampoTxt()">Agregar Destinatario</a></td>
								</tr>
							</table>						
						</div>
						<div style="width:45%;float:right;">
							<table class="tab_cadre_fixe">
								<tr class="tab_bg_1">
									<td>Copia a:</td>
									<td><input type="text" name="txtCC" value=""></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td>Copia Oculta:</td>
									<td><input type="text" name="txtBCC" value=""></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td>Asunto:</td>
									<td><input type="text" name="txtSubject" value="" required></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td>Mensaje:</td>
									<td><input type="text" name="txtBody" value="" required></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td>Adjuntar:</td>
									<td>
										<div id="adjuntos" style=''>
											<input type="file" name="archivos[]" style="width: 115px;height:22px;"></input>
										</div>
									</td>
									<td><a href="#" onClick="agregaCampo()">Subir otro archivo</a></td>
								</tr>
							</table>						
						</div>
					</div>
					<div><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><?php echo $mensaje;?><br>
						<input class='submit' type="submit" value="Enviar" name="btnEnviar"></input>
					</div>					
				</center>
			</form>
		</div>
		<?php include("footer.html")?>
	</body>
</html>