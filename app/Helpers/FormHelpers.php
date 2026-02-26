<?php
//use Storage;
if (!function_exists("uploadHelper")) {
}

if (!function_exists("form_action_button_blank")) {
    function form_action_button_blank($var)
    {
        return  "<button type=\"button\" class=\"btn btn-sm btn-warning\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" onclick=\"window.open('" . url($var["url"]) . "', '_blank')\">" . $var["label"] . "</button>";
    }
}


if (!function_exists("form_button_submit")) {
    function form_button_submit($var)
    {
        return  "<button type=\"submit\" class=\"btn btn-sm btn-primary\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\">" . $var["label"] . "</button>";
    }
}

if (!function_exists("form_button_update")) {
    function form_button_update($var)
    {
        return  "<button type=\"submit\" class=\"btn btn-sm btn-default\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\">" . $var["label"] . "</button>";

    }
}

if (!function_exists("form_button_confirm")) {
    function form_button_confirm($var)
    {
        return  "<button type=\"submit\" onclick=\"return confirm('" . $var["confirm"] . "')\" class=\"btn btn-sm btn-primary\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\">" . $var["label"] . "</button>";
    }
}

if (!function_exists("form_button_cancel")) {
    function form_button_cancel($var)
    {
        return     "<button type=\"button\" class=\"btn btn-sm btn-default\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" onclick=\"history.go(-1)\">" . $var["label"] . "</button>";
    }
}

if (!function_exists("form_button_cancel2")) {
    function form_button_cancel2($var)
    {
        return    "<button type=\"button\" class=\"btn btn-sm btn-default\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" onclick=\"location.href = '" . $var["action"] . "'\">" . $var["label"] . "</button>";
    }
}

if (!function_exists("form_button_window")) {
    function form_button_window($var)
    {
        return  "<button type=\"button\" class=\"btn btn-sm btn-warning\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\"  onClick=\"LWindow();\">" . $var["label"] . "</button>";
    }
}

if (!function_exists("form_action_button")) {
    function form_action_button($var)
    {
        return  "<button type=\"button\" class=\"btn btn-sm btn-warning\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" onclick=\"window.location='" . url($var["url"]) . "'\">" . $var["label"] . "</button>";
    }
}

if (!function_exists("form_button_back")) {
    function form_button_back($var)
    {
        return  "<button type=\"button\" class=\"btn btn-sm btn-inverse\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" onclick=\"window.location='" . url($var["url"]) . "'\">" . "<i class=\"fa fa-reply\"></i>" . $var["label"] . "</button>";
    }
}

if (!function_exists("form_action_button_cancel")) {
    function form_action_button_cancel($var)
    {
        return  "<button type=\"button\" class=\"btn btn-sm btn-default\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" onclick=\"window.location='" . url($var["url"]) . "'\">" . $var["label"] . "</button>";
    }
}

if (!function_exists("form_button_dismiss_modal")) {
    function form_button_dismiss_modal($var)
    {
        return    "<button type=\"button\" class=\"btn btn-sm btn-default\" data-dismiss=\"modal\" >" . $var["label"] . "</button>";
    }
}

if (!function_exists("form_text")) {
    function form_text($var)
    {
        $label               = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class               = (empty($var["class"])) ? "" : $var["class"];
        $first                = (empty($var["first_selected"])) ? "" : "first-selected";
        $align               = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder     = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title               = (empty($var["title"])) ? "" : $var["title"];
        $size               = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly         = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction         = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory         = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add         = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value               = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css        = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj        = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj        = "";
            } else {
                $focus_obj        = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-9\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                    </div>
                </div>";
    }
}

if (!function_exists("form_email")) {
    function form_email($var)
    {
        $label               = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class               = (empty($var["class"])) ? "" : $var["class"];
        $first                = (empty($var["first_selected"])) ? "" : "first-selected";
        $align               = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder     = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title               = (empty($var["title"])) ? "" : $var["title"];
        $size               = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly         = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction         = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory         = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add         = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value               = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css        = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj        = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj        = "";
            } else {
                $focus_obj        = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-9\">
                        <input type=\"email\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                    </div>
                </div>";
    }
}

if (!function_exists("form_textarea")) {
    function form_textarea($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $rows             = (empty($var["rows"])) ? "rows='5'" : "rows=" . $var["rows"];
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                //   $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
                $focus_obj       = "";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                // $focus_obj       = " onKeypress=\"return focusObject(document.myform.".$var["focus_field"].", event)\"";
                $focus_obj       = "";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>            
                    <div class=\"col-md-9\">
                        <textarea class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . " " . $rows . ">" . $value . "</textarea>
                    </div>
                </div>";
    }
}

if (!function_exists("form_text_label")) {
    function form_text_label($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];
        $info           = (empty($var["info"])) ? "" : $var["info"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-9\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                        <label style=\"font-size: 10px; font-style: italic;\"><span style=\"color:#FF0000\">*</span>" . $info . "</label>
                    </div>
                </div>";
    }
}

if (!function_exists("form_textarea2")) {
    function form_textarea2($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $rows             = "rows='5'";
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        // return "<div class=\"form-group\">
        //             <label class=\"col-md-3 control-label\">".$label.$mandatory."</label>
        //             <div class=\"col-md-9\">
        //                 <input type=\"text\" class=\"form-control ".$class." ".$first." ".$class_add."\" placeholder=\"".$placeholder."\" id=\"".$var["name"]."\" name=\"".$var["name"]."\" value=\"".$value."\"".$jsaction." ".$readonly." ".$focus_obj."/>
        //             </div>
        //         </div>";

        return "<div class=\"form-group\">
                             
                    <div class=\"col-md-12\">
                        <textarea class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . " " . $rows . ">" . $value . "</textarea>
                    </div>
                </div>";
    }
}

if (!function_exists("form_date")) {
    function form_date($var)
    {
        $label        = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class        = (empty($var["class"])) ? "" : $var["class"];
        $first        = (empty($var["first_selected"])) ? "" : "first-selected";
        $align        = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder  = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title        = (empty($var["title"])) ? "" : $var["title"];
        $size         = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly     = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction     = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory    = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add    = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value        = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css    = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj   = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj   = "";
            } else {
                $focus_obj   = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj    = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-9\">
                        <input type=\"text\" class=\"form-control masked-input-date " . $class . " " . $first . " " . $class_add . "\" " . $readonly . " placeholder=\"dd/mm/yyyy\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"  />
                    </div>
                </div>";
    }
}

if (!function_exists("form_datepicker")) {
    function form_datepicker($var)
    {
        $label        = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class        = (empty($var["class"])) ? "" : $var["class"];
        $first        = (empty($var["first_selected"])) ? "" : "first-selected";
        $align        = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder  = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title        = (empty($var["title"])) ? "" : $var["title"];
        $size         = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly     = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction     = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory    = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add    = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value        = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css    = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj   = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj   = "";
            } else {
                $focus_obj   = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj    = "";
        }

        if ($readonly != "readonly") {
            return "<div class=\"form-group\">
                        <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                        <div class=\"col-md-9\">
                            <div class=\"input-group date datepicker-format\" id=\"datepicker-disabled-past\" data-date-format=\"dd/mm/yyyy\">
                                <input type=\"text\" class=\"form-control\" placeholder=\"Select Date\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\" />
                                <span class=\"input-group-addon\"><i class=\"fa fa-calendar\"></i></span>
                            </div>
                        </div>
                    </div>";
        } else {
            return "<div class=\"form-group\">
                        <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                        <div class=\"col-md-9\">
                            <input type=\"text\" class=\"form-control\" placeholder=\"Select Date\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\" readonly/>
                        </div>
                    </div>";
        }
    }
}

if (!function_exists("form_currency")) {
    function form_currency($var)
    {
        $label          = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class          = (empty($var["class"])) ? "" : $var["class"];
        $first          = (empty($var["first_selected"])) ? "" : "first-selected";
        $align          = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title          = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value          = ((old($var["name"]) != "") || (empty($var["value"]))) ? $var["value"] : $var["value"];
        $style_icon     = (empty($var["readonly"])) ? "" : "  style=\"background-color:#EEEEEE\"";
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj      = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj      = "";
            } else {
                $focus_obj      = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj      = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-9\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" placeholder=\"" . $placeholder . "\" value=\"" . $value . "\" title=\"" . $title . "\" " . $jsaction . " " . $readonly . " onBlur=\"this.value=formatCurrency(this.value);\" " . $focus_obj . ">
                    </div>
                </div>";
    }
}

if (!function_exists("form_currency_label")) {
    function form_currency_label($var)
    {
        $label          = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class          = (empty($var["class"])) ? "" : $var["class"];
        $first          = (empty($var["first_selected"])) ? "" : "first-selected";
        $align          = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title          = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value          = ((old($var["name"]) != "") || (empty($var["value"]))) ? $var["value"] : $var["value"];
        $style_icon     = (empty($var["readonly"])) ? "" : "  style=\"background-color:#EEEEEE\"";
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj      = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj      = "";
            } else {
                $focus_obj      = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj      = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . " <span style=\"color:#FF0000\">*</span>" . "</label>
                    <div class=\"col-md-9\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" placeholder=\"" . $placeholder . "\" value=\"" . $value . "\" title=\"" . $title . "\" " . $jsaction . " " . $readonly . " onBlur=\"this.value=formatCurrency(this.value);\" " . $focus_obj . ">
                    </div>
                </div>";
    }
}

if (!function_exists("form_number")) {
    function form_number($var)
    {
        $label          = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class          = (empty($var["class"])) ? "" : $var["class"];
        $first          = (empty($var["first_selected"])) ? "" : "first-selected";
        $align          = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title          = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value          = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $style_icon     = (empty($var["readonly"])) ? "" : "  style=\"background-color:#EEEEEE\"";
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj      = "'enter'";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj      = "no";
            } else {
                $focus_obj      = "document.myform." . $var["focus_field"];
            }
        } else {
            $focus_obj      = "no";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-9\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" placeholder=\"" . $placeholder . "\" value=\"" . $value . "\" title=\"" . $title . "\" " . $jsaction . " " . $readonly . " onkeypress=\"return isNumberKey(this, event, $focus_obj);\">
                    </div>
                </div>";
    }
}

if (!function_exists("form_password")) {
    function form_password($var)
    {
        $label               = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class               = (empty($var["class"])) ? "" : $var["class"];
        $first                = (empty($var["first_selected"])) ? "" : "first-selected";
        $align               = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder     = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title               = (empty($var["title"])) ? "" : $var["title"];
        $size               = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly         = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction         = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory         = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add         = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value               = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css        = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj        = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj        = "";
            } else {
                $focus_obj        = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-9\">
                        <input type=\"password\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                    </div>
                </div>";
    }
}

if (!function_exists("form_header_text")) {
    function form_header_text($var)
    {
        $label               = (empty($var["label"])) ? $var["name"] : $var["label"];


        $object     = "<div class=\"form-group\">
                            <div class=\"col-md-12\" style=\"background: #242a30;\">
                                <h4 style=\"color: white; font-size: 12px\">" . $label . "</h4>
                            </div>
                        </div>";

        return $object;
    }
}

if (!function_exists("form_select_disable")) {
    function form_select_disable($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtoupper($label)) : ucwords(strtoupper($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : "disabled='true'";
        $data_style     = (empty($var["readonly"])) ? "btn-white" : "";
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $withnull       = (empty($var["withnull"])) ? "" : $var["withnull"];

        if (!empty($var["value"])) {
            $value      = (old($var["name"]) != "") ? old($var["name"]) : $var["value"];
        } else {
            $value      = "";
        }

        $object     = "<div class=\"form-group\">
                                        <label class=\"control-label col-md-3\">" . $label . $mandatory . "</label>
                                        <div class=\"col-md-9\">
                                        <select class=\"form-control selectpicker input-sm $class\" data-size=\"5\" data-live-search=\"true\" data-style=\"" . $data_style . "\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" " . $readonly . ">";
        if (!empty($var["withnull"])) {
            $object     .= "<label>";
            $object     .= "<option value=\"0\" selected=\"selected\">-Pilih-</option>";
            $object     .= "</label>";
        }

        foreach ($var["source"] as $rows) {
            $rows = (object)$rows;

            if ($rows->id) {
                $selected   = ($rows->id == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows->id . "\" $selected>" . $rows->name . "</option>";
                $object     .= "</label>";
            } else {
                $selected   = ($rows["id"] == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows["id"] . "\" $selected>" . $rows["name"] . "</option>";
                $object     .= "</label>";
            }
        }

        $object     .= "</select>
                                        </div>
                            </div>";

        return $object;
    }
}

if (!function_exists("form_select")) {
    function form_select($var)
    {
        // $label 			  = (empty($var["label"])) ? $var["name"] : $var["label"];
        $label          = (empty($var["label"])) ? $var["name"] : ucwords(strtolower($var["label"]));
        $class               = (empty($var["class"])) ? "" : $var["class"];
        $align               = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder     = (empty($var["placeholder"])) ? ucwords(strtoupper($label)) : ucwords(strtoupper($var["placeholder"]));
        $title             = (empty($var["title"])) ? "" : $var["title"];
        $readonly         = (empty($var["readonly"])) ? "" : "disabled='true'";
        $data_style     = (empty($var["readonly"])) ? "btn-white" : "";
        $jsaction         = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory         = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add         = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $withnull         = (empty($var["withnull"])) ? "" : $var["withnull"];

        if (!empty($var["value"])) {
            $value         = (old($var["name"]) != "") ? old($var["name"]) : $var["value"];
        } else {
            $value         = "";
        }

        $object     = "<div class=\"form-group\">
      									<label class=\"control-label col-md-3\">" . $label . $mandatory . "</label>
      									<div class=\"col-md-9\">
      									    <select class=\"form-control selectpicker input-sm $class\" data-size=\"5\" data-live-search=\"true\" data-style=\"" . $data_style . "\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" " . $readonly . ">";

        if (!empty($var["withnull"])) {
            $object     .= "<label>";
            $object     .= "<option value=\"0\" selected=\"selected\">-Pilih-</option>";
            $object     .= "</label>";
        }

        foreach ($var["source"] as $rows) {
            $rows = (object)$rows;

            if ($rows->id) {
                $selected     = ($rows->id == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows->id . "\" $selected>" . $rows->name . "</option>";
                $object     .= "</label>";
            } else {
                $selected     = ($rows["id"] == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows["id"] . "\" $selected>" . $rows["name"] . "</option>";
                $object     .= "</label>";
            }
        }

        $object     .= "</select>
      									</div>
            				</div>";

        return $object;
    }
}

if (!function_exists("form_upload")) {
    function form_upload($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-9\">
                        <input type=\"file\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . " style=\"margin-bottom: 5px;\"/>
                    </div>
                </div>";
    }
}

if (!function_exists("form_upload_foto")) {
    function form_upload_foto($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . " <span style=\"color:#FF0000\">*</span>" . "</label>
                    <div class=\"col-md-9\">
                        <input type=\"file\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                    </div>
                </div>";
    }
}

if (!function_exists("form_checklist")) {
    function form_checklist($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $checked           = (($var["checked"] == "1")) ? "checked" : "";
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-1\">
                        <input type=\"checkbox\" class=\"form-control " . $class . " " . $first . " " . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . " " . $focus_obj . " " . $checked . "/>
                    </div>
                     <div class=\"col-md-1\" style=\"align:'left'';\" ><label>Approved</label></div> 
                </div>";
    }
}

if (!function_exists("form_popup")) {
    function form_popup($var)
    {
        list($height, $width) = explode("|", $var["size"]);
        # ----------------- 
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];
        $href           = url("/") . "/" . $var["href"];
        $disabled       = (empty($var["disabled"])) ? "" : $var["disabled"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        if ($disabled != "disabled") {
            return "<div class=\"form-group\">
                            <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                            <div class=\"col-md-9\">
                                <div class=\"input-group\">
                                    <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                                    <span class=\"input-group-addon\">
                                        <span><a href=\"javascript:void(0)\" onClick=\"window.open('$href','targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,width=$width,height=$height')\">...</a></span>
                                    </span>
                                </div>
                            </div>
                        </div>";
        } else {
            return "<div class=\"form-group\">
                            <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                            <div class=\"col-md-9\">
                                <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                            </div>
                        </div>";
        }
    }
}

if (!function_exists("form_link")) {
    function form_link($var)
    {
        $label          = (empty($var["label"])) ? $var["name"] : $var["label"];
        $url            = url("/uploads/") . $var["url"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";

        if (empty($var["url"])) {
            return "<div class=\"form-group\">
                        <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                        <div class=\"col-md-9\">
                                  <span class=\"btn btn-sm btn-danger\">Tidak Ada</span>
                        </div>
                    </div>";
        } else {
            return "<div class=\"form-group\">
                        <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                        <div class=\"col-md-9\">
                            <span class=\"btn btn-sm btn-warning m-r-5\" onClick=\"window.open('$url', '_blank')\">" . $label . "</span>
                        </div>
                    </div>";
        }
    }
}

if (!function_exists("form_hidden")) {
    function form_hidden($var)
    {
        $label               = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class               = (empty($var["class"])) ? "" : $var["class"];
        $align               = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder     = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $value               = (empty($var["value"])) ? "" : $var["value"];
        $title               = (empty($var["title"])) ? "" : $var["title"];
        $readonly         = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction         = (empty($var["jsaction"])) ? "" : $var["jsaction"];

        return     "<input type=\"hidden\" class=\"form-control " . $class . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\">";
    }
}

if (!function_exists("form_radio")) {
    function form_radio($var)
    {
        $label               = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class               = (empty($var["class"])) ? "form-control" : $var["class"];
        $align               = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder     = (empty($var["placeholder"])) ? ucwords(strtoupper($label)) : ucwords(strtoupper($var["placeholder"]));
        $title               = (empty($var["title"])) ? "" : $var["title"];
        $readonly         = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction         = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory         = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $value               = (old($var["name"]) != "") ? old($var["name"]) : $var["value"];

        $object     = "<div class=\"form-group\">
  		              		<label for=\"" . $var["name"] . "\" class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
  		          				<div class=\"col-md-9\">";
        $i = 0;

        foreach ($var["source"] as $rows) {
            $rows = (object)$rows;

            if ($rows->id) {
                $checked   = ($rows->id == $value) ? "checked" : "";

                $object     .= "<label class=\"radio-inline\">
                                                               <input type=\"radio\" style=\"margin-top:0px;\" class=\"styled\" name=\"" . $var["name"] . "\" id=\"" . $rows->name . "_$i\" value=\"" . $rows->id . "\" $checked>" . $rows->name . "&nbsp;&nbsp;&nbsp;
                                                        </label>";

                // $object     .= "<label>";
                // $object     .= "<option value=\"".$rows->id."\" $selected>".$rows->name."</option>";
                // $object     .= "</label>";

            } else {
                $checked   = ($rows->id == $value) ? "checked" : "";

                $object     .= "<label class=\"radio-inline\">
                                                               <input type=\"radio\" style=\"margin-top:0px;\" class=\"styled\" name=\"" . $var["name"] . "\" id=\"" . $rows->name . "_$i\" value=\"" . $rows->id . "\" $checked>" . $rows->name . "&nbsp;&nbsp;&nbsp;
                                                        </label>";
                // $checked   = ($rows["id"] == $value) ? "checked" : "";

                // $object     .= "<label class=\"radio-inline\">
                //                        <input type=\"radio\" style=\"margin-top:0px;\" class=\"styled\" name=\"".$var["name"]."\" id=\"".$rows["name"]."_$i\" value=\"".$rows["id"]."\" $checked>".$rows["name"]."&nbsp;&nbsp;&nbsp;
                //                 </label>";
            }





            //        		$checked 	= ($rows["id"] == $value) ? "checked" : "";
            //        		$object 	.= "<label class=\"radio-inline\">
            //              							   <input type=\"radio\" style=\"margin-top:0px;\" class=\"styled\" name=\"".$var["name"]."\" id=\"".$var["name"]."_$i\" value=\"".$rows["id"]."\" $checked>".$rows["name"]."&nbsp;&nbsp;&nbsp;
            //              						</label>";

            // $i++;
        }

        $object     .= "</div>
  		             	</div>";

        return $object;
    }
}

if (!function_exists("formsmall_text")) {
    function formsmall_text($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder      = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly         = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction         = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory        = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add        = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css        = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group col-md-4\">
                    <label>" . $label . "</label>" . $mandatory . "
                    <input type=\"text\" class=\"form-control " . $first . " " . $class_add . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\" placeholder=\"" . $placeholder . "\" " . $readonly . " " . $focus_obj . " />
                </div>";
    }
}

if (!function_exists("formsmall_textarea")) {
    function formsmall_textarea($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder      = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly         = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction         = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory        = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add        = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css        = "style_form_input_" . $var["name"];
        $focus_obj        = "";
        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                //$focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                //$focus_obj       = " onKeypress=\"return focusObject(document.myform.".$var["focus_field"].", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group col-md-4\">
                    <label>" . $label . "</label>" . $mandatory . "
                    <textarea class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . ">" . $value . "</textarea>
                    
                </div>";
    }
}

if (!function_exists("formsmall_currency")) {
    function formsmall_currency($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder      = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly         = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction         = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory        = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add        = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css        = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group col-md-4\">
                    <label>" . $label . "</label>" . $mandatory . "
                    <input type=\"text\" class=\"form-control " . $first . " " . $class_add . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\" placeholder=\"" . $placeholder . "\" " . $readonly . " " . $focus_obj . "  onBlur=\"this.value=formatCurrency(this.value);\" />
                </div>";
    }
}

if (!function_exists("formsmall_select")) {
    function formsmall_select($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtoupper($label)) : ucwords(strtoupper($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $withnull       = (empty($var["withnull"])) ? "" : $var["withnull"];

        if (!empty($var["value"])) {
            $value      = (old($var["name"]) != "") ? old($var["name"]) : $var["value"];
        } else {
            $value      = "";
        }


        $object     = "<div class=\"form-group col-md-4\">
                            <label>" . $label . "</label>" . $mandatory . "
                                <select class=\"form-control selectpicker input-sm\" data-size=\"5\" data-live-search=\"true\" data-style=\"btn-white\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\">";
        if (!empty($var["withnull"])) {
            $object     .= "<label>";
            $object     .= "<option value=\"0\" selected=\"selected\">-Pilih-</option>";
            $object     .= "</label>";
        }

        foreach ($var["source"] as $rows) {
            $rows = (object)$rows;

            if ($rows->id) {
                $selected   = ($rows->id == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows->id . "\" $selected>" . $rows->name . "</option>";
                $object     .= "</label>";
            } else {
                $selected   = ($rows["id"] == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows["id"] . "\" $selected>" . $rows["name"] . "</option>";
                $object     .= "</label>";
            }
        }

        $object     .= "</select>
                        </div>";

        return $object;
    }
}

if (!function_exists("formsmall_empty")) {
    function formsmall_empty()
    {
        return "<div class=\"form-group col-md-4\">
                    <label>&nbsp;</label>
                    <input type=\"hidden\" />
                </div>";
    }
}

if (!function_exists("formsmall_upload")) {
    function formsmall_upload($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group col-md-4\">
                    <label>" . $label . $mandatory . "</label>
                        <input type=\"file\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                </div>";
    }
}

if (!function_exists("getActionButton")) {
    function getActionButton($name, $url, $var)
    {
        $var     = explode("|", $var);

        if ($var[0] == "direct") {
            $action     = array("btn-none-direct", $var[1]);
            # ---------------
            return  "<a href=\"" . URL::to('/') . $url . "\" class=\"btn btn-sm btn-primary m-r-5 m-b-5 $action[0]\" title=\"" . URL::to('/') . $url . "\"><i class=\"$action[1]\"></i>&nbsp;&nbsp; $name</a>";
        } elseif ($var[0] == "single") {
            $action     = array("btn-single-direct", $var[1]);
            $urls       = URL::to('/') . $url;
            # ---------------
            return  "<a href=\"javascript:;\" class=\"btn btn-sm btn-success m-r-5 m-b-5 $action[0]\" title=\"" . URL::to('/') . $url . "\" onmousedown=\"rightclick('$urls');\"><i class=\"$action[1]\"></i>&nbsp;&nbsp; $name</a>";
        } elseif ($var[0] == "popup") {
            $action     = array("btn-single-popup", $var[1]);
            # ---------------
            return  "<a href=\"javascript:;\" class=\"btn btn-sm btn-success m-r-5  m-b-5 $action[0]\" title=\"" . URL::to('/') . $url . "\"><i class=\"$action[1]\"></i>&nbsp;&nbsp; $name</a>";
        } elseif ($var[0] == "direct-popup") {
            $action     = array("btn-direct-popup", $var[1]);
            # ---------------
            return  "<a href=\"javascript:;\" class=\"btn btn-sm btn-primary m-r-5  m-b-5 $action[0]\" title=\"" . URL::to('/') . $url . "\"><i class=\"$action[1]\"></i>&nbsp;&nbsp; $name</a>";
        } elseif ($var[0] == "modal") {
            $action     = array("btn-modal", $var[1]);
            # ---------------
            return "<button type=\"button\" class=\"btn btn-sm btn-primary m-r-5 m-b-5\" data-toggle=\"modal\" data-target=\"#formModal\"><i class=\"$action[1]\"></i>&nbsp;&nbsp; $name</button>";
            //return  "<a href=\"javascript:;\" class=\"btn btn-sm btn-success m-r-5 $action[0]\" title=\"".URL::to('/').$url."\" data-toggle=\"modal\" data-target=\"#formModal\"><i class=\"$action[1]\"></i>&nbsp;&nbsp; $name</a>";
        } elseif ($var[0] == "single-modal") {
            $action     = array("btn-single-modal", $var[1]);
            # ---------------
            return "<button type=\"button\" class=\"btn btn-sm btn-success m-r-5 m-b-5\" data-toggle=\"modal\" data-target=\"#formModal\"><i class=\"$action[1]\"></i>&nbsp;&nbsp; $name</button>";
            //return  "<a href=\"javascript:;\" class=\"btn btn-sm btn-success m-r-5 $action[0]\" title=\"".URL::to('/').$url."\" data-toggle=\"modal\" data-target=\"#formModal\"><i class=\"$action[1]\"></i>&nbsp;&nbsp; $name</a>";
        } elseif ($var[0] == "excel") {
            $action     = array("btn-excel", $var[1]);
            # ---------------
            return  "<a href=\"javascript:;\" target=\"_blank\" class=\"btn btn-sm btn-primary m-r-5 m-b-5 $action[0]\" title=\"" . URL::to('/') . $url . "\"><i class=\"$action[1]\"></i>&nbsp;&nbsp; $name</a>";
        } else {
        }
    }
}

if (!function_exists("getPagging")) {
    function getPagging($data)
    {
        if(!empty($data)) {
            return     "<ul class=\"pagination pull-right\" style=\"margin-top:-5px;\">
                        <li class=\"paginate_button \"><a href=\"" . $data->url(1) . "\">&laquo;</a></li>
                        <li class=\"paginate_button \"><a href=\"" . $data->previousPageUrl() . "\">&lsaquo;</a></li>
                        <li class=\"paginate_button \"><a href=\"javascript:void(0)\">Page " . $data->currentPage() . " of " . $data->lastPage() . "</a></li>
                        <li class=\"paginate_button \"><a href=\"" . $data->nextPageUrl() . "\">&rsaquo;</a></li>
                        <li class=\"paginate_button \"><a href=\"" . $data->url($data->lastPage()) . "\">&raquo;</a></li>
                     </ul>";
        } else {
            return null;
        }
    }
}

if (!function_exists("getPaggingCustom")) {
    function getPaggingCustom($active_page, $record, $url, $rows)
    {
        $total_page = ceil($record / $rows);
        $pref = (($active_page - 1) == 0) ? 1 : $active_page - 1;
        $next = $active_page + 1;
        # ----------------
        if ($active_page == $total_page) {
            $next   = $total_page;
        } elseif ($active_page <= 1) {
            $pref   = 1;
        } else {
            $next   = $active_page + 1;
        }
        # ----------------
        return  "<ul class=\"pagination pull-right\" style=\"margin-top:-5px;\">
                        <li class=\"paginate_button \"><a href=\"" . URL::to('/') . $url . "\">&laquo;</a></li>
                        <li class=\"paginate_button \"><a href=\"" . URL::to('/') . $url . "/" . $pref . "\">&lsaquo;</a></li>
                        <li class=\"paginate_button \"><a href=\"javascript:void(0)\">Page " . $active_page . " of " . $total_page . "</a></li>
                        <li class=\"paginate_button \"><a href=\"" . URL::to('/') . $url . "/" . $next . "\">&rsaquo;</a></li>
                        <li class=\"paginate_button \"><a href=\"" . URL::to('/') . $url . "/" . $total_page . "\">&raquo;</a></li>
                     </ul>";
    }
}

if (!function_exists("getSimplePagging")) {
    function getSimplePagging($active_page, $record, $url, $perpage)
    {
        $total_page = ceil($record / $perpage);
        $pref = (($active_page - 1) == 0) ? 1 : $active_page - 1;
        $next = $active_page + 1;
        # ----------------
        if ($active_page == $total_page) {
            $next   = $total_page;
        } elseif ($active_page <= 1) {
            $pref   = 1;
        } else {
            $next   = $active_page + 1;
        }
        # ----------------
        return  "<span class=\"paginate_button \"><a href=\"" . URL::to('/') . $url . "\">&laquo;</a></span>
                    <span class=\"paginate_button \"><a href=\"" . URL::to('/') . $url . "/" . $pref . "\">&lsaquo;</a></span>
                    <span class=\"paginate_button \"><a href=\"javascript:void(0)\">Page " . $active_page . " of " . $total_page . "</a></span>
                    <span class=\"paginate_button \"><a href=\"" . URL::to('/') . $url . "/" . $next . "\">&rsaquo;</a></span>
                    <span class=\"paginate_button \"><a href=\"" . URL::to('/') . $url . "/" . $total_page . "\">&raquo;</a></span>";
    }
}

if (!function_exists("form_checklist2")) {
    function form_checklist2($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $checked           = (($var["checked"] == "2")) ? "checked" : "";
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $disabled       = (empty($var["disabled"])) ? "" : "disabled=" . $var["disabled"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-1\">
                        <input type=\"checkbox\" class=\"form-control " . $class . " " . $first . " " . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $disabled . " " . $focus_obj . " " . $focus_obj . " " . $checked . "/>
                    </div>
                     <div class=\"col-md-1\" style=\"align:'left'';\" ><label>Approved</label></div> 
                </div>";
    }
}

if (!function_exists("form_upload")) {
    function form_upload($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $value            = (empty($var["value"])) ? "" : $var["value"];
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];

        return  "<input type=\"file\" name=\"file\" id=\"file\" class=\"inputfile\" />";
        return  "<input type=\"hidden\" class=\"form-control " . $class . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\">";
    }
}

if (!function_exists("form_simpleselect")) {
    function form_simpleselect($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtoupper($label)) : ucwords(strtoupper($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $withnull       = (empty($var["withnull"])) ? "" : $var["withnull"];

        if (!empty($var["value"])) {
            $value      = (old($var["name"]) != "") ? old($var["name"]) : $var["value"];
        } else {
            $value      = "";
        }

        $object     = "<select class=\"form-control selectpicker input-sm\" data-size=\"5\" data-live-search=\"true\" data-style=\"btn-white\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\">";
        if (!empty($var["withnull"])) {
            $object     .= "<label>";
            $object     .= "<option value=\"0\" selected=\"selected\">-Pilih-</option>";
            $object     .= "</label>";
        }

        foreach ($var["source"] as $rows) {
            $rows = (object)$rows;

            if ($rows->id) {
                $selected   = ($rows->id == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows->id . "\" $selected>" . $rows->name . "</option>";
                $object     .= "</label>";
            } else {
                $selected   = ($rows["id"] == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows["id"] . "\" $selected>" . $rows["name"] . "</option>";
                $object     .= "</label>";
            }
        }

        $object     .= "</select>";

        return $object;
    }
}

if (!function_exists("form_search_text")) {
    function form_search_text($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];


        return "<div class=\"form-group m-r-10\">
                <input type=\"text\" class=\"form-control\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" placeholder=\"" . $placeholder . "\" value=\"" . $value . "\" />
            </div>";
    }
}

if (!function_exists("form_search_select")) {
    function form_search_select($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $withnull         = (empty($var["withnull"])) ? "" : $var["withnull"];

        if (!empty($var["value"])) {
            $value      = (old($var["name"]) != "") ? old($var["name"]) : $var["value"];
        } else {
            $value      = "";
        }

        $object     = "<div class=\"form-group m-r-10\">
                                            <select class=\"form-control selectpicker input-sm\" data-size=\"5\" data-live-search=\"true\" data-style=\"btn-white\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\">";
        if (!empty($var["withnull"])) {
            $object     .= "<label>";
            $object     .= "<option value=\"0\" selected=\"selected\">- $label -</option>";
            $object     .= "</label>";
        }

        foreach ($var["source"] as $rows) {
            $rows = (object)$rows;

            if ($rows->id) {
                $selected   = ($rows->id == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows->id . "\" $selected>" . $rows->name . "</option>";
                $object     .= "</label>";
            } else {
                $selected   = ($rows["id"] == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows["id"] . "\" $selected>" . $rows["name"] . "</option>";
                $object     .= "</label>";
            }
        }

        $object     .= "</select>
                            </div>";

        return $object;
    }
}

if (!function_exists("form_search_datepicker")) {
    function form_search_datepicker($var)
    {
        $label        = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class        = (empty($var["class"])) ? "" : $var["class"];
        $first        = (empty($var["first_selected"])) ? "" : "first-selected";
        $align        = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder  = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title        = (empty($var["title"])) ? "" : $var["title"];
        $size         = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly     = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction     = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory    = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add    = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value        = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css    = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj   = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj   = "";
            } else {
                $focus_obj   = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj    = "";
        }

        return "<div class=\"form-group m-r-10\">
                    <div class=\"input-group date datepicker-format\" id=\"datepicker-disabled-past\" data-date-format=\"dd/mm/yyyy\">
                        <input type=\"text\" class=\"form-control\" placeholder=\"$placeholder\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"  />
                        <span class=\"input-group-addon\"><i class=\"fa fa-calendar\"></i></span>
                    </div>
                </div>";
    }
}

if (!function_exists("form_hr")) {
    function form_hr()
    {
        return "<div>
                    <hr>
                </div>";
    }
}

if (!function_exists("form_file")) {
    function form_file($var)
    {
        $label = (empty($var["label"])) ? "" : $var["label"];
        if (empty($var["value"])) {
            return "<div class=\"form-group\">
                            <label class=\"col-md-3 control-label\">$label</label>
                            <div class=\"col-md-9\">
                                      <span class=\"btn btn-sm btn-danger\">No Attachment Found</span>
                            </div>
                        </div>";
        } else {
            $vurl = $var["value"];
            if (empty($var["valdelete"])) {
                return "<div class=\"form-group\">
                                <label class=\"col-md-3 control-label\">$label</label>
                                <div class=\"col-md-9\">
                                          <a href=\" $vurl \" target=\"_blank\" target=\"_blank\" class=\"btn btn-sm btn-warning m-r-5\">View Attachment</a>
                                </div>
                            </div>";
            } else {
                $vurlDelete = $var["valdelete"];
                $pathImage  = url('') . "/app/img/icon/delete.png";
                return "<div class=\"form-group\">
                                <label class=\"col-md-3 control-label\">$label</label>
                                <div class=\"col-md-9\">
                                          <a href=\" $vurl \" target=\"_blank\" target=\"_blank\" class=\"btn btn-sm btn-warning m-r-5\">View Attachment</a>
                                          <a href=\" $vurlDelete \"><img src=\" $pathImage  \" width=\"15px\" /></a>
                                </div>
                            </div>";
            }
        }
    }
}

if (!function_exists("form_select_text")) {
    function form_select_text($var)
    {
        $label          = (empty($var["label"])) ? $var["name"] : ucwords(strtolower($var["label"]));
        $class          = (empty($var["class"])) ? "" : $var["class"];
        $align          = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtoupper($label)) : ucwords(strtoupper($var["placeholder"]));
        $title          = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : "disabled='true'";
        $data_style     = (empty($var["readonly"])) ? "btn-white" : "";
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $withnull       = (empty($var["withnull"])) ? "" : $var["withnull"];

        $placeholdertext = (empty($var["placeholder"])) ? ucwords(strtolower($var["labeltext"])) : ucwords(strtolower($var["placeholder"]));
        $nametext        = $var["nametext"];
        $valuetext       = ((old($var["nametext"]) != "") || (empty($var["valuetext"]))) ? old($var["nametext"]) : $var["valuetext"];
        $jsactiontext    = (empty($var["jsactiontext"])) ? "" : $var["jsactiontext"];
        $readonlytext    = (empty($var["readonlytext"])) ? "" : $var["readonlytext"];

        if (!empty($var["value"])) {
            $value      = (old($var["name"]) != "") ? old($var["name"]) : $var["value"];
        } else {
            $value      = "";
        }

        $object     = "<div class=\"form-group\">
                                        <label class=\"control-label col-md-4\">" . $label . $mandatory . "</label>
                                        <div class=\"col-md-3\">
                                            <select class=\"form-control selectpicker input-sm $class\" data-size=\"5\" data-live-search=\"true\" data-style=\"" . $data_style . "\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" " . $readonly . ">";

        if (!empty($var["withnull"])) {
            $object     .= "<label>";
            $object     .= "<option value=\"0\" selected=\"selected\">-Pilih-</option>";
            $object     .= "</label>";
        }

        foreach ($var["source"] as $rows) {
            $rows = (object)$rows;

            if ($rows->id) {
                $selected   = ($rows->id == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows->id . "\" $selected>" . $rows->name . "</option>";
                $object     .= "</label>";
            } else {
                $selected   = ($rows["id"] == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows["id"] . "\" $selected>" . $rows["name"] . "</option>";
                $object     .= "</label>";
            }
        }

        $object     .= "</select>
                                        </div>
                                        <div class=\"col-md-5\">
                                            <input type=\"text\" class=\"form-control \" placeholder=\"" . $placeholdertext . "\" id=\"" . $nametext . "\" name=\"" . $nametext . "\" value=\"" . $valuetext . "\"" . $jsactiontext . " " . $readonlytext . "/>
                                        </div>
                            </div>";

        return $object;
    }
}

if (!function_exists("form_select2")) {
    function form_select2($var)
    {
        // $label             = (empty($var["label"])) ? $var["name"] : $var["label"];
        $label          = (empty($var["label"])) ? $var["name"] : ucwords(strtolower($var["label"]));
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtoupper($label)) : ucwords(strtoupper($var["placeholder"]));
        $title          = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : "disabled='true'";
        $data_style     = (empty($var["readonly"])) ? "btn-white" : "";
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $withnull       = (empty($var["withnull"])) ? "" : $var["withnull"];

        if (!empty($var["value"])) {
            $value      = (old($var["name"]) != "") ? old($var["name"]) : $var["value"];
        } else {
            $value      = "";
        }

        $object     = "<div class=\"form-group\">
                                        <label class=\"control-label col-md-4\">" . $label . $mandatory . "</label>
                                        <div class=\"col-md-8\">
                                            <select class=\"form-control selectpicker input-sm $class\" data-size=\"5\" data-live-search=\"true\" data-style=\"" . $data_style . "\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" " . $readonly . ">";

        if (!empty($var["withnull"])) {
            $object     .= "<label>";
            $object     .= "<option value=\"0\" selected=\"selected\">-Pilih-</option>";
            $object     .= "</label>";
        }

        foreach ($var["source"] as $rows) {
            $rows = (object)$rows;

            if ($rows->id) {
                $selected   = ($rows->id == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows->id . "\" $selected>" . $rows->name . "</option>";
                $object     .= "</label>";
            } else {
                $selected   = ($rows["id"] == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows["id"] . "\" $selected>" . $rows["name"] . "</option>";
                $object     .= "</label>";
            }
        }

        $object     .= "</select>
                                        </div>
                            </div>";

        return $object;
    }
}

if (!function_exists("form_currency_select")) {
    function form_currency_select($var)
    {
        $label          = (empty($var["label"])) ? $var["name"] : ucwords(strtolower($var["label"]));
        $class          = (empty($var["class"])) ? "" : $var["class"];
        $align          = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtoupper($label)) : ucwords(strtoupper($var["placeholder"]));
        $title          = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : "disabled='true'";
        $data_style     = (empty($var["readonly"])) ? "btn-white" : "";
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $withnull       = (empty($var["withnull"])) ? "" : $var["withnull"];

        $placeholdertext = (empty($var["placeholder"])) ? ucwords(strtolower($var["label"])) : ucwords(strtolower($var["placeholder"]));
        $nametext        = $var["nametext"];
        $valuetext       = $var["valuetext"];
        $jsactiontext    = (empty($var["jsactiontext"])) ? "" : $var["jsactiontext"];
        $readonlytext    = (empty($var["readonlytext"])) ? "" : $var["readonlytext"];

        if (!empty($var["value"])) {
            $value      = (old($var["name"]) != "") ? old($var["name"]) : $var["value"];
        } else {
            $value      = "";
        }

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj      = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj      = "";
            } else {
                $focus_obj      = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj      = "";
        }



        $object     = "<div class=\"form-group\">
                                        <label class=\"control-label col-md-3\">" . $label . $mandatory . "</label>
                                        <div class=\"col-md-5\">
                                            <input type=\"text\" class=\"form-control \" placeholder=\"" . $placeholdertext . "\" id=\"" . $nametext . "\" name=\"" . $nametext . "\" value=\"" . $valuetext . "\"" . $jsactiontext . " " . $readonlytext . " onBlur=\"this.value=formatCurrency(this.value);\" " . $focus_obj . "/>
                                        </div>
                                        <div class=\"col-md-4\">
                                            <select class=\"form-control selectpicker input-sm $class\" data-size=\"5\" data-live-search=\"true\" data-style=\"" . $data_style . "\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" " . $readonly . ">";

        if (!empty($var["withnull"])) {
            $object     .= "<label>";
            $object     .= "<option value=\"0\" selected=\"selected\">-Pilih-</option>";
            $object     .= "</label>";
        }

        foreach ($var["source"] as $rows) {
            $rows = (object)$rows;

            if ($rows->id) {
                $selected   = ($rows->id == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows->id . "\" $selected>" . $rows->name . "</option>";
                $object     .= "</label>";
            } else {
                $selected   = ($rows["id"] == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows["id"] . "\" $selected>" . $rows["name"] . "</option>";
                $object     .= "</label>";
            }
        }

        $object     .= "</select>
                                        </div>
                            </div>";

        return $object;
    }
}

if (!function_exists("form_number_select")) {
    function form_number_select($var)
    {
        $label          = (empty($var["label"])) ? $var["name"] : ucwords(strtolower($var["label"]));
        $class          = (empty($var["class"])) ? "" : $var["class"];
        $align          = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtoupper($label)) : ucwords(strtoupper($var["placeholder"]));
        $title          = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : "disabled='true'";
        $data_style     = (empty($var["readonly"])) ? "btn-white" : "";
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $withnull       = (empty($var["withnull"])) ? "" : $var["withnull"];

        $placeholdertext = (empty($var["placeholder"])) ? ucwords(strtolower($var["label"])) : ucwords(strtolower($var["placeholder"]));
        $nametext        = $var["nametext"];
        $valuetext       = ((old($var["nametext"]) != "") || (empty($var["valuetext"]))) ? old($var["nametext"]) : $var["valuetext"];
        $jsactiontext    = (empty($var["jsactiontext"])) ? "" : $var["jsactiontext"];
        $readonlytext    = (empty($var["readonlytext"])) ? "" : $var["readonlytext"];

        if (!empty($var["value"])) {
            $value      = (old($var["name"]) != "") ? old($var["name"]) : $var["value"];
        } else {
            $value      = "";
        }

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj      = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj      = "";
            } else {
                $focus_obj      = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj      = "";
        }

        $object     = "<div class=\"form-group\">
                                        <label class=\"control-label col-md-3\">" . $label . $mandatory . "</label>
                                        <div class=\"col-md-5\">
                                            <input type=\"text\" class=\"form-control \" placeholder=\"" . $placeholdertext . "\" id=\"" . $nametext . "\" name=\"" . $nametext . "\" value=\"" . $valuetext . "\"" . $jsactiontext . " " . $readonlytext . " onkeypress=\"return isNumberKey(this, event, $focus_obj);\" " . $focus_obj . "/>
                                        </div>
                                        <div class=\"col-md-4\">
                                            <select class=\"form-control selectpicker input-sm $class\" data-size=\"5\" data-live-search=\"true\" data-style=\"" . $data_style . "\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" " . $readonly . ">";

        if (!empty($var["withnull"])) {
            $object     .= "<label>";
            $object     .= "<option value=\"0\" selected=\"selected\">-Pilih-</option>";
            $object     .= "</label>";
        }

        foreach ($var["source"] as $rows) {
            $rows = (object)$rows;

            if ($rows->id) {
                $selected   = ($rows->id == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows->id . "\" $selected>" . $rows->name . "</option>";
                $object     .= "</label>";
            } else {
                $selected   = ($rows["id"] == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows["id"] . "\" $selected>" . $rows["name"] . "</option>";
                $object     .= "</label>";
            }
        }

        $object     .= "</select>
                                        </div>
                            </div>";

        return $object;
    }
}

if (!function_exists("form_currency_number")) {
    function form_currency_number($var)
    {
        $label          = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class          = (empty($var["class"])) ? "" : $var["class"];
        $first          = (empty($var["first_selected"])) ? "" : "first-selected";
        $align          = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title          = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value          = ((old($var["name"]) != "") || (empty($var["value"]))) ? $var["value"] : $var["value"];
        $style_icon     = (empty($var["readonly"])) ? "" : "  style=\"background-color:#EEEEEE\"";
        $group_css      = "style_form_input_" . $var["name"];

        $labelnumber          = (empty($var["labelnumber"])) ? $var["namenumber"] : $var["labelnumber"];
        $placeholdernumber    = (empty($var["placeholdernumber"])) ? ucwords(strtolower($labelnumber)) : ucwords(strtolower($var["placeholdernumber"]));
        $titlenumber          = (empty($var["titlenumber"])) ? "" : $var["titlenumber"];
        $valuenumber          = ((old($var["namenumber"]) != "") || (empty($var["valuenumbernumber"]))) ? $var["valuenumber"] : $var["valuenumber"];
        $class_add_number     = (empty($var["mandatorynumber"])) ? "" : " mandatory-input";
        $readonlynumber       = (empty($var["readonlynumber"])) ? "" : $var["readonlynumber"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj      = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj      = "";
            } else {
                $focus_obj      = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj      = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-3\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" placeholder=\"" . $placeholder . "\" value=\"" . $value . "\" title=\"" . $title . "\" " . $jsaction . " " . $readonly . " onBlur=\"this.value=formatCurrency(this.value);\" " . $focus_obj . ">
                    </div>
                    <label class=\"col-md-2 control-label\">" . $labelnumber . "</label>
                    <div class=\"col-md-4\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add_number . "\" id=\"" . $var["namenumber"] . "\" name=\"" . $var["namenumber"] . "\" placeholder=\"" . $placeholdernumber . "\" value=\"" . $valuenumber . "\" title=\"" . $titlenumber . "\" " . $jsaction . " " . $readonlynumber . " onkeypress=\"return isNumberKey(this, event, $focus_obj);\">
                    </div>
                </div>";
    }
}

if (!function_exists("form_number_number")) {
    function form_number_number($var)
    {
        $label          = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class          = (empty($var["class"])) ? "" : $var["class"];
        $first          = (empty($var["first_selected"])) ? "" : "first-selected";
        $align          = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title          = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value          = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $style_icon     = (empty($var["readonly"])) ? "" : "  style=\"background-color:#EEEEEE\"";
        $group_css      = "style_form_input_" . $var["name"];

        $labelnumber          = (empty($var["labelnumber"])) ? $var["namenumber"] : $var["labelnumber"];
        $placeholdernumber    = (empty($var["placeholdernumber"])) ? ucwords(strtolower($labelnumber)) : ucwords(strtolower($var["placeholdernumber"]));
        $titlenumber          = (empty($var["titlenumber"])) ? "" : $var["titlenumber"];
        $valuenumber          = ((old($var["namenumber"]) != "") || (empty($var["valuenumber"]))) ? $var["valuenumber"] : $var["valuenumber"];
        $class_add_number     = (empty($var["mandatory_number"])) ? "" : " mandatory-input";
        $readonlynumber       = (empty($var["readonlynumber"])) ? "" : $var["readonlynumber"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj      = "'enter'";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj      = "no";
            } else {
                $focus_obj      = "document.myform." . $var["focus_field"];
            }
        } else {
            $focus_obj      = "no";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-3\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" placeholder=\"" . $placeholder . "\" value=\"" . $value . "\" title=\"" . $title . "\" " . $jsaction . " " . $readonly . " onkeypress=\"return isNumberKey(this, event, $focus_obj);\">
                    </div>
                    <label class=\"col-md-2 control-label\">" . $labelnumber . "</label>
                    <div class=\"col-md-4\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add_number . "\" id=\"" . $var["namenumber"] . "\" name=\"" . $var["namenumber"] . "\" placeholder=\"" . $placeholdernumber . "\" value=\"" . $valuenumber . "\" title=\"" . $titlenumber . "\" " . $jsaction . " " . $readonlynumber . " onkeypress=\"return isNumberKey(this, event, $focus_obj);\">
                    </div>
                </div>";
    }
}

if (!function_exists("form_number_number_number")) {
    function form_number_number_number($var)
    {
        $label          = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class          = (empty($var["class"])) ? "" : $var["class"];
        $first          = (empty($var["first_selected"])) ? "" : "first-selected";
        $align          = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title          = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value          = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $style_icon     = (empty($var["readonly"])) ? "" : "  style=\"background-color:#EEEEEE\"";
        $group_css      = "style_form_input_" . $var["name"];

        $labelnumber          = (empty($var["labelnumber"])) ? $var["namenumber"] : $var["labelnumber"];
        $placeholdernumber    = (empty($var["placeholdernumber"])) ? ucwords(strtolower($labelnumber)) : ucwords(strtolower($var["placeholdernumber"]));
        $titlenumber          = (empty($var["titlenumber"])) ? "" : $var["titlenumber"];
        $valuenumber          = ((old($var["namenumber"]) != "") || (empty($var["valuenumber"]))) ? $var["valuenumber"] : $var["valuenumber"];
        $class_add_number     = (empty($var["mandatory_number"])) ? "" : " mandatory-input";
        $readonlynumber       = (empty($var["readonlynumber"])) ? "" : $var["readonlynumber"];

        $labelnumber_2        = (empty($var["labelnumber_2"])) ? $var["namenumber_2"] : $var["labelnumber_2"];
        $placeholdernumber_2  = (empty($var["placeholdernumber_2"])) ? ucwords(strtolower($labelnumber_2)) : ucwords(strtolower($var["placeholdernumber_2"]));
        $titlenumber_2        = (empty($var["titlenumber_2"])) ? "" : $var["titlenumber_2"];
        $valuenumber_2        = ((old($var["namenumber_2"]) != "") || (empty($var["valuenumber_2"]))) ? $var["valuenumber_2"] : $var["valuenumber_2"];
        $class_add_number_2   = (empty($var["mandatory_number_2"])) ? "" : " mandatory-input";
        $readonlynumber_2     = (empty($var["readonlynumber_2"])) ? "" : $var["readonlynumber_2"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj      = "'enter'";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj      = "no";
            } else {
                $focus_obj      = "document.myform." . $var["focus_field"];
            }
        } else {
            $focus_obj      = "no";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-3\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" placeholder=\"" . $placeholder . "\" value=\"" . $value . "\" title=\"" . $title . "\" " . $jsaction . " " . $readonly . " onkeypress=\"return isNumberKey(this, event, $focus_obj);\">
                    </div>
                    <label class=\"col-md-2 control-label\">" . $labelnumber . "</label>
                    <div class=\"col-md-2\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add_number . "\" id=\"" . $var["namenumber"] . "\" name=\"" . $var["namenumber"] . "\" placeholder=\"" . $placeholdernumber . "\" value=\"" . $valuenumber . "\" title=\"" . $titlenumber . "\" " . $jsaction . " " . $readonlynumber . " onkeypress=\"return isNumberKey(this, event, $focus_obj);\">
                    </div>
                    <div class=\"col-md-2\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add_number_2 . "\" id=\"" . $var["namenumber_2"] . "\" name=\"" . $var["namenumber_2"] . "\" placeholder=\"" . $placeholdernumber_2 . "\" value=\"" . $valuenumber_2 . "\" title=\"" . $titlenumber_2 . "\" " . $jsaction . " " . $readonlynumber_2 . " onkeypress=\"return isNumberKey(this, event, $focus_obj);\">
                    </div>
                </div>";
    }
}

if (!function_exists("form_textarea3")) {
    function form_textarea3($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $rows             = (empty($var["rows"])) ? "rows='5'" : "rows=" . $var["rows"];
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                //   $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
                $focus_obj       = "";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                // $focus_obj       = " onKeypress=\"return focusObject(document.myform.".$var["focus_field"].", event)\"";
                $focus_obj       = "";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-4 control-label\">" . $label . $mandatory . "</label>            
                    <div class=\"col-md-8\">
                        <textarea class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . " " . $rows . ">" . $value . "</textarea>
                    </div>
                </div>";
    }
}

if (!function_exists("form_upload_file")) {
    function form_upload_file($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly       = (empty($var["readonly"])) ? "" : "disabled='true'";
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        if (empty($var["valfile"])) {
            return "<div class=\"form-group\">
                        <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                        <div class=\"col-md-7\">
                            <input type=\"file\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                        </div>
                        <div class=\"col-md-2\">
                            <span class=\"btn btn-sm btn-danger\">Tidak Ada</span>
                        </div>
                    </div>";
        } else {
            $vurl = $var["valfile"];
            if (empty($var["valdelete"])) {
                return "<div class=\"form-group\">
                            <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                            <div class=\"col-md-7\">
                                <input type=\"file\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                            </div>
                            <div class=\"col-md-2\">
                                <a href=\" $vurl \" target=\"_blank\" target=\"_blank\" class=\"btn btn-sm btn-warning m-r-5\">Tampilkan</a>
                            </div>
                        </div>";
            } else {
                $vurlDelete = $var["valdelete"];
                $pathImage  = url('') . "/app/img/icon/delete.png";
                return "<div class=\"form-group\">
                            <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                            <div class=\"col-md-7\">
                                <input type=\"file\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                            </div>
                            <div class=\"col-md-2\">
                                <a href=\" $vurl \" target=\"_blank\" target=\"_blank\" class=\"btn btn-sm btn-warning m-r-5\">Tampilkan</a>
                                <a href=\" $vurlDelete \"><img src=\" $pathImage  \" width=\"15px\" /></a>
                            </div>
                        </div>";
            }
        }
    }
}

if (!function_exists("form_currency_select_number")) {
    function form_currency_select_number($var)
    {
        $label          = (empty($var["label"])) ? $var["name"] : ucwords(strtolower($var["label"]));
        $class          = (empty($var["class"])) ? "" : $var["class"];
        $align          = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtoupper($label)) : ucwords(strtoupper($var["placeholder"]));
        $title          = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : "disabled='true'";
        $data_style     = (empty($var["readonly"])) ? "btn-white" : "";
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $withnull       = (empty($var["withnull"])) ? "" : $var["withnull"];

        $placeholdertext = (empty($var["placeholder"])) ? ucwords(strtolower($var["label"])) : ucwords(strtolower($var["placeholder"]));
        $nametext        = $var["nametext"];
        $valuetext       = ((old($var["nametext"]) != "") || (empty($var["valuetext"]))) ? old($var["nametext"]) : $var["valuetext"];
        $jsactiontext    = (empty($var["jsactiontext"])) ? "" : $var["jsactiontext"];
        $readonlytext    = (empty($var["readonlytext"])) ? "" : $var["readonlytext"];

        $placeholdernumber = (empty($var["placeholder"])) ? ucwords(strtolower($var["label"])) : ucwords(strtolower($var["placeholder"]));
        $namenumber        = $var["namenumber"];
        $valuenumber       = ((old($var["namenumber"]) != "") || (empty($var["valuenumber"]))) ? $var["valuenumber"] : $var["valuenumber"];
        $jsactionnumber    = (empty($var["jsactionnumber"])) ? "" : $var["jsactionnumber"];
        $readonlynumber    = (empty($var["readonlynumber"])) ? "" : $var["readonlynumber"];

        if (!empty($var["value"])) {
            $value      = (old($var["name"]) != "") ? old($var["name"]) : $var["value"];
        } else {
            $value      = "";
        }

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj      = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj      = "";
            } else {
                $focus_obj      = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj      = "";
        }

        $object     = "<div class=\"form-group\">
                                        <label class=\"control-label col-md-3\">" . $label . $mandatory . "</label>
                                        <div class=\"col-md-5\">
                                            <input type=\"text\" class=\"form-control \" placeholder=\"" . $placeholdertext . "\" id=\"" . $nametext . "\" name=\"" . $nametext . "\" value=\"" . $valuetext . "\"" . $jsactiontext . " " . $readonlytext . " onBlur=\"this.value=formatCurrency(this.value);\" " . $focus_obj . "/>
                                        </div>
                                        <div class=\"col-md-2\">
                                            <select class=\"form-control selectpicker input-sm $class\" data-size=\"5\" data-live-search=\"true\" data-style=\"" . $data_style . "\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" " . $readonly . ">";

        if (!empty($var["withnull"])) {
            $object     .= "<label>";
            $object     .= "<option value=\"0\" selected=\"selected\">-Pilih-</option>";
            $object     .= "</label>";
        }

        foreach ($var["source"] as $rows) {
            $rows = (object)$rows;

            if ($rows->id) {
                $selected   = ($rows->id == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows->id . "\" $selected>" . $rows->name . "</option>";
                $object     .= "</label>";
            } else {
                $selected   = ($rows["id"] == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows["id"] . "\" $selected>" . $rows["name"] . "</option>";
                $object     .= "</label>";
            }
        }

        $object     .= "</select>
                                        </div>
                                        <div class=\"col-md-2\">
                                            <input type=\"text\" class=\"form-control \" placeholder=\"" . $placeholdernumber . "\" id=\"" . $namenumber . "\" name=\"" . $namenumber . "\" value=\"" . $valuenumber . "\"" . $jsactionnumber . " " . $readonlynumber . " onBlur=\"this.value=formatCurrency(this.value);\" " . $focus_obj . "/>
                                        </div>
                            </div>";

        return $object;
    }
}

if (!function_exists("form_select_currency")) {
    function form_select_currency($var)
    {
        $label          = (empty($var["label"])) ? $var["name"] : ucwords(strtolower($var["label"]));
        $class          = (empty($var["class"])) ? "" : $var["class"];
        $align          = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtoupper($label)) : ucwords(strtoupper($var["placeholder"]));
        $title          = (empty($var["title"])) ? "" : $var["title"];
        $readonly       = (empty($var["readonly"])) ? "" : "disabled='true'";
        $data_style     = (empty($var["readonly"])) ? "btn-white" : "";
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $withnull       = (empty($var["withnull"])) ? "" : $var["withnull"];

        $placeholdertext = (empty($var["placeholder"])) ? ucwords(strtolower($var["label"])) : ucwords(strtolower($var["placeholder"]));
        $nametext        = $var["nametext"];
        $valuetext       = ((old($var["nametext"]) != "") || (empty($var["valuetext"]))) ? $var["valuetext"] : $var["valuetext"];
        $jsactiontext    = (empty($var["jsactiontext"])) ? "" : $var["jsactiontext"];
        $readonlytext    = (empty($var["readonlytext"])) ? "" : $var["readonlytext"];

        if (!empty($var["value"])) {
            $value      = (old($var["name"]) != "") ? old($var["name"]) : $var["value"];
        } else {
            $value      = "";
        }

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj      = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj      = "";
            } else {
                $focus_obj      = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj      = "";
        }

        $object     = "<div class=\"form-group\">
                                        <label class=\"control-label col-md-3\">" . $label . $mandatory . "</label>
                                        <div class=\"col-md-5\">
                                            <select class=\"form-control selectpicker input-sm $class\" data-size=\"5\" data-live-search=\"true\" data-style=\"" . $data_style . "\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\" " . $readonly . ">";

        if (!empty($var["withnull"])) {
            $object     .= "<label>";
            $object     .= "<option value=\"0\" selected=\"selected\">-Pilih-</option>";
            $object     .= "</label>";
        }

        foreach ($var["source"] as $rows) {
            $rows = (object)$rows;

            if ($rows->id) {
                $selected   = ($rows->id == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows->id . "\" $selected>" . $rows->name . "</option>";
                $object     .= "</label>";
            } else {
                $selected   = ($rows["id"] == $value) ? "selected" : "";
                $object     .= "<label>";
                $object     .= "<option value=\"" . $rows["id"] . "\" $selected>" . $rows["name"] . "</option>";
                $object     .= "</label>";
            }
        }

        $object     .= "</select>
                                        </div>
                                        <div class=\"col-md-4\">
                                            <input type=\"text\" class=\"form-control \" placeholder=\"" . $placeholdertext . "\" id=\"" . $nametext . "\" name=\"" . $nametext . "\" value=\"" . $valuetext . "\"" . $jsactiontext . " " . $readonlytext . " onBlur=\"this.value=formatCurrency(this.value);\" " . $focus_obj . "/>
                                        </div>
                            </div>";

        return $object;
    }
}

if (!function_exists("form_datepicker_datepicker")) {
    function form_datepicker_datepicker($var)
    {
        $label        = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class        = (empty($var["class"])) ? "" : $var["class"];
        $first        = (empty($var["first_selected"])) ? "" : "first-selected";
        $align        = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder  = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title        = (empty($var["title"])) ? "" : $var["title"];
        $size         = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly     = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction     = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory    = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add    = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value        = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css    = "style_form_input_" . $var["name"];

        $labeldate2   = (empty($var["labeldate2"])) ? $var["namedate2"] : $var["labeldate2"];
        $valuedate2   = ((old($var["namedate2"]) != "") || (empty($var["valuedate2"]))) ? old($var["namedate2"]) : $var["valuedate2"];
        $mandatorydate2    = (empty($var["mandatorydate2"])) ? "" : " <span style=\"color:#FF0000\">*</span>";

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj   = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj   = "";
            } else {
                $focus_obj   = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj    = "";
        }

        if ($readonly != "readonly") {
            return "<div class=\"form-group\">
                        <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                        <div class=\"col-md-3\">
                            <div class=\"input-group date datepicker-format\" id=\"datepicker-disabled-past\" data-date-format=\"dd/mm/yyyy\">
                                <input type=\"text\" class=\"form-control\" placeholder=\"Select Date\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\" />
                                <span class=\"input-group-addon\"><i class=\"fa fa-calendar\"></i></span>
                            </div>
                        </div>
                        <label class=\"col-md-2 control-label\">" . $labeldate2 . $mandatorydate2 . "</label>
                        <div class=\"col-md-4\">
                            <div class=\"input-group date datepicker-format\" id=\"datepicker-disabled-past\" data-date-format=\"dd/mm/yyyy\">
                                <input type=\"text\" class=\"form-control\" placeholder=\"Select Date\" id=\"" . $var["namedate2"] . "\" name=\"" . $var["namedate2"] . "\" value=\"" . $valuedate2 . "\" />
                                <span class=\"input-group-addon\"><i class=\"fa fa-calendar\"></i></span>
                            </div>
                        </div>
                    </div>";
        } else {
            return "<div class=\"form-group\">
                        <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                        <div class=\"col-md-3\">
                            <input type=\"text\" class=\"form-control\" placeholder=\"Select Date\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\" readonly/>
                        </div>
                        <label class=\"col-md-2 control-label\">" . $labeldate2 . $mandatorydate2 . "</label>
                        <div class=\"col-md-4\">
                            <input type=\"text\" class=\"form-control\" placeholder=\"Select Date\" id=\"" . $var["namedate2"] . "\" name=\"" . $var["namedate2"] . "\" value=\"" . $valuedate2 . "\" readonly/>
                        </div>
                    </div>";
        }
    }
}

if (!function_exists("formnolabel_text")) {
    function formnolabel_text($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder      = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly         = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction         = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory        = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add        = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css        = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div>
                    <input type=\"text\" class=\"form-control " . $first . " " . $class_add . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\" placeholder=\"" . $placeholder . "\" " . $readonly . " " . $focus_obj . " style=\"text-align:$align;\" />
                </div>";
    }
}

if (!function_exists("form_text_text")) {
    function form_text_text($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : ucwords(strtolower($var["label"]));
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        $placeholdertext = (empty($var["placeholder"])) ? ucwords(strtolower($var["labeltext"])) : ucwords(strtolower($var["placeholder"]));
        $nametext        = $var["nametext"];
        $valuetext       = ((old($var["nametext"]) != "") || (empty($var["valuetext"]))) ? old($var["nametext"]) : $var["valuetext"];
        $jsactiontext    = (empty($var["jsactiontext"])) ? "" : $var["jsactiontext"];
        $readonlytext    = (empty($var["readonlytext"])) ? "" : $var["readonlytext"];

        $object     = "<div class=\"form-group\">
                                        <label class=\"control-label col-md-4\">" . $label . $mandatory . "</label>
                                        <div class=\"col-md-3\">
                                            <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                                        </div>
                                        <div class=\"col-md-5\">
                                            <input type=\"text\" class=\"form-control \" placeholder=\"" . $placeholdertext . "\" id=\"" . $nametext . "\" name=\"" . $nametext . "\" value=\"" . $valuetext . "\"" . $jsactiontext . " " . $readonlytext . "/>
                                        </div>
                            </div>";

        return $object;
    }
}

if (!function_exists("form_text2")) {
    function form_text2($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : ucwords(strtolower($var["label"]));
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-4 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-8\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                    </div>
                </div>";
    }
}

if (!function_exists("form_text_mdt")) {
    function form_text_mdt($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " ";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . " <span style=\"color:#FF0000\">*</span>" . "</label>
                    <div class=\"col-md-9\">
                        <input type=\"text\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . "/>
                    </div>
                </div>";
    }
}

if (!function_exists("form_ckeditor")) {
    function form_ckeditor($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $placeholder      = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $rows             = (empty($var["rows"])) ? "20" : $var["rows"];
        $readonly         = (empty($var["readonly"])) ? "" : $var["readonly"];
        $mandatory        = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add        = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];



        return "<div class=\"form-group\">
                     <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                     <div class=\"col-md-9\">
                         <textarea class=\"ckeditor " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"editor1\" name=\"" . $var["name"] . "\" rows=\"" . $rows . "\" " . $readonly . ">" . $value . "</textarea>
                     </div>
                 </div>";
    }
}

if (!function_exists("form_multi_select")) {
    function form_multi_select($var)
    {
        // $label             = (empty($var["label"])) ? $var["name"] : $var["label"];
        $label          = (empty($var["label"])) ? $var["name"] : ucwords(strtolower($var["label"]));
        $class               = (empty($var["class"])) ? "" : $var["class"];
        $align               = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder     = (empty($var["placeholder"])) ? ucwords(strtoupper($label)) : ucwords(strtoupper($var["placeholder"]));
        $title             = (empty($var["title"])) ? "" : $var["title"];
        $readonly         = (empty($var["readonly"])) ? "" : "disabled='true'";
        $data_style     = (empty($var["readonly"])) ? "btn-white" : "";
        $jsaction         = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory         = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add         = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $withnull         = (empty($var["withnull"])) ? "" : $var["withnull"];

        if (!empty($var["value"])) {
            $value         = (old($var["name"]) != "") ? old($var["name"]) : $var["value"];
        } else {
            $value         = "";
        }

        $object     =  "<div class=\"form-group\">
                            <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                            <div class=\"col-md-9\">
                                <select class=\"multiple-select2 form-control\" multiple=\"multiple\" tabindex=\"-1\" style=\"display: none;\" name=\"" . $var["name"] . "\" id=\"" . $var["name"] . "\">";

                                foreach ($var["source"] as $rows) {
                                    $rows = (object)$rows;

                                    if ($rows->id) {
                                        $selected     = (in_array($rows->id, $value)) ? "selected" : "";
                                        $object     .= "<option value=\"" . $rows->id . "\" $selected>" . $rows->name . "</option>";
                                    } else {
                                        $selected     = (in_array($rows["id"], $value)) ? "selected" : "";
                                        $object     .= "<option value=\"" . $rows["id"] . "\" $selected>" . $rows["name"] . "</option>";
                                    }
                                }
        

        $object     .=          "</select>
                            </div>
                        </div>";

        return $object;
    }
}

if (!function_exists("form_upload_multi")) {
    function form_upload_multi($var)
    {
        $label            = (empty($var["label"])) ? $var["name"] : $var["label"];
        $class            = (empty($var["class"])) ? "" : $var["class"];
        $first            = (empty($var["first_selected"])) ? "" : "first-selected";
        $align            = (empty($var["align"])) ? "left" : $var["align"];
        $placeholder    = (empty($var["placeholder"])) ? ucwords(strtolower($label)) : ucwords(strtolower($var["placeholder"]));
        $title            = (empty($var["title"])) ? "" : $var["title"];
        $size             = (empty($var["size"])) ? "30px" : $var["size"] . "px";
        $readonly       = (empty($var["readonly"])) ? "" : $var["readonly"];
        $jsaction       = (empty($var["jsaction"])) ? "" : $var["jsaction"];
        $mandatory      = (empty($var["mandatory"])) ? "" : " <span style=\"color:#FF0000\">*</span>";
        $class_add      = (empty($var["mandatory"])) ? "" : " mandatory-input";
        $value            = ((old($var["name"]) != "") || (empty($var["value"]))) ? old($var["name"]) : $var["value"];
        $group_css      = "style_form_input_" . $var["name"];

        if ($readonly != "readonly") {
            if (empty($var["focus_field"])) {
                $focus_obj       = " onKeypress=\"return handleEnter(this, event)\"";
            } elseif ($var["focus_field"] == "no") {
                $focus_obj       = "";
            } else {
                $focus_obj       = " onKeypress=\"return focusObject(document.myform." . $var["focus_field"] . ", event)\"";
            }
        } else {
            $focus_obj        = "";
        }

        return "<div class=\"form-group\">
                    <label class=\"col-md-3 control-label\">" . $label . $mandatory . "</label>
                    <div class=\"col-md-9\">
                        <input type=\"file\" class=\"form-control " . $class . " " . $first . " " . $class_add . "\" placeholder=\"" . $placeholder . "\" id=\"" . $var["name"] . "\" name=\"" . $var["name"] . "[]\" value=\"" . $value . "\"" . $jsaction . " " . $readonly . " " . $focus_obj . " style=\"margin-bottom: 5px;\" multiple/>
                    </div>
                </div>";
    }
}