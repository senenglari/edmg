<?php
if(!function_exists("getUrl")) {
	function getUrl() {
		$url  	= "/" . \Request::segment(1);

		return $url;
	}
}

if(!function_exists("setString")) {
	function setString($var) {
		$var 	= strtoupper(str_replace('"', "", str_replace("'", "", $var)));

		return $var;
	}
}

if(!function_exists("setDate")) {
	function setDate($var) {
		if(($var == "") || ($var == "0000-00-00") || ($var == "____-__-__")) {
			$var 	= null;
		} else {
			$var	= $var;
		}

		return $var;
	}
}

if(!function_exists("isNumber")) {
	function isNumber($var) {
		if(preg_match('/[^0-9]+$/', $var)) {
			return false;
		} else {
			return true;
		}
	}
}

if(!function_exists("setYMD")) {
	function setYMD($var, $chr) {
		list($day, $month, $year) = explode($chr, $var);
		# ---------------
		$var = $year . "-" . $month . "-" . $day;
		# ---------------
		return $var;
	}
}


if (!function_exists("addWorkingDaysHardcode")) {
    function addWorkingDaysHardcode($startDate, $days)
    {
        // Hardcode libur nasional/cuti bersama. Format: 'Y-m-d'
        // Kamu bisa isi sesuai kebutuhan perusahaan (misal hanya nasional, tanpa cuti bersama).
        $holidays = [
            '2026-01-01',
            '2026-02-17',
            '2026-03-19',
            // dst...
        ];

        $date  = \Carbon\Carbon::parse($startDate);
        $added = 0;

        // Start date tidak dihitung; mulai dari hari berikutnya
        while ($added < (int)$days) {
            $date->addDay();

            // Skip Sabtu/Minggu
            if ($date->isWeekend()) {
                continue;
            }

            // Skip hari libur
            if (in_array($date->format('Y-m-d'), $holidays, true)) {
                continue;
            }

            $added++;
        }

        return $date->format('Y-m-d');
    }
}





if(!function_exists("setDMY")) {
	function setDMY($var, $chr) {
		list($year, $month, $day) = explode($chr, $var);
		# ---------------
		$var = $day . "-" . $month . "-" . $year;
		# ---------------
		return $var;
	}
}

if(!function_exists("displayDMY")) {
	function displayDMY($var) {
		if(!empty($var)) {
			list($year, $month, $day) = explode("-", $var);
			# ---------------
			$var = $day . "/" . $month . "/" . $year;
			# ---------------
			return $var;
		} else {
			return "";
		}
	}
}

if(!function_exists("setNoComma")) {
	function setNoComma($var) {
		if($var == "") {
			$var = 0;
		} else {
			$var = str_replace(",", "", $var);
		}
		# ---------------
		return $var;
	}
}

if(!function_exists("setComma")) {
	function setComma($var) {
		$var = number_format($var, 0);
		# ---------------
		return $var;
	}
}

if(!function_exists("getMonthName")) {
	function getMonthName($var, $lang=null) {		
		switch($var) {
			case 1:
				$en = "January";
				$id = "Januari";
				break;
			case 2:
				$en = "February";
				$id = "Februari";
				break;
			case 3:
				$en = "March";
				$id = "Maret";
				break;
			case 4:
				$en = "April";
				$id = "April";
				break;
			case 5:
				$en = "May";
				$id = "Mei";
				break;
			case 6:
				$en = "June";
				$id = "Juni";
				break;
			case 7:
				$en = "July";
				$id = "Juli";
				break;
			case 8:
				$en = "August";
				$id = "Agustus";
				break;
			case 9:
				$en = "September";
				$id = "September";
				break;
			case 10:
				$en = "October";
				$id = "Oktober";
				break;
			case 11:
				$en = "November";
				$id = "Nopember";
				break;
			case 12:
				$en = "December";
				$id = "Desember";
				break;
		}
		# ---------------
		if($lang == "en") {
			return $en;
		} else {
			return $id;
		}
	}
}

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

if(!function_exists("getPeriodeNameByDate")) {
	function getPeriodeNameByDate($var) {
		list($tahun, $bulan, $tanggal) = explode("-", $var);
		# ---------------
		$periode 	= getMonthName($bulan) . " " . $tahun;
		# ---------------
		return strtoupper($periode);
	}
}

if(!function_exists("setTerbilang")) {
	function setTerbilang($nominal) {
		$angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
				if ($nominal < 12)
				    return " " . $angka[$nominal];
				elseif ($nominal < 20)
				    return setTerbilang($nominal - 10) . " belas";
				elseif ($nominal < 100)
				    return setTerbilang($nominal / 10) . " puluh" . setTerbilang($nominal % 10);
				elseif ($nominal < 200)
				    return "seratus" . setTerbilang($nominal - 100);
				elseif ($nominal < 1000)
				    return setTerbilang($nominal / 100) . " ratus" . setTerbilang($nominal % 100);
				elseif ($nominal < 2000)
				    return "seribu" . setTerbilang($nominal - 1000);
				elseif ($nominal < 1000000)
				    return setTerbilang($nominal / 1000) . " ribu" . setTerbilang($nominal % 1000);
				elseif ($nominal < 1000000000)
				    return setTerbilang($nominal / 1000000) . " juta" . setTerbilang($nominal % 1000000);
				elseif ($nominal < 1000000000000)
				    return setTerbilang($nominal / 1000000000) . " milyar" . setTerbilang($nominal % 1000000000);

		return $angka;
	}
}

if(!function_exists("getMonthRomawi")) {
	function getMonthRomawi($var) {		
		switch($var) {
			case 1:
				$bulan = "I";
				break;
			case 2:
				$bulan = "II";
				break;
			case 3:
				$bulan = "III";
				break;
			case 4:
				$bulan = "IV";
				break;
			case 5:
				$bulan = "V";
				break;
			case 6:
				$bulan = "VI";
				break;
			case 7:
				$bulan = "VII";
				break;
			case 8:
				$bulan = "VIII";
				break;
			case 9:
				$bulan = "IX";
				break;
			case 10:
				$bulan = "X";
				break;
			case 11:
				$bulan = "XI";
				break;
			case 12:
				$bulan = "XII";
				break;
		}
		# ---------------
		return $bulan;
	}
}

if(!function_exists("getMonthNameByDate")) {
	function getMonthNameByDate($var, $format) {
		if($format == "YMD") {
			list($y, $m, $d) = explode("-", $var);
		} else {
			list($d, $m, $y) = explode("/", $var);
		}

		switch($m) {
			case 1:
				$id = "Januari";
				break;
			case 2:
				$id = "Februari";
				break;
			case 3:
				$id = "Maret";
				break;
			case 4:
				$id = "April";
				break;
			case 5:
				$id = "Mei";
				break;
			case 6:
				$id = "Juni";
				break;
			case 7:
				$id = "Juli";
				break;
			case 8:
				$id = "Agustus";
				break;
			case 9:
				$id = "September";
				break;
			case 10:
				$id = "Oktober";
				break;
			case 11:
				$id = "Nopember";
				break;
			case 12:
				$id = "Desember";
				break;
		}
		# ---------------
		return $id;
	}
}

if(!function_exists("getUsia")) {
	function getUsia($date) {
		$biday 	= new DateTime($date);
		$today 	= new DateTime();
	
		$diff 	= $today->diff($biday);
		# ---------------
		return $diff->y;
	}
}

if(!function_exists("removeStrip")) {
	function removeStrip($var) {
		$var 	= strtoupper(str_replace("-", " ", $var));

		return $var;
	}
}

if(!function_exists("ucwordString")) {
    function ucwordString($var) {
        $var 	= ucwords(strtolower($var));

        return $var;
    }
}

if(!function_exists("ucwordString")) {
    function ucwordString($var) {
        $var 	= ucwords(strtolower($var));

        return $var;
    }
}

if (!function_exists("TextView")) {
    function TextView($var, $len, $align, $delimiter)
    {
        $len_data   = strlen($var);
        $result     = $var;

        for ($i = 1; $i <= ($len - $len_data); $i++) {
            $result     .= " ";
        }

        return $result . $delimiter;
    }
}

if (!function_exists("createTextFile")) {
    function createTextFile($text, $File)
    {
        $file       = fopen(env("APPS_DIRECTORY_MIRRORING_BUKOPIN") . $File, "w") or die("Unable to open file!");
        # ------------------
        fwrite($file, $text);
        fclose($file);
    }
}

if (!function_exists("addWorkingDays")) {
	function addWorkingDays($start, $add){
	  	$start = strtotime($start);
	  	$d = min(date('N',$start),5);
	  	$start -= date('N',$start) * 24 * 60 * 60;
	  	$w = (floor( $add / 5 ) * 2);
	  	$r = $add + $w + $d;

	  	return date('Y-m-d', strtotime( date("Y-m-d", $start) . " +$r day") );
	}
}