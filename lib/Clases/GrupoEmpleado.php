<?php
class GrupoEmpleado{

	private $id;
	private $id_empleado;
	private $id_secretaria;
	private $nombre;

	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}

	public function getId_empleado(){
		return $this->id_empleado;
	}

	public function setId_empleado($id_empleado){
		$this->id_empleado = $id_empleado;
	}

	public function getId_secretaria(){
		return $this->id_secretaria;
	}

	public function setId_secretaria($id_secretaria){
		$this->id_secretaria = $id_secretaria;
	}

	public function getNombre(){
		return $this->nombre;
	}

	public function setNombre($nombre){
		$this->nombre = $nombre;
	}

	static function agregarEmpleados($idS,$arrayE)
	{
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		foreach($arrayE as $valor)
		{
			$queryCheck="SELECT * from grupo_empleados where id_secretaria={$idS} AND id_empleado={$valor}";
			$rsCheck=sqlsrv_query($_SESSION['con'],$queryCheck, $params, $options);
			if (sqlsrv_num_rows($rsCheck)==0) 
			{
				$query = "INSERT INTO grupo_empleados
				(id_empleado, id_secretaria)
				VALUES
				({$valor}, {$idS})";
				$rs = sqlsrv_query($_SESSION['con'],$query);
			}
		}
	}
	
	static function eliminarEmpleados($idS, $arrayE)
	{
		foreach($arrayE as $valor)
		{
			$query = "DELETE FROM grupo_empleados
					  WHERE id_secretaria = {$idS} 
					  AND id_empleado ={$valor}";
			$rs = sqlsrv_query($_SESSION['con'],$query);
		}
	}

	static function obtenerEmpleados($idS)
	{
		$query="SELECT a.id, a.nombre, a.cedula, c.nombre as cargo from empleado a
				inner join t_cargo c on c.id = a.cargo
				inner join grupo_empleados g on g.id_empleado = a.id
				WHERE g.id_secretaria={$idS}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		return $rs;
	}
}