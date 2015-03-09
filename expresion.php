<?php 
	include("lib/motor.php");
?>
<html>
	<head>
		<title></title>
		<script src="css/jquery-2.0.3.min.js" type="text/javascript"></script>
	</head>
	<body>
		<p id="texto">Hello</p>
		<a href="#" id="enlace">Click to hide me too</a>
		<p>Here is another paragraph</p>
		 
		<script>
			
			$( "enlace" ).click(function( event ) {
			  $( "texto" ).hide();
			  event.preventDefault();
			  $( this ).hide();
			});
		</script>
	</body>
</html>