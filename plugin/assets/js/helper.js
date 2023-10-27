function _yesno2html(val) {
    if (val) {
        return '<i class="fa fa-check text-success"></i>';
    } else {
        return '<i class="fa fa-times text-danger"></i>';
    }
}

window._yesno2html = _yesno2html;