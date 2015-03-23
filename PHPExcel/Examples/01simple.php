<?php
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2014 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.8.0, 2014-03-02
 */

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');
set_time_limit(0);

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

/** Include PHPExcel */
require_once dirname(__FILE__) . '/../Classes/PHPExcel.php';
include('../../lib/motor.php');


// Create new PHPExcel object
echo date('H:i:s') , " Create new PHPExcel object" , EOL;
$objPHPExcel = new PHPExcel();

// Set document properties
echo date('H:i:s') , " Set document properties" , EOL;
$objPHPExcel->getProperties()->setCreator("DTIC")
							 ->setLastModifiedBy("DTIC")
							 ->setTitle("PHPExcel Test Document")
							 ->setSubject("PHPExcel Test Document")
							 ->setDescription("Test document for PHPExcel, generated using PHP classes.")
							 ->setKeywords("office PHPExcel php")
							 ->setCategory("Test result file");

/* MANEJO DE DATOS */

global $tabla;
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

if(isset($_GET['fi']) && isset($_GET['ff']))
{
	$query="select a.nombre nombre, a.cedula, c.nombre cargo, d.nombre  departamento, sum(pago) as pago, a.sueldo as sueldo from empleado a 
            inner join horario b on a.id = b.id_empleado and b.fecha BETWEEN '{$_GET['fi']}' AND '{$_GET['ff']}' 
            inner join t_cargo c on a.cargo = c.id
            inner join t_departamento d on a.departamento = d.id
            inner join solicitudes e on a.id = e.id_empleado
            inner join solicitudhe f on e.id_solicitud = f.id and b.fecha = f.fecha
            inner join solicitudes_autorizadas g on f.id = g.id_solicitud and g.tipo = 'HoraExtra' and g.autorizado = 1
            inner join historial_empleado h on a.id = h.id_empleado and b.id = h.id_horario 
            where a.nivel <> 2";

			if (!strcmp($_SESSION['tipo'], "Viewer")==0) {
				$query.=" and f.usr in (
                            SELECT a.usuario
                            FROM [horasextra].[dbo].[usuario] a
                            inner join empleado b on a.empleado =  b.id
                            inner join t_departamento c on b.departamento = c.id
                            where b.departamento in (

                                            select b.id from  usuario c
                                            inner join empleado a on c.empleado = a.id
                                            inner join t_departamento b on a.departamento = b.id or a.departamento = b.subDepId 
                                            where c.id = {$_SESSION['id']}                     
                            ))";
			}
			$query.=" GROUP BY a.nombre,a.cedula,c.nombre,d.nombre, a.sueldo";

	$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
	if($rs)
	{
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$tabla[]=$fila['nombre'].";".$fila['cedula'].";".$fila['cargo'].";".$fila['departamento'].";".$fila['pago'].";".$fila['sueldo'];
		}
	}
}
else
{
	echo "<script>alert('error, espeficique la fecha');</script>";
}

$i=1;
$f=0;
$letras = range('A', 'Z');

foreach($tabla as $row)
{
	$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
	if($row != end($tabla))
	{
		
		/*Gathering adicional data*/
		$id = Manejador::obtenerIdE($columna[1]);
		$pagonormal;
		$horanormal;
		$pagoferiado;
		$horaferiada;
		$tempN = explode('/*', ManejadorSolicitud::obtenerTotalHoras($id,$_GET['fi'], $_GET['ff'], 0 ));
		$tempF = explode('/*', ManejadorSolicitud::obtenerTotalHoras($id,$_GET['fi'], $_GET['ff'], 1 ));
		$horanormal = $tempN[0];
		$pagonormal = $tempN[1];
		$horaferiada = $tempF[0];
		$pagoferiado = $tempF[1];

		/*Creacion del objeto excel*/
		$objPHPExcel->setActiveSheetIndex(0)
	    	->setCellValue($letras[$f++].$i, $columna[0])
	    	->setCellValue($letras[$f++].$i, $columna[1])
	    	->setCellValue($letras[$f++].$i, $columna[2])
	   		->setCellValue($letras[$f++].$i, $columna[3])
	   		->setCellValue($letras[$f++].$i, $columna[4])
	    	->setCellValue($letras[$f++].$i, $columna[5])
	    	->setCellValue($letras[$f++].$i, $horaferiada)
	   		->setCellValue($letras[$f++].$i, $horanormal)
	   		->setCellValue($letras[$f++].$i, $pagoferiado)
	    	->setCellValue($letras[$f++].$i, $pagonormal);  
	    $i++;  
	    $f=0;	
	}
}


// Save Excel 2007 file
echo date('H:i:s') , " Write to Excel2007 format" , EOL;
$callStartTime = microtime(true);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;

echo date('H:i:s') , " File written to " , str_replace('.php', '.xlsx', pathinfo(__FILE__, PATHINFO_BASENAME)) , EOL;
echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;

// Echo memory peak usage
echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , EOL;

// Echo done
echo date('H:i:s') , " Done writing files" , EOL;
echo 'Files have been created in ' , getcwd() , EOL;
