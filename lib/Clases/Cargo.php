<?php 
class Cargo
{
	private $id;
	private $nombre;
	private $id_dpto;
	private $sueldo_min;
	private $sueldo_max;
	
	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}

	public function getNombre(){
		return $this->nombre;
	}

	public function setNombre($nombre){
		$this->nombre = $nombre;
	}

	public function getSueldo_min(){
		return $this->sueldo_min;
	}

	public function setSueldo_min($sueldo_min){
		$this->sueldo_min = $sueldo_min;
	}

	public function getSueldo_max(){
		return $this->sueldo_max;
	}

	public function setSueldo_max($sueldo_max){
		$this->sueldo_max = $sueldo_max;
	}
	
	function guardar()
	{
		if ($this->getId() > 0)
		{
			$query="UPDATE t_cargo SET
				nombre = '{$this->getNombre()}',
				sueldo_min = {$this->getSueldo_min()},
				sueldo_max = {$this->getSueldo_max()}
				WHERE
				id = {$this->getId()}";
			sqlsrv_query($_SESSION['con'],$query);
		}
		else
		{
			$query="INSERT INTO t_cargo 
				(nombre, sueldo_min, sueldo_max)
				VALUES
				('{$this->getNombre()}', {$this->getSueldo_min()}, {$this->getSueldo_max()})";
			sqlsrv_query($_SESSION['con'],$query);
			
			$queryScope = "select SCOPE_IDENTITY() scope";
			$rs = sqlsrv_query($_SESSION['con'],$queryScope);
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			
			$this->setID($fila['scope']);
		}		
	}
	
	function cargar()
	{
		$query="SELECT * FROM t_cargo WHERE id = {$this->id}";
		
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		
		if(sqlsrv_num_rows($rs) > 0)
		{
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			$this->setId($fila["id"]);
			$this->setNombre($fila["nombre"]);
			$this->setSueldo_min($fila["sueldo_min"]);
			$this->setSueldo_max($fila["sueldo_max"]);
		}
	}
	
	function eliminar($codigo)
	{
		$query="DELETE FROM t_cargo
				WHERE id = {$codigo}";
				sqlsrv_query($_SESSION['con'],$query);
	}		
}
?>