<?php 
class Transporte
{
	private $id;
	private $area;
	private $no_oficio;
	private $fecha;
	private $fecha_creacion;
	private $departamento;

	public function getID() { return $this->id; } 
	public function getArea() { return $this->area; } 
	public function getNoOficio() { return $this->no_oficio; } 
	public function getFecha() { return $this->fecha; } 
	public function getFechaCreacion() { return $this->fecha_creacion; } 
	public function getDepartamento() { return $this->departamento; } 
	public function setID($x) { $this->id = $x; } 
	public function setDepartamento($x) { $this->departamento = $x; } 
	public function setArea($x) { $this->area = $x; } 
	public function setNoOficio($x) { $this->no_oficio = $x; } 
	public function setFecha($x) { $this->fecha = $x; } 
	public function setFechaCreacion($x) { $this->fecha_creacion = $x; } 
	
	function guardar()
	{
		$id=0;
		if ($this->getID() > 0)
		{
			$query="UPDATE formulario_transporte SET
				area = '{$this->getArea()}',
				no_oficio = '{$this->getNoOficio()}',
				fecha = '{$this->getFecha()}',
				departamento ='{$this->getDepartamento()}'
				WHERE
				id = '{$this->getID()}'";
			sqlsrv_query($_SESSION['con'],$query);
		}
		else
		{
			$query="INSERT INTO formulario_transporte 
					(area, no_oficio, fecha, fecha_creacion, departamento)
					VALUES
					('{$this->getArea()}', '{$this->getNoOficio()}', '{$this->getFecha()}', '{$this->getFechaCreacion()}','{$this->getDepartamento()}')";
			sqlsrv_query($_SESSION['con'],$query);
		    $res = sqlsrv_query($_SESSION['con'],"SELECT @@identity AS id"); 
		    if ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) { 
		        $id = $row["id"]; 
		    }
			$this->setID($id);
		}		
	}
	
	function cargar()
	{
		$query="SELECT * FROM formulario_transporte WHERE id = '{$this->getID()}'";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		
		if(sqlsrv_num_rows($rs) > 0)
		{
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			$this->setID($fila["id"]);
			$this->setArea($fila['area']);
			$this->setNoOficio($fila["no_oficio"]);
			$this->setFecha($fila["fecha"]);
			$this->setFechaCreacion($fila["fecha_creacion"]);
			$this->setDepartamento($fila["departamento"]);
		}
	}
	
	function eliminar($codigo)
	{
		$query="DELETE FROM formulario_transporte
				WHERE id = '{$codigo}'";
				sqlsrv_query($_SESSION['con'],$query);
	}
}

?>