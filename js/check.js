function chekuncheck(s, d) {
    for (var i = 0, l = d.elements.length; i < l; i++) {
        d.elements[i].checked = s.checked;
    }
}