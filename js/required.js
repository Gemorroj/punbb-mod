function process_form(f) {
    for (var e, i = 0, l = f.length; i < l; i++) {
        e = f.elements[i];
        if (e.name && e.name.substring(0, 4) === "req_") {
            if (e.type && (e.type === "text" || e.type === "textarea" || e.type === "password" || e.type === "file") && e.value === "") {
                window.alert('"' + reqFormLang[e.name] + '" - ' + reqField);
                e.focus();
                return false;
            }
        }
    }

    return true;
}