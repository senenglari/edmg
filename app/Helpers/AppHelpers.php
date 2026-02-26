<?php
if (!function_exists("getSelectStatusUser")) {
	function getSelectStatusUser()
	{
		$qSelect		= array(array("id" => "1", "name" => "Active"), array("id" => "0", "name" => "Inactive"));

		return $qSelect;
	}
}

if (!function_exists("getLabelFlag")) {
	function getLabelFlag($var)
	{
		switch (strtoupper($var)) {
			case "WAITING FOR RETURN":
				$flag 	= "<span class=\"label \" style='background-color: #11CE44'>Waiting for return</span>";
				break;

			case "SENT":
				$flag 	= "<span class=\"label label-primary\">Sent</span>";
				break;

			case "ASSIGNED":
				$flag 	= "<span class=\"label label-primary\">ASSIGNED</span>";
				break;

			case "UNISSUED":
				$flag 	= "<span class=\"label label-primary\">Unissued</span>";
				break;

			case "APPROVED":
				$flag 	= "<span class=\"label label-success\">APPROVED</span>";
				break;

			case "SUDAH DI SET":
				$flag 	= "<span class=\"label label-success\">SUDAH DI SET</span>";
				break;

			case "BELUM DI SET":
				$flag 	= "<span class=\"label label-warning\">BELUM DI SET</span>";
				break;

			case "LOCK":
				$flag 	= "<span class=\"label label-warning\">LOCK</span>";
				break;

			case "OPEN":
				$flag 	= "<span class=\"label label-success\">OPEN</span>";
				break;

			case "BELUM":
				$flag 	= "<span class=\"label label-warning\">BELUM</span>";
				break;

			case "SUDAH":
				$flag 	= "<span class=\"label label-primary\">SUDAH</span>";
				break;

			case "SUDAH POSTING":
				$flag 	= "<span class=\"label label-primary\">SUDAH POSTING</span>";
				break;

			case "SUDAH DIKIRIM":
				$flag 	= "<span class=\"label label-primary\">SUDAH DIKIRIM</span>";
				break;

			case "SUDAH TERPAKAI":
				$flag 	= "<span class=\"label label-warning\">SUDAH TERPAKAI</span>";
				break;

			case "BELUM DIKIRIM":
				$flag 	= "<span class=\"label label-warning\">BELUM DIKIRIM</span>";
				break;

			case "BELUM DITERIMA (P)":
				$flag 	= "<span class=\"label label-warning\">BELUM DITERIMA (P)</span>";
				break;

			case "BELUM DITERIMA (C)":
				$flag 	= "<span class=\"label label-danger\">BELUM DITERIMA (C)</span>";
				break;

			case "SUDAH DITERIMA (C)":
				$flag 	= "<span class=\"label label-primary\">SUDAH DITERIMA (C)</span>";
				break;

			case "SUDAH DITERIMA (P)":
				$flag 	= "<span class=\"label label-primary\">SUDAH DITERIMA (P)</span>";
				break;

			case "TITIPAN":
				$flag 	= "<span class=\"label label-primary\">TITIPAN</span>";
				break;

			case "SAMA":
				$flag 	= "<span class=\"label label-primary\">SAMA</span>";
				break;

			case "SELISIH":
				$flag 	= "<span class=\"label label-warning\">SELISIH</span>";
				break;

			case "DONE":
				$flag 	= "<span class=\"label \" style='background-color: #11CE44'>DONE</span>";
				break;

			case "NONE":
				$flag 	= "<span class=\"label label-warning\">NONE</span>";
				break;

			case "NEW":
				$flag 	= "<span class=\"label label-primary\">NEW</span>";
				break;

			case "RENEW":
				$flag 	= "<span class=\"label label-primary\">RENEW</span>";
				break;

			case "ACTIVE":
				$flag 	= "<span class=\"label label-primary\">ACTIVE</span>";
				break;

			case "INACTIVE":
				$flag 	= "<span class=\"label label-warning\">INACTIVE</span>";
				break;

			case "ANGSURAN":
				$flag 	= "<span class=\"label label-success\">ANGSURAN</span>";
				break;

			case "PELUNASAN":
				$flag 	= "<span class=\"label bg-orange\">PELUNASAN</span>";
				break;

			case "DENDA":
				$flag 	= "<span class=\"label label-danger\">DENDA</span>";
				break;

			case "PA":
				$flag 	= "<span class=\"label bg-orange\">PA</span>";
				break;

			case "AKTIF":
				$flag 	= "<span class=\"label bg-green\">AKTIF</span>";
				break;

			case "PUL":
				$flag 	= "<span class=\"label label-inverse\">PUL</span>";
				break;

			case "REGULER":
				$flag 	= "<span class=\"label bg-primary\">REGULER</span>";
				break;

			case "W-DENDA":
				$flag 	= "<span class=\"label bg-primary\">W-DENDA</span>";
				break;

			case "W-BUNGA":
				$flag 	= "<span class=\"label label-warning\">W-BUNGA</span>";
				break;

				// case "PUL" :
				// 	$flag 	= "<span class=\"label label-warning\">PUL</span>";
				// 	break;

			case "ABDA":
				$flag 	= "<span class=\"label label-danger\">ABDA</span>";
				break;

			case "POOL":
				$flag 	= "<span class=\"label label-warning\">POOL</span>";
				break;

			case "RECOVERY":
				$flag 	= "<span class=\"label \" style='background-color: #D66071'>RECOVERY</span>";
				break;

			case "STOCK":
				$flag 	= "<span class=\"label \" style='background-color: #8C14D6'>STOCK</span>";
				break;

			case "PENGAJUAN STOCK":
				$flag 	= "<span class=\"label \" style='background-color: #D69975'>PENGAJUAN STOCK</span>";
				break;

			case "TREASURY DONE":
				$flag 	= "<span class=\"label \" style='background-color: #1B6D4C'>TREASURY DONE</span>";
				break;

			case "PAID":
				$flag 	= "<span class=\"label label-warning\">PAID</span>";
				break;

			case "TBO":
				$flag 	= "<span class=\"label label-warning\">TBO</span>";
				break;

			case "PENGAJUAN":
				$flag 	= "<span class=\"label label-success\">PENGAJUAN</span>";
				break;

			case "SUDAH DIPROSES":
				$flag 	= "<span class=\"label label-success\">SUDAH DIPROSES</span>";
				break;

			case "SUDAH DITERIMA":
				$flag 	= "<span class=\"label label-primary\">SUDAH DITERIMA</span>";
				break;

			case "BELUM DITERIMA":
				$flag 	= "<span class=\"label label-danger\">BELUM DITERIMA</span>";
				break;

			case "APPROVE":
				$flag 	= "<span class=\"label label-success\">APPROVE</span>";
				break;

			case "APPROVE WITH NOTES":
				$flag 	= "<span class=\"label label-info\">APPROVE WITH NOTES</span>";
				break;

			case "REJECT":
				$flag 	= "<span class=\"label label-danger\">REJECT</span>";
				break;

			case "REJECT NEGATIVE":
				$flag 	= "<span class=\"label label-danger\">REJECT NEGATIVE</span>";
				break;

			case "BANDING":
				$flag 	= "<span class=\"label label-warning\">BANDING</span>";
				break;

			case "PENDING":
				$flag 	= "<span class=\"label label-warning\">PENDING</span>";
				break;

			case "BOOKING":
				$flag 	= "<span class=\"label label-info\">BOOKING</span>";
				break;

			case "GOLIVE":
				$flag 	= "<span class=\"label label-info\">GOLIVE</span>";
				break;

			case "PENGAJUAN REJECT":
				$flag 	= "<span class=\"label label-danger\">PENGAJUAN REJECT</span>";
				break;

			case "BANDING":
				$flag 	= "<span class=\"label label-warning\">BANDING</span>";
				break;

			case "BANDING REJECT":
				$flag 	= "<span class=\"label label-danger\">BANDING REJECT</span>";
				break;

			case "TOLAK":
				$flag 	= "<span class=\"label label-danger\">TOLAK</span>";
				break;

			case "RETURN":
				$flag 	= "<span class=\"label label-warning\">RETURN</span>";
				break;

			case "PENGURUSAN":
				$flag 	= "<span class=\"label label-danger\">PENGURUSAN</span>";
				break;

			case "DICETAK":
				$flag 	= "<span class=\"label label-warning\">DICETAK</span>";
				break;

			case "DIKIRIM":
				$flag 	= "<span class=\"label label-info\">DIKIRIM</span>";
				break;

			case "DITERIMA":
				$flag 	= "<span class=\"label label-primary\">DITERIMA</span>";
				break;

			case "EXPIRED":
				$flag 	= "<span class=\"label label-danger\">EXPIRED</span>";
				break;

			case "BATAL":
				$flag 	= "<span class=\"label label-danger\">BATAL</span>";
				break;

			case "PENYELESAIAN":
				$flag 	= "<span class=\"label label-success\">PENYELESAIAN</span>";
				break;

			case "PENARIKAN INTERNAL":
				$flag 	= "<span class=\"label label-success\">PENARIKAN INTERNAL</span>";
				break;

			case "PENARIKAN EKSTERNAL":
				$flag 	= "<span class=\"label label-danger\">PENARIKAN EKSTERNAL</span>";
				break;

			case "PENEBUSAN":
				$flag 	= "<span class=\"label label-warning\">PENEBUSAN</span>";
				break;

			case "PENDAMPINGAN":
				$flag 	= "<span class=\"label label-primary\">PENDAMPINGAN</span>";
				break;

			case "DEREK":
				$flag 	= "<span class=\"label label-info\">DEREK</span>";
				break;

			case "KLAIM":
				$flag 	= "<span class=\"label label-warning\">KLAIM</span>";
				break;

			case "APPROVE KLAIM":
				$flag 	= "<span class=\"label label-info\">APPROVE KLAIM</span>";
				break;

			case "PERJALANAN DINAS":
				$flag 	= "<span class=\"label label-info\">PERJALANAN DINAS</span>";
				break;

			case "PEMBUATAN AKTA":
				$flag 	= "<span class=\"label label-success\">PEMBUATAN AKTA</span>";
				break;

			case "PEMBUATAN LP":
				$flag 	= "<span class=\"label label-warning\">PEMBUATAN LP</span>";
				break;

			case "PENGEMBALIAN":
				$flag 	= "<span class=\"label label-warning\">PENGEMBALIAN</span>";
				break;
			case "RETURN CCU":
				$flag 	= "<span class=\"label label-danger\">RETURN CCU</span>";
				break;
			case "RETURN KLAIM CCU":
				$flag 	= "<span class=\"label label-danger\">RETURN KLAIM CCU</span>";
				break;

			case "RETURN KLAIM":
				$flag 	= "<span class=\"label label-warning\">RETURN KLAIM</span>";
				break;
			case "BELUM APPROVE":
				$flag 	= "<span class=\"label label-danger\">BELUM APPROVE</span>";
				break;
			case "SUDAH APPROVE":
				$flag 	= "<span class=\"label label-success\">SUDAH APPROVE</span>";
				break;
			case "PROSES LELANG":
				$flag 	= "<span class=\"label label-inverse\">PROSES LELANG</span>";
				break;
			case "PEMBAYARAN ANGSURAN":
				$flag 	= "<span class=\"label label-default\">PEMBAYARAN ANGSURAN</span>";
				break;
			case "TERJUAL":
				$flag 	= "<span class=\"label \" style='background-color: #02d6ab'>TERJUAL</span>";
				break;
			case "PROSES PENYELESAIAN":
				$flag 	= "<span class=\"label label-warning\">PROSES PENYELESAIAN</span>";
				break;
			case "RETURN PENYELESAIAN":
				$flag 	= "<span class=\"label label-danger\">RETURN PENYELESAIAN</span>";
				break;
			case "REJECT PENYELESAIAN":
				$flag 	= "<span class=\"label label-danger\">REJECT PENYELESAIAN</span>";
				break;
			case "APPROVE PENYELESAIAN":
				$flag 	= "<span class=\"label \" style='background-color: #b7471f'>APPROVE PENYELESAIAN</span>";
				break;
			case "PROSES RECOVERY":
				$flag 	= "<span class=\"label \" style='background-color: #f700d6'>PROSES RECOVERY</span>";
				break;
			case "PENGELUARAN":
				$flag 	= "<span class=\"label label-success\">PENGELUARAN</span>";
				break;
			case "BALAI LELANG":
				$flag 	= "<span class=\"label label-warning\">BALAI LELANG</span>";
				break;
			case "PENGAJUAN PUL":
				$flag 	= "<span class=\"label \" style='background-color: #707070'>PENGAJUAN PUL</span>";
				break;
			case "BATAL LELANG":
				$flag 	= "<span class=\"label \" style='background-color: #ff0000'>BATAL LELANG</span>";
				break;
			case "TIDAK DISETUJUI":
				$flag 	= "<span class=\"label \" style='background-color: #f26800'>TIDAK DISETUJUI</span>";
				break;
			case "PENGAJUAN RECOVERY":
				$flag 	= "<span class=\"label \" style='background-color: #707070'>PENGAJUAN RECOVERY</span>";
				break;
			case "PENGAJUAN LELANG":
				$flag 	= "<span class=\"label \" style='background-color: #ff7200'>PENGAJUAN LELANG</span>";
				break;
			case "DISETUJUI":
				$flag 	= "<span class=\"label \" style='background-color: #00c9a1'>DISETUJUI</span>";
				break;
			case "BELUM UPLOAD":
				$flag 	= "<span class=\"label label-danger\">BELUM UPLOAD</span>";
				break;
			case "SUDAH UPLOAD":
				$flag 	= "<span class=\"label label-success\">SUDAH UPLOAD</span>";
				break;
			case "BELUM DIHUBUNGI":
				$flag 	= "<span class=\"label label-warning\">BELUM DIHUBUNGI</span>";
				break;
			case "SUDAH DIHUBUNGI":
				$flag 	= "<span class=\"label label-success\">SUDAH DIHUBUNGI</span>";
				break;
			case "GAGAL DIHUBUNGI":
				$flag 	= "<span class=\"label label-danger\">GAGAL DIHUBUNGI</span>";
				break;
			case "MATCHING":
				$flag 	= "<span class=\"label label-success\">MATCHING</span>";
				break;
			case "INPUT SURVEY":
				$flag 	= "<span class=\"label label-warning\">INPUT SURVEY</span>";
				break;
			case "INPUT PENGAJUAN":
				$flag 	= "<span class=\"label label-primary\">INPUT PENGAJUAN</span>";
				break;
			case "PROSES KOMITE":
				$flag 	= "<span class=\"label label-success\">PROSES KOMITE</span>";
				break;
			case "HOLD KOMITE":
				$flag 	= "<span class=\"label label-warning\">HOLD KOMITE</span>";
				break;
			case "HOLD":
				$flag 	= "<span class=\"label label-warning\">HOLD</span>";
				break;
			case "RETURN KOMITE":
				$flag 	= "<span class=\"label label-warning\">RETURN KOMITE</span>";
				break;
			case "RE ENTRY BOOKING":
				$flag 	= "<span class=\"label label-warning\">RE ENTRY BOOKING</span>";
				break;
			case "RE ENTRY":
				$flag 	= "<span class=\"label label-warning\">RE ENTRY</span>";
				break;
			case "REJECT KOMITE":
				$flag 	= "<span class=\"label label-danger\">REJECT KOMITE</span>";
				break;
			case "RECOMMENDED":
				$flag 	= "<span class=\"label label-success\">RECOMMENDED</span>";
				break;
			case "PENGAJUAN BOOKING":
				$flag 	= "<span class=\"label label-success\">PENGAJUAN BOOKING</span>";
				break;
			case "PENGAJUAN PENCAIRAN":
				$flag 	= "<span class=\"label label-success\">PENGAJUAN PENCAIRAN</span>";
				break;
			case "PENCAIRAN":
				$flag 	= "<span class=\"label label-success\">PENCAIRAN</span>";
				break;
			case "PERUBAHAN KARTU ANGSURAN":
				$flag 	= "<span class=\"label label-success\">PERUBAHAN KARTU ANGSURAN</span>";
				break;
			case "RESTRUKTUR RATE":
				$flag 	= "<span class=\"label label-primary\">RESTRUKTUR RATE</span>";
				break;
			case "RESTRUKTUR TENOR":
				$flag 	= "<span class=\"label label-warning\">RESTRUKTUR TENOR</span>";
				break;
			case "MIRRORING":
				$flag 	= "<span class=\"label label-warning\">MIRRORING</span>";
				break;
			case "NON MIRRORING":
				$flag 	= "<span class=\"label label-warning\">NON MIRRORING</span>";
				break;
			case "PENGAJUAN PEMINJAMAN":
				$flag 	= "<span class=\"label label-warning\">PENGAJUAN PEMINJAMAN</span>";
				break;
			case "PENGAJUAN PENGEMBALIAN":
				$flag 	= "<span class=\"label label-warning\">PENGAJUAN PENGEMBALIAN</span>";
				break;
			case "APPROVE PEMINJAMAN":
				$flag 	= "<span class=\"label label-success\">APPROVE PEMINJAMAN</span>";
				break;
			case "APPROVE PENGEMBALIAN":
				$flag 	= "<span class=\"label label-success\">APPROVE PENGEMBALIAN</span>";
				break;
			case "PRIORITAS":
				$flag 	= "<span class=\"label label-warning\">PRIORITAS</span>";
				break;
			case "TERKIRIM":
				$flag 	= "<span class=\"label \" style='background-color: #52BC53'>TERKIRIM</span>";
				break;
			case "PROSES":
				$flag 	= "<span class=\"label \" style='background-color: #DC8919'>PROSES</span>";
				break;
			case "VERIFICATION FAILED":
				$flag 	= "<span class=\"label \" style='background-color: #DC1D1E'>VERIFICATION FAILED</span>";
				break;
			case "RELEASE":
				$flag 	= "<span class=\"label \" style='background-color: #2F77DC'>RELEASE</span>";
				break;
			case "VERIFIED":
				$flag 	= "<span class=\"label \" style='background-color: #74AB0F'>VERIFIED</span>";
				break;
			case "RECEIVED":
				$flag 	= "<span class=\"label \" style='background-color: #E1990E'>RECEIVED</span>";
				break;
			case "PEMBUATAN SURAT":
				$flag 	= "<span class=\"label \" style='background-color: #E18A83'>PEMBUATAN SURAT</span>";
				break;
			case "UPLOAD SK":
				$flag 	= "<span class=\"label \" style='background-color: #7E4E7E'>UPLOAD SK</span>";
				break;
			case "PROSES LP":
				$flag 	= "<span class=\"label \" style='background-color: #DC8919'>PROSES LP</span>";
				break;
			case "PENERIMAAN BOOKING":
				$flag 	= "<span class=\"label \" style='background-color: #717272'>PENERIMAAN BOOKING</span>";
				break;
			case "EXP - BOOKING":
				$flag 	= "<span class=\"label \" style='background-color: #c6a900'>EXP - BOOKING</span>";
				break;
			case "PENERIMAAN JAMINAN":
				$flag 	= "<span class=\"label \" style='background-color: #ff00fa'>PENERIMAAN JAMINAN</span>";
				break;
			case "PENERIMAAN JAMINAN":
				$flag 	= "<span class=\"label \" style='background-color: #05af85'>PENERIMAAN JAMINAN</span>";
				break;
			case "EXP - PINJAMAN":
				$flag 	= "<span class=\"label \" style='background-color: #db7800'>EXP - PINJAMAN</span>";
				break;
			case "PENERIMAAN PINJAMAN":
				$flag 	= "<span class=\"label \" style='background-color: #7f5196'>PENERIMAAN PINJAMAN</span>";
				break;
			case "BIROJASA":
				$flag 	= "<span class=\"label \" style='background-color: #ff3838'>BIROJASA</span>";
				break;
			case "PENGEMBALIAN BIROJASA":
				$flag 	= "<span class=\"label \" style='background-color: #9b6914'>PENGEMBALIAN BIROJASA</span>";
				break;
			case "EXP - PENGEMBALIAN PINJAMAN":
				$flag 	= "<span class=\"label \" style='background-color: #ffcc00'>EXP - PENGEMBALIAN PINJAMAN</span>";
				break;
			case "PENGEMBALIAN PINJAMAN":
				$flag 	= "<span class=\"label \" style='background-color: #2696bf'>PENGEMBALIAN PINJAMAN</span>";
				break;
			case "EXP - RELEASE":
				$flag 	= "<span class=\"label \" style='background-color: #82d811'>EXP - RELEASE</span>";
				break;
			case "PENERIMAAN RELEASE":
				$flag 	= "<span class=\"label \" style='background-color: #9335ff'>PENERIMAAN RELEASE</span>";
				break;
			case "EXP - PENGEMBALIAN RELEASE":
				$flag 	= "<span class=\"label \" style='background-color: #ff007f'>EXP - PENGEMBALIAN RELEASE</span>";
				break;
			case "PENGEMBALIAN RELEASE":
				$flag 	= "<span class=\"label \" style='background-color: #000000'>PENGEMBALIAN RELEASE</span>";
				break;
			case "PENGAJUAN PENGELUARAN":
				$flag 	= "<span class=\"label \" style='background-color: #0086b7'>PENGAJUAN PENGELUARAN</span>";
				break;
			case "PERSETUJUAN PENGELUARAN":
				$flag 	= "<span class=\"label \" style='background-color: #0f6b05'>PERSETUJUAN PENGELUARAN</span>";
				break;
			case "BANK":
				$flag 	= "<span class=\"label \" style='background-color: #00bf7c'>BANK</span>";
				break;
			case "PENGAJUAN PENGAMBILAN":
				$flag 	= "<span class=\"label \" style='background-color: #ff6600'>PENGAJUAN PENGAMBILAN</span>";
				break;
			case "PERSETUJUAN PENGAMBILAN":
				$flag 	= "<span class=\"label \" style='background-color: #82ba00'>PERSETUJUAN PENGAMBILAN</span>";
				break;
			case "PENERIMAAN BANK":
				$flag 	= "<span class=\"label \" style='background-color: #bf0000'>PENERIMAAN BANK</span>";
				break;
			case "PINDAH RAK":
				$flag 	= "<span class=\"label \" style='background-color: #a269aa'>PINDAH RAK</span>";
				break;
			case "R.O":
				$flag 	= "<span class=\"label \" style='background-color: #5d8968'>R.O</span>";
				break;
			case "BAYAR":
				$flag 	= "<span class=\"label label-success\">BAYAR</span>";
				break;
			case "TIDAK BAYAR":
				$flag 	= "<span class=\"label label-danger\">TIDAK BAYAR</span>";
				break;
			case "WO":
				$flag 	= "<span class=\"label label-danger\">WO</span>";
				break;
			case "DIKETAHUI":
				$flag 	= "<span class=\"label \" style='background-color: #cc8800'>DIKETAHUI</span>";
				break;
			default:
				$flag 	= "<span class=\"label bg-grey\">$var</span>";
		}
		# ---------------
		return $flag;
	}
}

if (!function_exists("getSelectStatusActiveInactive")) {
	function getSelectStatusActiveInactive()
	{
		$data = array(
			array("id" => "1", "name" => "Active"),
			array("id" => "2", "name" => "Inactive")
		);

		return $data;
	}
}

if (!function_exists("getSelectRoleAssignment")) {
	function getSelectRoleAssignment()
	{
		$data = array(
			array("id" => "REVIEWER", "name" => "REVIEWER"),
			array("id" => "APPROVER", "name" => "APPROVER"),
			array("id" => "OBSERVER", "name" => "OBSERVER"),
		);

		return $data;
	}
}

if (!function_exists("getSelectOrderAssignment")) {
	function getSelectOrderAssignment()
	{
		$data = array(
			array("id" => "1", "name" => "1"),
			array("id" => "2", "name" => "2"),
			array("id" => "3", "name" => "3"),
			array("id" => "4", "name" => "4"),
			array("id" => "5", "name" => "5"),
			array("id" => "6", "name" => "6"),
			array("id" => "7", "name" => "7"),
			array("id" => "8", "name" => "8"),
			array("id" => "9", "name" => "9"),
			array("id" => "10", "name" => "10"),
			array("id" => "11", "name" => "11"),
			array("id" => "12", "name" => "12"),
			array("id" => "13", "name" => "13"),
			array("id" => "14", "name" => "14"),
			array("id" => "15", "name" => "15")
		);

		return $data;
	}
}
if (!function_exists("getSelectMaintenanceMode")) {
	function getSelectMaintenanceMode()
	{
		$data = array(
			array("id" => "1", "name" => "ON"),
			array("id" => "2", "name" => "OFF")
		);

		return $data;
	}
}

if (!function_exists("getSelectExtention")) {
	function getSelectExtention()
	{
		$data = array(
			array("id" => "pdf", "name" => "PDF"), array("id" => "xlsx", "name" => "EXCEL"), array("id" => "jpg", "name" => "JPG"), array("id" => "docx", "name" => "DOC")
		);

		return $data;
	}
}

if (!function_exists("getSelectStatusDocument")) {
	function getSelectStatusDocument()
	{
		$data = array(
			array("id" => "1", "name" => "Unissued"),
			array("id" => "2", "name" => "Waiting for reviewer"),
			array("id" => "3", "name" => "Waiting for compiler"),
			array("id" => "4", "name" => "Waiting for return"),
			array("id" => "5", "name" => "Waiting for approval"),
			array("id" => "6", "name" => "Done"),
			array("id" => "7", "name" => "Waiting for view"),
			array("id" => "99", "name" => "Stored")
		);

		return $data;
	}
}

if (!function_exists("getSelectStatusEmail")) {
	function getSelectStatusEmail()
	{
		$data = array(
			array("id" => 1, "name" => "NO SEND EMAIL"),
			array("id" => 2, "name" => "YES SEND EMAIL")
		);

		return $data;
	}
}

if (!function_exists("transposeMultiSelect")) {
	function transposeMultiSelect($data)
	{
		if(!empty($data)) {
			$ext    = "";
		
	        foreach($data as $extention) {
	            $ext   .= $extention . ",";
	        }

			return substr($ext, 0, strlen($ext)-1);
		} else {
			return "";
		}
	}
}

if (!function_exists("getSelectIsDocumentIdc")) {
	function getSelectIsDocumentIdc()
	{
		$data = array(
			array("id" => "NO", "name" => "NO"),
			array("id" => "YES", "name" => "YES")
		);

		return $data;
	}

	if (!function_exists("getSelectStatusInterface")) {
		function getSelectStatusInterface()
		{
			$data = array(
				array("id" => "2", "name" => "Viewed")
			);
	
			return $data;
		}
	}
}