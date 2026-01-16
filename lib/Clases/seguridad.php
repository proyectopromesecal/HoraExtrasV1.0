<?php 
	class Seguridad	
	{
		private $ldaphost = 'promese.promesecal.gob.do';
		private $ldapport = 389;
		
		function conectarLdap($usuario, $pass)
		{
			if(empty($usuario) or empty($pass))
			{
				return false;
			}
			else
			{
				
				//$ds = ldap_connect($this->ldaphost, $this->ldapport) or die("No se pudo conectar a $ldaphost");
				$ds = ldap_connect("promese.promesecal.gob.do");
				//ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 2);
				ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
				//ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
				if($ds)
				{
					$ldapbind= ldap_bind($ds, $usuario, $pass);
					if($ldapbind)
					{
						return true;
					}
					else
					{
						//return true;
						return false;
					}
					ldap_unbind($ldapbind);

				}		
			}

		}
		
		function verificar()
		{
			if(!isset($_SESSION))
			{
				session_start();
				//return true;
			}
			if(isset($_SESSION['usuario']))
			{
				return true;
			}
		}
		
		function verificarTipo()
		{
			if($_SESSION['tipo'])
			{
				return $_SESSION['tipo'];
			}
		}
	}
?> 