<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>TRANSMITTAL LETTER</title>
	<style type="text/css">
		body {
			text-align: justify;
			font-size: 14px;
			font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
			margin:auto;
			margin-top:100px;
			/* margin-bottom:1px; */
			margin-left:20px;
			margin-right:20px;
		}

		.fontbast {
			text-align: justify;
			font-size: 9px;
			font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
			margin:auto;
		}

		/* table.noborder, .noborder td, .noborder th {
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

		table.border, .border td, .border th {
			border: 1px solid black;
			vertical-align: top;
		} */
        table.border, .border td, .border th {
            border: 1px solid;
            vertical-align: top;
        }

        table.border {
        width: 100%;
        border-collapse: collapse;
        }

        table.noborder, .noborder td, .noborder th {
			border: none;
			vertical-align: top;
		}

        table.noborder {
        width: 100%;
        }

		/* table.border {
			border-collapse: collapse;
			width: 100%;
		}

		table.border th {
			height: 50px;
		} */

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
        header {
            position: fixed; 
            top: 0px; 
            left: 0px; 
            right: 0px;
            /** Extra personal styles **/
            text-align: left;
            margin-left:20px;
            margin-right:20px;
        }
	</style>
</head>
<body>
    <header>
        <table class="noborder">
            <tr>
                <td style="text-align:left; vertical-align:top; width:60%; border-bottom:1px solid #0070C0; color:#0070C0;">
                    <b>CONSORTIUM HANOCHEM-CHAKRA-MTC</b><br/>  
                    Jalan Kyai Maja No. 11<br/>
                    Gunung, Kebayoran Baru<br/>
                    Jakarta Selatan 12120<br/>
                    No Tel :+6221-727-86837
                </td>
                <td style="text-align:right; vertical-align:middle; width:40%; border-bottom:1px solid #0070C0;">
                    <img src="{{ $logo_hanochem }}" style="width: 70px; height: 70px;" />
                    <img src="{{ $logo_kanan_tengah }}" style="width: 70px; height: 70px;" />
                    <img src="{{ $logo_kanan_pojok }}" style="width: 70px; height: 70px;" />
                </td>
            </tr>
        </table>
    </header>
    <footer>FRM.TML.05</footer>

    <p><b>TRANSMITTAL LETTER</b></p>
	<table class="noborder">
        <tr>
            <td style="width: 60%;">
            {{$vendor_name}}<br/>
            {!! wordwrap($vendor_address,45,'<br>') !!}<br/>
            Tel switchboard: {{$vendor_phone_number}}
            </td>   
            <td style="width: 40%;">
            Date : <?php echo date('d/m/Y')?><br/>
            Transmittal No : {{$subject}}<br/>
            Contract No : <br/>
            Ref No :
            </td>
        </tr>
        <tr>
            <td colpspan="2">
                Attn to : {{$vendor_pic}}
            </td>
        </tr>
        <tr>
            <td colpspan="2">
                PROJECT TITLE : {{$project_name}}
            </td>
        </tr>
    </table>
    <p>Attached herewith, please find eclosed --</p>
    <table class="border" width="100%">
        <tr>
            <td style="width:3%; text-align:center;">No</td>
            <td style="width:40%; text-align:center;">Document No</td>
            <td style="text-align:center;">Rev</td>
            <td style="width:40%; text-align:center;">Title/Description</td>
            <td style="text-align:center;">Next Status</td>
            <td style="text-align:center;">Code (Additional)</td>
        </tr>
        @foreach($detail as $i => $value)
            @php
            $return_status_name = [1=>'AWTC', 2=>'AWC', 3=>'RWTC', 4=>'RWC'];
            $return_status_value= array_search($value->return_status_name, $return_status_name);
            @endphp
        <tr>
            <td>{{$i+1}}</td>
            <td style="text-align:center;">{{$value->document_no}}</td>
            <td style="text-align:center;">{{$value->document_status_name}}</td>
            <td>{{$value->document_title}}</td>
            <td style="text-align:center;">{{$value->issue_status_name}}</td>
            <td style="text-align:center;">{{$return_status_value}}</td>
        </tr>
        @endforeach
        @php 
            $isActiveIFC = in_array('IFC', $issue_status_name) ? 'checked' : '';
            $isActiveIFA = in_array('IFA', $issue_status_name) ? 'checked' : '';
            $isActiveIFU = in_array('IFU', $issue_status_name) ? 'checked' : '';
            $isActiveIFU = in_array('IFU', $issue_status_name) ? 'checked' : '';
            $isActiveIFD = in_array('IFD', $issue_status_name) ? 'checked' : '';
            $isActiveIFI = in_array('IFI', $issue_status_name) ? 'checked' : '';
            $isActiveREC = in_array('REC', $issue_status_name) ? 'checked' : '';
            $isActiveAFU = in_array('AFU', $issue_status_name) ? 'checked' : '';
            $isActiveAFD = in_array('AFD', $issue_status_name) ? 'checked' : '';
            $isActiveASB = in_array('ASB', $issue_status_name) ? 'checked' : '';
            $isActiveCAN = in_array('CAN', $issue_status_name) ? 'checked' : '';
        @endphp
    </table>
    <p>Reference:</p>
	<table class="border">
        <tr>
            <td>
	            <u>ISSUED FOR:</u>
                <table class="noborder" style="border-spacing: 0px;">
                    <tr>
                        <td><input type="checkbox" style="margin-top:3px; padding:none;" {{$isActiveIFC}}> IFC: Issued for Comment</td>
                        <td><input type="checkbox" style="margin-top:3px; padding:none;" {{$isActiveAFU}}> AFU: Approved for Use</td>
                        <td><input type="checkbox" style="margin-top:3px; padding:none;" {{$isActiveCAN}}> CAN: CANCELLED</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" style="margin-top:3px;" {{$isActiveIFA}}> IFA: Issued for Approval</td>
                        <td><input type="checkbox" style="margin-top:3px;" {{$isActiveAFD}}> AFD: Approved for Design</td>
                        <td><input type="checkbox" style="margin-top:3px;" > REC: Record/Reference</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" style="margin-top:3px;" {{$isActiveIFU}}> IFU: Issued for Use</td>
                        <td colspan="2"><input type="checkbox" style="margin-top:3px;" > ASB: Approved for Construction</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" style="margin-top:3px;"> IFU: Issued for Use</td>
                        <td colspan="2"><input type="checkbox" style="margin-top:3px;" {{$isActiveASB}}> ASB: Approved for Construction</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" style="margin-top:3px;" {{$isActiveIFI}}> IFI: Issued for Information</td>
                        <td colspan="2"><input type="checkbox" style="margin-top:3px;"> Return Comment</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" style="margin-top:3px;"> REC: Record/Reference</td>
                        <td colspan="2"><input type="checkbox" style="margin-top:3px;" checked> For Action</td>
                    </tr>
                </table>
                <table class="noborder">
                    <tr>
                        <td><input type="checkbox" style="margin-top:3px;" checked> By Email</td>
                        <td><input type="checkbox" style="margin-top:3px;"> By Courier</td>
                        <td><input type="checkbox" style="margin-top:3px;"> By Fax</td>
                        <td><input type="checkbox" style="margin-top:3px;"> By Hand</td>
                    </tr>   
                </table>
            </td>
        </tr>
    </table>
    <table class="border">
        <tr>
            <td>
                <u>REVIEW CODE STATUS:</u> 
                <table class="noborder">
                    <tr>
                        <td><input type="checkbox" style="margin-top:3px;"> CODE 1 : Approved without Comments</td>
                        <td><input type="checkbox" style="margin-top:3px;"> CODE 4 : Returned with Comments</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" style="margin-top:3px;"> CODE 2 : Approved with Comments</td>
                        <td><input type="checkbox" style="margin-top:3px;"> CODE 5 : Not Approved</td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="checkbox" style="margin-top:3px;"> CODE 3 : Returned without Comments</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>        
    <p>REMARKS:</p>
    <table class="border">
        <tr>
            <td width="50%">
                Transmitted By:<br/><br/>
                &nbsp;&nbsp;&nbsp;Document Controller
            </td>
            <td width="50%">Received By:</td>
        </tr>
    </table>
    <br/>
    <table class="border">
        <tr>
            <td width="50%">
                <table class="noborder">
                    <tr>
                        <td width="7%">Name</td>
                        <td width="1%">:</td>
                        <td width="42%">Document Controller</td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>:</td>
                        <td><?php echo date('d/m/Y')?></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>:</td>
                        <td>docmtc@gmail.com</td>
                    </tr>
                </table>
            </td>
            <td width="50%">
                <table class="noborder">
                    <tr>
                        <td width="7%">Name</td>
                        <td width="1%">:</td>
                        <td width="43%"></td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>:</td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <i>Please Acknowledge and return of this transmittal to Document Control Dept. to be retained as evidence of receipts.</i>
</body>
</html>

