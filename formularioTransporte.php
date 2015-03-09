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
			$t = new Transporte();
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
		if(isset($_POST['btnGuardar']))
		{
			if($t->getID()==0 ||$t->getID()=='')
			{
				$t->setFechaCreacion(date("Y-m-d"));
			}
			$t->setID($_POST['txtID']);
			$t->setArea($_POST['txtArea']);
			$t->setGerencia($_POST['txtGerencia']);
			$t->setFecha($_POST['txtFecha']);
			$t->setDepartamento($_SESSION['dpto']);
			$t->guardar();	
			$msjError ="";
			header('Location:formularioTransporte.php');		
		}
	}
	else if (isset($_GET['edit']))
	{
		$t->setID($_GET['edit']);
		$t->cargar();
	}
	else if (isset($_GET['del']))
	{
		$t->eliminar($_GET['del']);
	}
?>
<html>
	<head>
		<title>Formulario de Solicitud de Tranporte</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
	</head>
	
	<body class="ext-webkit ext-chrome">
		<?php include("menu.html")?>
		<div id='page'>
			<center>
				<div id='form' style="width: 40%">
					<br>
					<form method='post' action='formulariotransporte.php'>
						<fieldset style='width:70%;border:solid;border-width:1px; border-color: #000;-moz-border-radius: 8px;-webkit-border-radius: 8px;-o-border-radius: 8px; '>
							<legend style='font-weight:bold;'>Datos del Formulario</legend>
							<table style="width:100%;" class="tab_cadre_fixe" >
								<tr class="tab_bg_1">
									<td width='100%'><?php echo $msjError;?></td>
									<td width='100%'><input type='hidden' name='txtID' value='<?php echo $t->getID();?>'></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td><label id='lb'>Area Solicitante:</label></td>
									<td><input id='txtForm' type="text" name='txtArea' required value='<?php echo $t->getArea();?>'></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td><label id='lb'>Gerencia:</label></td>
									<td><input id='txtForm' type="text" name='txtGerencia' required value='<?php echo $t->getGerencia();?>'></input></td>
								</tr>						
								<tr class="tab_bg_1">
									<td><label id='lb'>Fecha:</label></td>
									<td><input type='date' name='txtFecha' value='<?php if($t->getFecha()=='' || $t->getFecha()=='0000-00-00')
									{
										echo '';
									}
									else
									{
										echo date("Y-m-d",strtotime($t->getFecha()));
									}
									?>' required></input></td>
								</tr>
								<tr class="tab_bg_1">
									<td align='right' colspan =2><input type='button' name='btnNuevo'  onClick="history.go(0)" value='Nuevo' class='submit'></input><input class='submit 'type='submit' name='btnGuardar' value='Guardar'></input></td>
								</tr>
							</table>
						</fieldset>			
						<br>
					</form>
				</div>				
			</center>
		</div>
		<?php include("footer.html")?>
	</body>
</html>