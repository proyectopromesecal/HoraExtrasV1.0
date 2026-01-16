<?php
	// Intentar cargar variables desde .env (sube un nivel desde /lib)
	$envPath = __DIR__ . '/../.env';
	if (file_exists(__DIR__ . '/env_loader.php')) {
		include_once(__DIR__ . '/env_loader.php');
		if (function_exists('loadEnv')) {
			loadEnv($envPath);
		}
	}

	// Definir constantes usando variables de entorno o valores por defecto seguros (vacíos)
	if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '');
	if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: '');
	if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
	if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: '');
