<?php
if(!function_exists("setPHPExcel_Title")) {
	function setPHPExcel_Title() {
		$style 	= array(
				    	'font'  => array(
				        'bold'  => true,
				        'color' => array('rgb' => '000000'),
				        'size'  => 10,
				        'name'  => 'Calibri'
					));

		return $style;
	}
}

if(!function_exists("setPHPExcel_Header")) {
	function setPHPExcel_Header() {
		$style 	= array(
				    	'font'  => array(
					        'bold'  => true,
					        'color' => array('rgb' => '000000'),
					        'size'  => 10,
					        'name'  => 'Calibri'
						),
						'alignment' => array(
					        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					    ),
				        'borders' => array(
						    'allborders' => array(
						      	'style' => PHPExcel_Style_Border::BORDER_THIN,
						      	'color' => array('rgb' => '000000'),
						    )
						)
					);

		return $style;
	}
}

if(!function_exists("setPHPExcel_Content_Left")) {
	function setPHPExcel_Content_Left() {
		$style 	= array(
				    	'font'  => array(
					        'bold'  => false,
					        'color' => array('rgb' => '000000'),
					        'size'  => 10,
					        'name'  => 'Calibri'
						),
						'alignment' => array(
					        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					    ),
				        'borders' => array(
						    'allborders' => array(
						      	'style' => PHPExcel_Style_Border::BORDER_THIN,
						      	'color' => array('rgb' => '000000'),
						    )
						)
					);

		return $style;
	}
}

if(!function_exists("setPHPExcel_Content_Center")) {
	function setPHPExcel_Content_Center() {
		$style 	= array(
				    	'font'  => array(
					        'bold'  => false,
					        'color' => array('rgb' => '000000'),
					        'size'  => 10,
					        'name'  => 'Calibri'
						),
						'alignment' => array(
					        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					    ),
				        'borders' => array(
						    'allborders' => array(
						      	'style' => PHPExcel_Style_Border::BORDER_THIN,
						      	'color' => array('rgb' => '000000'),
						    )
						)
					);

		return $style;
	}
}

if(!function_exists("setPHPExcel_Content_Right")) {
	function setPHPExcel_Content_Right() {
		$style 	= array(
				    	'font'  => array(
					        'bold'  => false,
					        'color' => array('rgb' => '000000'),
					        'size'  => 10,
					        'name'  => 'Calibri'
						),
						'alignment' => array(
					        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					    ),
				        'borders' => array(
						    'allborders' => array(
						      	'style' => PHPExcel_Style_Border::BORDER_THIN,
						      	'color' => array('rgb' => '000000'),
						    )
						)
					);

		return $style;
	}
}

if(!function_exists("setPHPExcel_Content_Total")) {
	function setPHPExcel_Content_Total() {
		$style 	= array(
				    	'font'  => array(
					        'bold'  => true,
					        'color' => array('rgb' => '000000'),
					        'size'  => 10,
					        'name'  => 'Calibri'
						),
						'alignment' => array(
					        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					    )
					);

		return $style;
	}
}