<?php 
class CSP
{
	private $id;
	private $nombre;
	private $idRegion;
	
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
	
	public function getIdRegion(){
		return $this->idRegion;
	}

	public function setIdRegion($idRegion){
		$this->idRegion = $idRegion;
	}
	
	function guardar()
	{
		if ($this->getId() > 0)
		{
			$query="UPDATE centro_salud SET
				nombre = '{$this->getNombre()}',
				id_region = {$this->setIdRegion()}
				WHERE
				id = {$this->getId()}";
			mysql_query($query);
		}
		else
		{
			$query="INSERT INTO centro_salud 
				(nombre, id_region)
				VALUES
				('{$this->getNombre()}', {$this->getIdRegion()})";
			mysql_query($query);
			$this->setID(mysql_insert_id());
		}		
	}
	
	function cargar()
	{
		$query="SELECT * FROM centro_salud WHERE id = {$this->id}";
		$rs = mysql_query($query);
		
		if(mysql_num_rows($rs) > 0)
		{
			$fila = mysql_fetch_assoc($rs);
			$this->setId($fila["id"]);
			$this->setNombre($fila["nombre"]);
			$this->setIdRegion($fila['id_region']);
		}
	}
	
	function eliminar($codigo)
	{
		$query="DELETE FROM centro_salud
				WHERE id = {$codigo}";
				mysql_query($query);
	}		
}
?>