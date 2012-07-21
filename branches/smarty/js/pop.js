var gwActivePopup = null, gwTimeoutId = 0;

function gwCloseActive() {
    if (gwActivePopup) {
        gwActivePopup.style.visibility = 'hidden';
        gwActivePopup = null;
    }
}


function elementX(el) {
    var x = el.offsetLeft, parent = el.offsetParent;
    while (parent !== null) {
        x += parent.offsetLeft;
        parent = parent.offsetParent;
    }
    return x;
}


function elementY(el) {
    var y = el.offsetTop, parent = el.offsetParent;
    while (parent !== null) {
        y += parent.offsetTop;
        parent = parent.offsetParent;
    }
    return y;
}


function gwPopup(e, layerid, noautoclose) {
    var isEvent = true, x = null, y = null, layer = document.getElementById(layerid);

    gwCloseActive();
    try {
        e.type;
    } catch (ex) {
        isEvent = false;
    }

    if (isEvent) {
        if (e.pageX || e.pageY) {
            x = e.pageX;
            y = e.pageY;
        } else if (e.clientX || e.clientY) {
            if (document.documentElement && document.documentElement.scrollTop) {
                x = e.clientX + document.documentElement.scrollLeft;
                y = e.clientY + document.documentElement.scrollTop;
            } else {
                x = e.clientX + document.body.scrollLeft;
                y = e.clientY + document.body.scrollTop;
            }
        } else {
            return;
        }
    } else if (e !== null) {
        x = elementX(e);
        y = elementY(e);
    }

    if (x !== null) {
        layer.style.left = x + 'px';
        layer.style.top = y + 'px';
    }
    layer.style.visibility = 'visible';
    gwActivePopup = layer;
    clearTimeout(gwTimeoutId);
    gwTimeoutId = 0;

    if (!noautoclose) {
        gwTimeoutId = window.setTimeout(gwCloseActive, 2000);
        layer.onmouseout = function () {
            window.clearTimeout(gwTimeoutId);
            gwTimeoutId = window.setTimeout(gwCloseActive, 350);
        };
        layer.onmouseover = function () {
            window.clearTimeout(gwTimeoutId);
            gwTimeoutId = 0;
        };
    }
}


function downloadPopup(e, aid) {
    gwCloseActive();
    var title = document.getElementById('pun-title'), body = document.getElementById('pun-body'), desc = document.getElementById('pun-desc'), funcdata = ATTACH_DATA[aid];
    var atime = funcdata[0];
    var adescr = funcdata[1];
    var acmt = funcdata[2];
    var athumb = funcdata[3];
    var can_download = funcdata[4];

    if (can_download) {
        adescr = '<a href="' + O_BASE_URL + '/download.php?aid=' + aid + '" title="click to download" class="att_filename">' + adescr;
    }
    if (athumb != '') {
        adescr += '<br /><img src="' + O_BASE_URL + '/' + athumb + '" alt="" /><br/>';
    }
    if (can_download) {
        adescr += '</a>';
    }
    if (athumb != '') {
        adescr += '<strong>BBcode</strong>: <input type="text" onclick="this.select();" value="::thumb' + aid + '::" /><br/>';
    }

    title.innerHTML = '#' + aid + ': ' + atime;
    desc.innerHTML = adescr;
    body.innerHTML = acmt;

    gwPopup(e, 'pun-popup');
}