<?php 

class DiaFeriado
{
	private $id;
	private $fecha;
	private $motivo;
	
	public function setID($id)
	{
		$this->id = $id;
	}

	public function setFecha($fecha)
	{
		$this->fecha = $fecha;
	}
	
	public function setMotivo($motivo)
	{
		$this->motivo = $motivo;
	}
	
	public function getID()
	{
		return $this->id;
	}
	
	public function getFecha()
	{
		return $this->fecha;
	}
	
	public function getMotivo()
	{
		return $this->motivo;
	}
	
	function guardar()
	{
		if ($this->getID() > 0)
		{
			$query = "UPDATE dias_feriados SET
				fecha = '{$this->getFecha()}',
				motivo = '{$this->getMotivo()}'
				WHERE
				id = {$this->getID()}";
				
			sqlsrv_query($_SESSION['con'],$query);
		}
		else
		{
			$query = "INSERT INTO dias_feriados 
				(fecha, motivo)
				VALUES
				('{$this->getFecha()}', '{$this->getMotivo()}')";
				
			sqlsrv_query($_SESSION['con'],$query);
			
			$queryScope = "select SCOPE_IDENTITY() scope";
			$rs = sqlsrv_query($_SESSION['con'],$queryScope);
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			
			$this->setID($fila['scope']);
		}		
	}
	
	function cargar()
	{
		$query="SELECT * FROM dias_feriados WHERE id = {$this->getID()}";
		
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		
		if(sqlsrv_num_rows($rs) > 0)
		{
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			$this->setID($fila["id"]);
			$this->setFecha($fila["fecha"]);
			$this->setMotivo($fila["motivo"]);
		}
	}
	
	function eliminar($codigo)
	{
		$query = "DELETE FROM dias_feriados
				WHERE id = '{$codigo}'";
				sqlsrv_query($_SESSION['con'],$query);
	}		
	
	
}
?>