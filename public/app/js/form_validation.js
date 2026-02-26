$(".first-selected").focus();
$(".first-selected").select();

$("#myform").submit(function(){
    var flag = "F";
    var no = 1;

    $(".mandatory-input").each(function() {
        var obj = $(this).attr("name");

        if((trim($(this).val()) == "") || (trim($(this).val()) == "0") || (trim($(this).val()) == "__/__/____") || (trim($(this).val()) == "00/00/0000")) {
            $(this).css("border-color", "red");
            $(".style_form_input_" + obj).css("border-color", "red");
            $(".style_form_input_" + obj).css("color", "red");

            if(no == 1) {
                $(this).focus();
                no = 2;
            }

            flag = "T";
        } else {
            $(this).css("border-color", "#DDDDDD");
            $(".style_form_input_" + obj).css("border-color", "#DDDDDD");
            $(".style_form_input_" + obj).css("color", "#DDDDDD");
        }
    });

    if(flag == "T") {
        return false;
    }

    $(".button-container").hide();
    $(".preloader-container").show();
});
