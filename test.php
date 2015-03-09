<?php
	include('lib/motor.php');
	

 //echo (ManejadorSolicitud::obtenerTotalHoras(1637, '2015-01-01', '2015-01-31', 0));
  ManejadorUsuario::obtenerUsuariosSlc();
 echo (ManejadorSolicitud::obtenerTotalHoras(1637, '2015-01-01', '2015-01-31', 0));

	
?>