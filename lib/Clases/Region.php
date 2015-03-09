<?php 
class Region
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
			$query="UPDATE region SET
				nombre = '{$this->getNombre()}'
				WHERE
				id = {$this->getId()}";
			mysql_query($query);
		}
		else
		{
			$query="INSERT INTO region 
				(nombre)
				VALUES
				('{$this->getNombre()}')";
			mysql_query($query);
			$this->setID(mysql_insert_id());
		}		
	}
	
	function cargar()
	{
		$query="SELECT * FROM region WHERE id = {$this->id}";
		$rs = mysql_query($query);
		
		if(mysql_num_rows($rs) > 0)
		{
			$fila = mysql_fetch_assoc($rs);
			$this->setId($fila["id"]);
			$this->setNombre($fila["nombre"]);
		}
	}
	
	function eliminar($codigo)
	{
		$query="DELETE FROM region
				WHERE id = {$codigo}";
				mysql_query($query);
	}		
}
?>