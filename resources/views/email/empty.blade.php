<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Document Review Notification</title>
	<style type="text/css">
		body {
			text-align: justify;
			font-size: 10px;
			font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
			margin:auto;
			margin-bottom:1px;
			margin-left:10px;
			margin-right:10px;
		}

		.fontbast {
			text-align: justify;
			font-size: 9px;
			font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
			margin:auto;
		}
		table.border, .border td, .border th {
			border: 1px solid black;
			vertical-align: top;
		}

		table.border {
			border-collapse: collapse;
			width: 100%;
		}

		table.border th {
			height: 50px;
		}

		table.noborder, .noborder td, .noborder th {
			border: none;
			vertical-align: top;
    		padding:0;
    		border-spacing: 0;
		}

		table.noborder {
			width: 100%;
		}

		table.noborder th {
			height: 50px;
		}

		.m-t-100 {
			margin-top: 100px;
		}

		.page_break { page-break-before: always; }

        .tab1 { 
            tab-size: 2; 
        } 
        footer {
                position: fixed; 
                bottom: 0px; 
                left: 0px; 
                right: 0px;
                /** Extra personal styles **/
                text-align: right;
            }
	</style>
</head>
<body>
    <footer>
    MEDCO E P NATUNA Ltd. | The Energy Building SCBD Area Lot. 11A ,<br/>
    JI. JENDRAL SUDIRMAN KAV JAKARTA 10190 INDONESIA<br/>
    |P +62 21 29954000 | F +62 21 29954001
    </footer>
	<table class="border">
        <tr>
            <td style="text-align:center; vertical-align:middle; width:30%; height:60px;">
                <img src="{{ $logo_medco }}" style="width: 40px; height: 40px;" />
            </td>
            <td style="text-align:center; vertical-align:middle; width:70%;">
                <b>TRANSMITTAL SHEET<br/><br/>
                <u>FOREL FPSO TIME CHARTER</u><br/>
                CONTRACT NO: 3510006954</b>
            </td>
            <td style="text-align:center; vertical-align:middle; width:30%;">
                <img src="{{ $logo_hanochem }}" style="width: 40px; height: 40px;" />
                <img src="{{ $logo_kanan_tengah }}" style="width: 40px; height: 40px;" />
                <img src="{{ $logo_kanan_pojok }}" style="width: 40px; height: 40px;" />
            </td>
        </tr>
    </table>
	<table class="noborder">
        <tr>
            <td style="width:30%">
	            <table class="noborder">
                    <tr>
                        <td style="font-weight:bold; width:20%;"><u>FROM :</u></td>
                        <td style="font-weight:bold; width:80%;">{{$from}}</td>
                    </tr>
                    <tr>
                        <td><u>Name :</u></td>
                        <td>{{$name}}</td>
                    </tr>
                    <tr>
                        <td><u>Address :</u></td>
                        <td>Jalan Kyai Maja No. 1 <br/>Kebayoran Baru, Jakarta Selatan 12120 <br/>Phone : +6221-727-86837</td>
                    </tr>
                </table>
            </td>
            <td style="width:5%;">&nbsp;</td>
            <td style="width:30%">
                <table class="noborder">
                    <tr>
                        <td style="font-weight:bold;"><u>Contract n° :</u></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black;">
                            <table class="noborder">
                                <tr>
                                    <td width="30%"><u>Transmittal N° :</u></td>
                                    <td width="70%">{{$transmittal}}</td>
                                </tr>
                                <tr>
                                    <td><u>Issue Date :</u></td>
                                    <td>{{$issueddate}}</td>
                                </tr>
                                <tr>
                                    <td>Remarks :</td>
                                    <td>{{$remarks}}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width:5%;">&nbsp;</td>
            <td style="width:30%">
	            <table class="noborder">
                    <tr>
                        <td style="font-weight:bold; width:20%;"><u>To :</u></td>
                        <td style="width:80%;">{{$to}}</td>
                    </tr>
                    <tr>
                        <td><u>Attn :</u></td>
                        <td style="font-weight:bold;">{{$attn}}</td>
                    </tr>
                    <tr>
                        <td><u>Address :</u></td>
                        <td>SCBD Area Lot 11A <br/>JL Jendral Sudirman, Jakarta 12190 <br/>Indonesia</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
	<table class="noborder">
        <tr>
            <td style="width:40%; border-top: 1px solid black;">
                <table class="noborder">
                    <tr>
                        <td width="70%" >
                        Transmittal and documents have been delivered<br/><br/>

                        <u><b>Return Code</b></u><br/>
                        1 : Approved without Comments<br/>
                        2 : Approved with Comments<br/>
                        3 : Returned without Comments<br/>
                        4 : Returned with Comments<br/>
                        </td>
                        <td style="vertical-align:top; width:30%;">
                            <table class="noborder">
                                <tr>
                                    <td width="50%" style="font-weight:bold;">CD</td>
                                    <td style="border:1px solid black; width:40%; text-align: center;"></td>
                                    <td style="width:10%;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="50%" style="font-weight:bold;">E-mail</td>
                                    <td style="border:1px solid black; width:40%; text-align: center;"></td>
                                    <td style="width:10%;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="50%" style="font-weight:bold;">Courier</td>
                                    <td style="border:1px solid black; width:40%; text-align: center;"></td>
                                    <td style="width:10%;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="50%" style="font-weight:bold;">Exchange</td>
                                    <td style="border:1px solid black; width:40%; text-align: center;"></td>
                                    <td style="width:10%;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="50%" style="font-weight:bold;">Folder</td>
                                    <td style="border:1px solid black; width:40%; text-align: center;"></td>
                                    <td style="width:10%;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="50%" style="font-weight:bold;">Other</td>
                                    <td style="border:1px solid black; width:40%; text-align: center;"></td>
                                    <td style="width:10%;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width:30%; border:1px solid black;">
                <table class="noborder">
                    <tr>
                        <td colspan="2" style="font-weight:bold;"><u>Issued by :</u></td>
                    </tr>
                    <tr>
                        <td style="width:20%;">Name :</td>
                        <td style="width:80%;">{{$issuedname}}</td>
                    </tr>
                    <tr>
                        <td style="width:20%;">Date :</td>
                        <td style="width:80%;">{{$issueddate}}</td>
                    </tr>
                    <tr>
                        <td style="width:20%;">Signature :</td>
                        <td style="width:80%;">{{$issuedsiganture}}</td>
                    </tr>
                </table>
            </td>
            <td style="width:30%; border:1px solid black;">            
                <table class="noborder">
                    <tr>
                        <td colspan="2" style="font-weight:bold;"><u>Acknowledgement of receipt :</u></td>
                    </tr>
                    <tr>
                        <td style="width:20%;">Name :</td>
                        <td style="width:80%;"></td>
                    </tr>
                    <tr>
                        <td style="width:20%;">Date :</td>
                        <td style="width:80%;"></td>
                    </tr>
                    <tr>
                        <td style="width:20%;">Signature :</td>
                        <td style="width:80%;"></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
	<table class="noborder">
        <tr>
            <td width="45%">
                <table class="noborder">
                    <tr>
                        <td width="63%">
                            &nbsp;
                        </td>
                        <td width="37%" style="border-bottom: 1px solid black;">
                            :
                        </td>
                    </tr>
                    <tr>
                        <td width="63%">
                            &nbsp;
                        </td>
                        <td width="37%">
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border:1px solid black;">
                            We are pleased to deliver to your Company the following documents.<br/>
                            Prior to be sent, these documents have been checked as per contract requirements.<br/>
                            Information included in this transmittal for each document is in accordance with the document itself.<br/>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="55%" style="border:1px solid black;">
                <table>
                    <tr>
                        <td colspan="3">Next Expected Submission:</td>
                    </tr>
                    <tr>
                        <td><b>IFC</b> : Issued for Comment</td>
                        <td><b>AFC</b> : Approved for Construction</td>
                        <td><b>ACP</b> : Approved for Concept & Pre-FEED</td>
                    </tr>
                    <tr>
                        <td><b>IFA</b> : Issued for Approval</td>
                        <td><b>ASB</b> : As Built</td>
                        <td><b>CLO</b> : Closed</td>
                    </tr>
                    <tr>
                        <td><b>AFU</b> : Approved for Use</td>
                        <td><b>ADM</b> : Approved for Demolition</td>
                        <td><b>NOR</b> : Not Required for further Submission</td>
                    </tr>
                    <tr>
                        <td><b>AFD</b> : Approved for Design</td>
                        <td colspan="2"><b>CLEAN</b>: Clean Sheet (for AFU/AFD/AFC/Concept & Pre-FEED)</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br/>
    
    @php
        $list_doc 		= explode("@", $doc_no_listing);
        $list_title 	= explode("@", $doc_title_listing);
        $list_issue 	= explode("@", $issue_status_listing);
        $list_status 	= explode("@", $doc_status_listing);
        $list_rev_number= explode("@", $doc_inc_no);
        $list_deadline  = explode("@", $deadline_listing);
        
    @endphp
	<table class="border" width="100%">
        <tr>
            <td style="text-align:center; vertical-align:middle; font-weight:bold; height:40px; width:20%; background-color: #A9A9A9;">Document Number</td>
            <td style="text-align:center; vertical-align:middle; font-weight:bold; height:40px; width:8%; background-color: #A9A9A9;">Rev</td>
            <td style="text-align:center; vertical-align:middle; font-weight:bold; height:40px; width:15%; background-color: #A9A9A9;">Purpose issue</td>
            <td style="text-align:center; vertical-align:middle; font-weight:bold; height:40px; width:22%; background-color: #A9A9A9;">Title</td>
            <td style="text-align:center; vertical-align:middle; font-weight:bold; height:40px; width:8%;  background-color: #A9A9A9;">Due date(5 working days)</td>
            <td style="text-align:center; vertical-align:middle; font-weight:bold; height:40px; width:5%; background-color: #A9A9A9;">Return Code</td>
            <td style="text-align:center; vertical-align:middle; font-weight:bold; height:40px; width:12%; background-color: #A9A9A9;">Next Expected Submission</td>
            <td style="text-align:center; vertical-align:middle; font-weight:bold; height:40px; width:10%; background-color: #A9A9A9;">Remarks</td>
        </tr>
        @foreach($list_doc as $i => $val)
        <tr>
            <td style="text-align: center; font-size:9px;">{{ isset($list_doc[$i]) ? $list_doc[$i] : "" }}</td>
            <td style="text-align: center; font-size:9px;">{{ isset($list_status[$i]) ? $list_status[$i] : "" }}</td>
            <td style="text-align: center; font-size:9px;">{{ isset($list_issue[$i]) ? $list_issue[$i] : "" }}</td>
            <td style="text-align: left; font-size:9px;">{{ isset($list_title[$i]) ? $list_title[$i] : "" }}</td>
            <td style="text-align: center; font-size:9px;">{{ isset($list_deadline[$i]) ? $list_deadline[$i] : "" }}</td>
            <td style="text-align: center; font-size:9px;"></td>
            <td style="text-align: center; font-size:9px;"></td>
            <td style="text-align: center; font-size:9px;">&nbsp;</td>
        </tr>
        @endforeach
	</table>
</body>
</html>

