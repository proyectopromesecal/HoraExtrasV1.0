<?php 
class Correo
{
	private $usuario;
	private $pass;
	private $from;
	private $fromName;
	private $adress;
	private $CC;
	private $BCC;
	private $subject;
	private $body;
	private $attachment;
	
	public function getUsuario(){
		return $this->usuario;
	}

	public function setUsuario($usuario){
		$this->usuario = $usuario;
	}

	public function getPass(){
		return $this->pass;
	}

	public function setPass($pass){
		$this->pass = $pass;
	}

	public function getFrom(){
		return $this->from;
	}

	public function setFrom($from){
		$this->from = $from;
	}

	public function getFromName(){
		return $this->fromName;
	}

	public function setFromName($fromName){
		$this->fromName = $fromName;
	}

	public function getAdress(){
		return $this->adress;
	}

	public function setAdress($adress){
		$this->addAdress = $adress;
	}

	public function getCC(){
		return $this->CC;
	}

	public function setCC($CC){
		$this->CC = $CC;
	}

	public function getBCC(){
		return $this->BCC;
	}

	public function setBCC($BCC){
		$this->BCC = $BCC;
	}

	public function getSubject(){
		return $this->subject;
	}

	public function setSubject($subject){
		$this->subject = $subject;
	}

	public function getBody(){
		return $this->body;
	}

	public function setBody($body){
		$this->body = $body;
	}
	
	public function getAttachment()
	{
		foreach($this->attachment as $adjunto)
		{
			return $adjunto;
		}
	}
	
	public function setAttachment($attachment)
	{
		$this->attachment = $attachment;
	}
}
?>