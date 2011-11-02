function chekuncheck(s, d)
{
    var i = 0;

    for (; i < d.elements.length; i++) {
        if (s.checked === true) {
            d.elements[i].checked = true;
        } else {
            d.elements[i].checked = false;
        }
    }
}