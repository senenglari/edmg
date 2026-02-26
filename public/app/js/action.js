$(".btn-none-direct").click(function() {
  	var url 				= $(this).attr("title");
  	// --------------
  	window.location.href 	= url;
});

$(".btn-excel").click(function() {
    var url         = $(this).attr("title");
    // --------------
    window.open(url, '_blank');
});

$(".btn-modal").click(function() {
    var url         = $(this).attr("title");
    // --------------
    window.location.href  = url;
});

$(".btn-single-direct").click(function () {
  	var searchIDs = [];
  	var atLeastOneIsChecked = $(".checkbox_id:checked").length;

  	if(atLeastOneIsChecked == 0) {
		$("#alert-message").text("No item selected (0)");
		$("#alert-box").slideDown(100).delay(1000).slideUp(500);
  	} else if(atLeastOneIsChecked == 1) {
		$("#myform input:checkbox:checked").map(function(){
			searchIDs.push($(this).val());
		});

		var url = $(this).attr("title") + "/" + searchIDs;

		window.location.href 	= url;
  	} else {
		$("#alert-message").text("Please select only one item");
		$("#alert-box").slideDown(100).delay(1000).slideUp(500);
  	}
});

$(".btn-single-popup").click(function () {
    var searchIDs = [];
    var atLeastOneIsChecked = $(".checkbox_id:checked").length;

    if(atLeastOneIsChecked == 0) {
        $("#alert-message").text("No item selected (0)");
        $("#alert-box").slideDown(100).delay(1000).slideUp(500);
    } else if(atLeastOneIsChecked == 1) {
        $("#myform input:checkbox:checked").map(function(){
            searchIDs.push($(this).val());
        });

        var url = $(this).attr("title") + "/" + searchIDs;

        LaunchWindow(url);
    } else {
        $("#alert-message").text("Please select only one item");
        $("#alert-box").slideDown(100).delay(1000).slideUp(500);
    }
});

$(".btn-direct-popup").click(function () {
    var url         = $(this).attr("title");
    // --------------
    // window.open(url, "_blank", "toolbar=1, scrollbars=1, resizable=1, width=" + 1015 + ", height=" + 800);
    window.open(url, "_blank")
    // window.location.href  = url;
});

// $(".btn-single-prompt").click(function () {
// 	var searchIDs 			= [];
// 	var atLeastOneIsChecked = $(".checkbox_id:checked").length;

// 	if(atLeastOneIsChecked == 0) {
// 		$("#alert-message").text("No item selected (0)");
// 		$("#alert-box").slideDown(100).delay(1000).slideUp(500);
// 	} else if(atLeastOneIsChecked == 1) {
// 		$("#myform input:checkbox:checked").map(function(){
// 			searchIDs.push($(this).val());
// 		});

// 		var url_comb = $(this).attr("title").split("|");

// 		var url = url_comb[0] + "/" + searchIDs;

// 		swal({
//             title: url_comb[1],
//             type: "input",
//             showCancelButton: true,
//             confirmButtonColor: "#2196F3",
//             closeOnConfirm: false,
//             animation: "slide-from-top",
//             inputPlaceholder: url_comb[1]
//         },
//         function(inputValue){
//             if (inputValue === false) return false;
//             if (inputValue === "") {
//                 swal.showInputError("Anda perlu menulis sesuatu!");
//                 return false
//             }

// 			window.location.href 	= url;
//         });
// 	} else {
// 		$("#alert-message").text("Please select only one item");
// 		$("#alert-box").slideDown(100).delay(1000).slideUp(500);
// 	}
// });

function LaunchWindow(url) { 
    var str = "left=0,screenX=0,top=0,screenY=0";

    var ah = screen.availHeight - 30;
    var aw = screen.availWidth - 10;
    var sc = "yes";
    str += ",height=" + ah;
    str += ",innerHeight=" + ah;
    str += ",width=" + aw;
    str += ",innerWidth=" + aw;
    str += ",scrollbars=" + sc;
    str += ",resizable";    
    
    window.open(url,"MyWindow", str);
}