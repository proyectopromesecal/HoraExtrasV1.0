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
			$link="";
			$noOficio="";
			$comentario="";
			$mensaje="";
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

	if (isset($_GET['s']) && !empty($_GET['s']) && isset($_GET['typ']) && !empty($_GET['typ']))
	{
		$_SESSION['pxid'] = $_GET['s'];
		$_SESSION['pxNoOficio'] = ManejadorSolicitud::obtenerNoOficio($_GET['s']);
		$_SESSION['pxTipo'] = $_GET['typ'];
		
		$query="SELECT solicitudes_autorizadas.noOficio as noOficio , autorizado, comentario
				FROM solicitudHE, solicitudes_autorizadas
				WHERE solicitudhe.id = solicitudes_autorizadas.id_solicitud
				AND tipo = '{$_SESSION['pxTipo']}'
				AND solicitudhe.id = {$_SESSION['pxid']}
				AND solicitudhe.noOficio = '{$_SESSION['pxNoOficio']}'";
		$rs = mysql_query($query);
		
		if($rs)
		{
			$fila = mysql_fetch_assoc($rs);
			if($fila['autorizado'])
			{
				$noOficio = $fila['noOficio'];
				$comentario = $fila['comentario'];
				$mensaje= "Esta solicitud esta autorizada <br>";
				if($_SESSION['pxTipo']=='Transporte')
				{
					$link =  "<a href='formulario.php'>Volver</a>";
				}
				else
				{
					$link = "<a href='index.php'>Realizar calculo</a>";
				}
				
			}
			elseif ($fila['autorizado']=='')
			{
				$mensaje= "Esta solicitud aun no tiene una respuesta del autorizador <br>";
				if($_SESSION['pxTipo']=='Transporte')
				{
					$link =  "<a href='formulario.php'>Volver</a>";
				}
				else
				{
					
					$link =  "<a href='formulario.php'>Volver</a>";
				}
			}
			else
			{
				$mensaje= "Esta solicitud no fue aprobada <br>";			
				if($_SESSION['pxTipo']=='Transporte')
				{
					$link =  "<a href='formulario.php'>Volver</a>";
				}
				else
				{

					$noOficio = $fila['noOficio'];
					$comentario = $fila['comentario'];
					$link =  "<a href='formulario.php'>Volver</a>";
				}
			}
		}
		else
		{
			$mensaje= "Hubo un problema al cargar los datos de la base de datos.";
		}
	}
?>
<html>
	<head>
		<title>Consulta</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<script src="css/jquery-2.0.3.min.js" type="text/javascript"></script>
	</head>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<form method="post" action="paginax.php">
				<fieldset style='width:30%;border:solid;border-width:1px; border-color: #000;-moz-border-radius: 8px;-webkit-border-radius: 8px;-o-border-radius: 8px;'>
					<table class="tab_cadre_fixe" >
						<tr class="tab_bg_1">
							<td>Numero de oficio: </td>
							<td><input type="text" name="txtNoOficio" value="<?php echo $_SESSION['pxNoOficio'];?>" required readonly></input></td>
						</tr>
						<tr class="tab_bg_1">
							<td>Numero de oficio dado:</td>
							<td><input type="text" name="txtNoOficioDado" value="<?php echo $noOficio;?>"required readonly></input></td>
						</tr>
						<tr class="tab_bg_1">
							<td>Comentarios:</td>
							<td><textarea name="txtComentario" cols=30 rows=6 readonly><?php echo $comentario;?></textarea></td>
						</tr>
						<tr class="tab_bg_1">
							<td colspan=2><?php echo "<b>"; echo $mensaje; echo "</b><br>";?></td>
						</tr>
					</table>
				</fieldset>
				<input type="hidden" name="txtForm" value="<?php echo $_SESSION['pxid'];?>" required></input><br>
				<?php echo "<br>".$link;?>
			</form>
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