<?php
class SQLHE
{
	static function obtenerIdEmpleado($codigo, $con)
	{
		$id=0;
		$query="SELECT id 
				FROM empleado
				WHERE codigo_empleado = {$codigo} ";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($con, $query, $params, $options);
		if(sqlsrv_num_rows($rs)>0)
		{
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			$id = $fila['id'];
		}
		else
		{
			$id = 0;
		}
		return $id;
	}
	
	static function insertarHorario($con,$idE, $fecha, $hora_entrada, $hora_salida='00:00:00')
	{
		$queryCheck="SELECT * 
					 FROM horario
					 WHERE id_empleado={$idE}
					 AND fecha='{$fecha}'
					 AND horadeentrada='{$hora_entrada}'
					 AND horadesalida='{$hora_salida}'";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rsCheck=sqlsrv_query($con, $queryCheck, $params, $options);
		if(sqlsrv_num_rows($rsCheck)==0)
		{
			$query="INSERT INTO horario(id_empleado, fecha, horadeentrada, horadesalida)
					VALUES({$idE}, '{$fecha}', '{$hora_entrada}', '{$hora_salida}')";
			$rs=sqlsrv_query($con, $query);
			if($rs)
			{
				return true;
			}
			else
			{
				//echo mysql_error();
				return false;
			}			
		}
		else
		{
			//echo "Ya existe";
			return false;
		}
	}
}