<?php 
class DietaViatico
{
	private $id;
	private $no_oficio;
	private $departamento;
	private $fecha_entrada;
	private $fecha_salida;
	private $objetivo;
	private $lugar;
	private $hora_entrada;
	private $hora_salida;
	private $descripcion;
	private $fecha_creacion;
	private $hora;
	private $usr;
	
	public function getID() { return $this->id; } 
	public function getNoOficio() { return $this->no_oficio; } 
	public function getDepartamento() { return $this->departamento; } 
	public function getObjetivo() { return $this->objetivo; } 
	public function getDescripcion() { return $this->descripcion; } 
	public function getFecha_creacion() { return $this->fecha_creacion; } 
	public function getHora() {return $this->hora;}
	public function setID($x) { $this->id = $x; } 
	public function setNoOficio($x) { $this->no_oficio = $x; } 
	public function setDepartamento($x) { $this->departamento = $x; } 
	public function setObjetivo($x) { $this->objetivo = $x; } 
	public function setDescripcion($x) { $this->descripcion = $x; }
	public function setFecha_creacion($x) { $this->fecha_creacion = $x; }
	public function setHora($x) { $this->hora = $x;}
	public function getUsr() {return $this->usr;}
	public function setUsr($x) { $this->usr = $x; } 

	function guardar()
	{
		if ($this->getID() > 0)
		{
			$query="UPDATE dietaviatico SET
				departamento = '{$this->getDepartamento()}',
				objetivo = '{$this->getObjetivo()}',
				descripcion ='{$this->getDescripcion()}'
				WHERE
				id = '{$this->getID()}'";
			$rs = sqlsrv_query($_SESSION['con'],$query);
		}
		else
		{
			$query="INSERT INTO dietaviatico 
					(no_oficio, departamento, objetivo, fecha_creacion,descripcion, hora, usr)
					VALUES
					('{$this->getNoOficio()}','{$this->getDepartamento()}', '{$this->getObjetivo()}', '{$this->getFecha_creacion()}', '{$this->getDescripcion()}', '{$this->getHora()}', '{$this->getUsr()}')";
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
		$query="SELECT * FROM dietaviatico WHERE  id = '{$this->id}'";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		
		if(sqlsrv_num_rows($rs) > 0)
		{
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			$this->id = $fila["id"];
			$this->departamento = $fila["departamento"];
			$this->objetivo = $fila["objetivo"];
			$this->descripcion = $fila["descripcion"];
			$this->no_oficio = $fila["no_oficio"];
		}
	}
	
	function eliminar($codigo)
	{
		$query="DELETE FROM dietaviatico
				WHERE id = '{$codigo}'";
		sqlsrv_query($_SESSION['con'],$query);
	}	
}
?>