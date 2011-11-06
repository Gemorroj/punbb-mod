function chekuncheck(s, d)
{
    var i = 0;

    for (; i < d.elements.length; i++) {
        d.elements[i].checked = s.checked;
    }
}