<body style="font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; font-size: 11px;">
<table cellspacing="0" cellpadding="0" border="0" width="700px" style="top: 50%;left: 50%; ">
	<tr>
		<td style="background-color: #ffffff; font-weight: bold; font-size: 16px; padding: 30px; color: #00AF40; border-top: 1px solid #EDEDED; border-left: 1px solid #EDEDED; border-right: 1px solid #EDEDED; text-align: right;" colspan="2">{{ $title }} : {{ $inc_no }}</td>
	</tr>
	<tr style="font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; font-size: 11px; color: #B1A3B4; background-color: #F4F4F4;">
		<td width="150" colspan="2" style="padding: 30px 0 0 30px;border-left: 1px solid #EDEDED; border-right: 1px solid #EDEDED;">
			<table cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td style="border: 1px #00AF40 solid; border-right: 0; color: #454545; font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; font-size: 11px; font-weight: bold; height: 30px; text-align: center; width: 150px;">Document Number</td>
					<td style="border: 1px #00AF40 solid; border-right: 0; color: #454545; font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; font-size: 11px; font-weight: bold; text-align: center; width: 250px;">Document Name</td>
					<td style="border: 1px #00AF40 solid; border-right: 0; color: #454545; font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; font-size: 11px; font-weight: bold; text-align: center; width: 100px;">Issue Status</td>
					<td style="border: 1px #00AF40 solid; color: #454545; font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; font-size: 11px; font-weight: bold; text-align: center; width: 100px;">Revision Number</td>
				</tr>				
				<tr>
					<td style="border: 1px #00AF40 solid; border-right: 0; border-top: 0; color: #454545; font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; font-size: 11px; font-weight: bold; height: 30px; text-align: center;">{{ $contents->document_no }}</td>
					<td style="border: 1px #00AF40 solid; border-right: 0; border-top: 0; color: #454545; font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; font-size: 11px; font-weight: bold; height: 30px; text-align: left;">&nbsp;&nbsp;{{ $contents->document_title }}&nbsp;&nbsp;</td>
					<td style="border: 1px #00AF40 solid; border-right: 0; border-top: 0; color: #454545; font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; font-size: 11px; font-weight: bold; height: 30px; text-align: center;">{{ $contents->issue_status_name }}&nbsp;&nbsp;&nbsp;</td>
					<td style="border: 1px #00AF40 solid; border-top: 0; color: #454545; font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; font-size: 11px; font-weight: bold; height: 30px; text-align: center;">{{ $contents->document_status_name }}&nbsp;&nbsp;&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="background-color: #F4F4F4; font-weight: bold; font-size: 16px; padding: 15px; color: #00AF40;border-left: 1px solid #EDEDED; border-right: 1px solid #EDEDED;"></td>
	</tr>
	<tr>
		<td colspan="2" style="background-color: #ffffff; font-weight: bold; font-size: 11px; padding: 30px 0 5px 30px; color: #454545;border-left: 1px solid #EDEDED; border-right: 1px solid #EDEDED;">Automatic mail system</td>
	</tr>
	<tr>
		<td colspan="2" style="background-color: #ffffff; font-weight: bold; font-size: 11px; padding: 0 0 5px 30px; color: #454545;border-left: 1px solid #EDEDED; border-right: 1px solid #EDEDED;">This is an automatic message, please do not reply .</td>
	</tr>
	<tr>
		<td colspan="2" style="background-color: #ffffff; font-size: 11px; padding: 10px 0 30px 30px; color: #454545;border-left: 1px solid #EDEDED; border-right: 1px solid #EDEDED; border-bottom: 1px solid #EDEDED;"></td>
	</tr>
</table>
</body>