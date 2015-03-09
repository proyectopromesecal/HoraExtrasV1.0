<?php 
class Departamento
{
	private $id;
	private $nombre;
	
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
	
	function guardar()
	{
		if ($this->getId() > 0)
		{
			$query="UPDATE t_departamento SET
				nombre = '{$this->getNombre()}'
				WHERE
				id = {$this->getId()}";
			sqlsrv_query($_SESSION['con'],$query);
		}
		else
		{
			$query="INSERT INTO t_departamento 
				(nombre)
				VALUES
				('{$this->getNombre()}')";
			sqlsrv_query($_SESSION['con'],$query);
			$queryScope = "select SCOPE_IDENTITY() scope";
			$rs = sqlsrv_query($_SESSION['con'],$queryScope);
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			
			$this->setID($fila['scope']);
		}		
	}
	
	function cargar()
	{
		$query="SELECT * FROM t_departamento WHERE id = {$this->id}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs =sqlsrv_query($_SESSION['con'],$query, $params, $options);
		
		if(sqlsrv_num_rows($rs) > 0)
		{
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			$this->setId($fila["id"]);
			$this->setNombre($fila["nombre"]);
		}
	}
	
	function eliminar($codigo)
	{
		$query="DELETE FROM t_departamento
				WHERE id = {$codigo}";
		sqlsrv_query($_SESSION['con'],$query);
	}

}
?>