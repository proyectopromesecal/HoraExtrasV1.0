<?php

include("config.php");

class conexion
{
	private $con;
	
	function __construct()
	{
		// Se asume que config.php ya cargó las variables de entorno
		$info = array('Database'=>DB_NAME, 'UID'=>DB_USER, 'PWD'=>DB_PASS);
		$this->con = sqlsrv_connect(DB_HOST, $info);
		
		// Verificamos si se conectó antes de tratar de usar la DB
		if ($this->con) {
			$query="use " . DB_NAME . ";";
			$rs = sqlsrv_query($this->con, $query);
		}
		
		if (!$this->con)
		{
			die( print_r( sqlsrv_errors(), true));
		}
		/*
		if (mysql_error())
		{
			echo "
				<script language='javascript'>
					window.location='instalador.php';
				</script>";
		}*/
	}
	
	function __destruct()
	{
		sqlsrv_close($this->con);
	}
		
	function getConexion()
	{
		return $this->con;
	}

}




