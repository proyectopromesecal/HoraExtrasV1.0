<?php

include("config.php");

class conexion
{
	private $con;
	
	function __construct()
	{
		$info = array('Database'=>'horasextra', 'UID'=>'sa', 'PWD'=>'PromeseCal1525');
		$this->con = sqlsrv_connect('PROMESEAPP01\RRINSIDEPROMESE', $info);
		$query="use horasextra;";
		$rs = sqlsrv_query($this->con, $query);
		
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




