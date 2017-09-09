var gwActivePopup = null, gwTimeoutId = 0;

function gwCloseActive() {
    if (gwActivePopup) {
        gwActivePopup.style.visibility = 'hidden';
        gwActivePopup = null;
    }
}

/**
 * @param {Event} e
 * @param {String} layerId
 * @param {Boolean} autoClose
 */
function gwPopup(e, layerId, autoClose) {
    var x, y;

    gwCloseActive();

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

    gwActivePopup = document.getElementById(layerId);
    gwActivePopup.style.left = x + 'px';
    gwActivePopup.style.top = y + 'px';

    gwActivePopup.style.visibility = 'visible';



    if (autoClose) {
        clearTimeout(gwTimeoutId);
        gwTimeoutId = 0;

        gwTimeoutId = window.setTimeout(gwCloseActive, 2000);
        gwActivePopup.onmouseleave = function () {
            window.clearTimeout(gwTimeoutId);
            gwTimeoutId = window.setTimeout(gwCloseActive, 350);
        };
        gwActivePopup.onmouseenter = function () {
            window.clearTimeout(gwTimeoutId);
            gwTimeoutId = 0;
        };
    }
}

/**
 * @param {Event} e
 * @param {String} aid
 */
function downloadPopup(e, aid) {
    gwCloseActive();
    var title = document.getElementById('pun-title'),
        body = document.getElementById('pun-body'),
        desc = document.getElementById('pun-desc'),
        funcdata = ATTACH_DATA[aid];
    var atime = funcdata[0];
    var adescr = funcdata[1];
    var acmt = funcdata[2];
    var athumb = funcdata[3];
    var can_download = funcdata[4];

    if (can_download) {
        adescr = '<a href="./download.php?aid=' + aid + '" title="download" class="att_filename">' + adescr;
    }
    if (athumb != '') {
        adescr += '<br /><img src="./' + athumb + '" alt="" /><br />';
    }
    if (can_download) {
        adescr += '</a>';
    }
    if (athumb != '') {
        adescr += '<strong>BBcode</strong>: <input type="text" onclick="this.select();" value="::thumb' + aid + '::" /><br />';
    }

    title.innerHTML = '#' + aid + ': ' + atime;
    desc.innerHTML = adescr;
    body.innerHTML = acmt;

    gwPopup(e, 'pun-popup', true);
}
