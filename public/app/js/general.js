function trim(stringToTrim) {
	   return stringToTrim.replace(/^\s+|\s+$/g,"");
}

function handleEnter (field, event) {
		var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
		if (keyCode == 13) {
				var i;

				for (i = 0; i < field.form.elements.length; i++)
					if (field == field.form.elements[i])
						break;
				i = (i + 1) % field.form.elements.length;

				var len = field.form.elements[i].value.length;

				field.form.elements[i].focus();
				field.form.elements[i].setSelectionRange(len, len);
				return false;
		} else {
				return true;
		}
}

function focusObject(objDest, event) {
		var keyCode 	= event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
		var len 		= objDest.value.length;

		if(keyCode == 13) {
				objDest.focus();
				objDest.setSelectionRange(len, len);
				return false;
		} else {
				return true;
		}
}

function formatCurrency(num) {
		num = num.toString().replace(/\$|\,/g,'');
		if(isNaN(num))
		num = "0";
		sign = (num == (num = Math.abs(num)));
		num = Math.floor(num*100+0.50000000001);
		//cents = num%100;
		num = Math.floor(num/100).toString();
		//if(cents<10)
		//cents = "0" + cents;
		for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
		num = num.substring(0,num.length-(4*i+3))+','+
		num.substring(num.length-(4*i+3));
		return (((sign)?'':'-') + '' + num);
}

function formatCurrencyDec(num) {
    num = num.toString().replace(/\$|\,/g,'');
    if(isNaN(num))
    num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num*100+0.50000000001);
    cents = num%100;
    num = Math.floor(num/100).toString();
    if(cents<10)
    cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
    num = num.substring(0,num.length-(4*i+3))+','+
    num.substring(num.length-(4*i+3));
    return (((sign)?'':'-') + '' + num + '.' + cents);
}

function isNumberKey(field, evt, act) {
    evt 		 = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    
    if(charCode != 13) {    
	    if (charCode != 37 && charCode != 39 && charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
	        return false;	  
	    }
	} else {
		if(act != "enter") {
			return focusObject(act, evt);
		} else {
			if(act == "enter") {
				return handleEnter(field, evt);
			}
		}
	}
	
    return true;
}