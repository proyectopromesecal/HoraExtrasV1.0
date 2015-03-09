<?php 
	include('../lib/clases/seguridad.php');
	$s = new Seguridad();
	if(!isset($_SESSION))
	{
		session_start();
	}
	if($s->verificar())
	{
		if(strcmp($s->verificarTipo(), "SuperAdmin") ==0)
		{
			$_SESSION['rutaActual']="Utilidades > Reloj";
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
?>
<html>
	<head>
		<title>Reloj</title>
		<link rel="stylesheet" type="text/css" href="style.css" />
		<link rel="stylesheet" href="../css/styles.css" type="text/css" media="screen">
		<style type="text/css">
			.boton {
				-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
				-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
				box-shadow:inset 0px 1px 0px 0px #ffffff;
				background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #e0e0e0), color-stop(1, #999499) );
				background:-moz-linear-gradient( center top, #e0e0e0 5%, #999499 100% );
				filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#e0e0e0', endColorstr='#999499');
				background-color:#e0e0e0;
				-webkit-border-top-left-radius:30px;
				-moz-border-radius-topleft:30px;
				border-top-left-radius:30px;
				-webkit-border-top-right-radius:30px;
				-moz-border-radius-topright:30px;
				border-top-right-radius:30px;
				-webkit-border-bottom-right-radius:30px;
				-moz-border-radius-bottomright:30px;
				border-bottom-right-radius:30px;
				-webkit-border-bottom-left-radius:30px;
				-moz-border-radius-bottomleft:30px;
				border-bottom-left-radius:30px;
			text-indent:-3.93px;
				border:1px solid #dcdcdc;
				display:inline-block;
				color:#777777;
				font-family:Arial;
				font-size:18px;
				font-weight:bold;
				font-style:normal;
			height:65px;
				line-height:65px;
			width:131px;
				text-decoration:none;
				text-align:center;
				text-shadow:1px 1px 0px #ffffff;
			}.boton:hover {
				background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #999499), color-stop(1, #e0e0e0) );
				background:-moz-linear-gradient( center top, #999499 5%, #e0e0e0 100% );
				filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#999499', endColorstr='#e0e0e0');
				background-color:#999499;
			}.boton:active {
				position:relative;
				top:1px;
			}
			#divBtn
			{
				position:relative;
				width:100px;
				height:100px;
				left:600px;
				top:250px;
			}
		</style>
		<script>
			function cron()
			{
				var hora = new Date();
				if(hora.getHours()==1 && hora.getMinutes()==30)
				{
					clearInterval(time);
					window.open('punchsql/index.php');				
					//alert('Son las 10');
				}
			}
			time=setInterval(cron, 30000); //cada 60 segundos llamará a la función
		</script>
	</head>
<body>
	<?php include("../menu.html");?>
	<br>
	<div id="wrapper">
		<div id="back">
			 <div id="upperHalfBack">
					<img src="spacer.png" /><img id="hoursUpBack" src="Single/Up/AM/0.png"/>
					<img id="minutesUpLeftBack" src="Double/Up/Left/0.png" class="asd" /><img id="minutesUpRightBack" src="Double/Up/Right/0.png"/>
					<img id="secondsUpLeftBack" src="Double/Up/Left/0.png" /><img id="secondsUpRightBack" src="Double/Up/Right/0.png"/>
			 </div>
			 <div id="lowerHalfBack">
					<img src="spacer.png" /><img id="hoursDownBack" src="Single/Down/AM/0.png" />
				   <img id="minutesDownLeftBack" src="Double/Down/Left/0.png" /><img id="minutesDownRightBack" src="Double/Down/Right/0.png" />
				   <img id="secondsDownLeftBack" src="Double/Down/Left/0.png" /><img id="secondsDownRightBack" src="Double/Down/Right/0.png" />
			 </div>
		</div>
		
		
		<div id="front">
			 <div id="upperHalf">
					<img src="spacer.png" /><img id="hoursUp" src="Single/Up/AM/0.png"/>
					<img id="minutesUpLeft" src="Double/Up/Left/0.png" /><img id="minutesUpRight" src="Double/Up/Right/0.png"/>
					<img id="secondsUpLeft" src="Double/Up/Left/0.png" /><img id="secondsUpRight" src="Double/Up/Right/0.png"/>
			 </div>
			 <div id="lowerHalf">
					<img src="spacer.png" /><img id="hoursDown" src="Single/Down/AM/0.png"/>
				   <img id="minutesDownLeft" src="Double/Down/Left/0.png" /><img id="minutesDownRight" src="Double/Down/Right/0.png" />
				   <img id="secondsDownLeft" src="Double/Down/Left/0.png" /><img id="secondsDownRight" src="Double/Down/Right/0.png" />
			 </div>
		</div>
	</div>
	<br><br>
	<div id="divBtn">
		<a onclick="window.open('punchsql/index.php');"class="boton">PULL DATA</a>	
	</div>

</body>
<script src="mootools.js" type="text/javascript"></script>
<script src="animate.js" type="text/javascript"></script>
</html>
