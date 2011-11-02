function process_form(f)
{
    var e;
    var i = 0;

    if (document.all || document.getElementById) {
        for (; i < f.length; i++) {
            e = f.elements[i];
            if (e.name && e.name.substring(0, 4) === "req_") {
                if (e.type && (e.type === "text" || e.type === "textarea" || e.type === "password" || e.type === "file") && e.value === "") {
                    window.alert('"' + reqFormLang[e.name] + '" - ' + reqField);
                    e.focus();
                    return false;
                }
            }
        }
    }
    return true;
}