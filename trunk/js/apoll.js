var busy = '<span style="display:block;position:absolute;top:50%;left:46%;width:50%;"><span style="position:relative;top:-50%;background-color:white;padding:8px;border:1px solid #AfAfAf">Please wait... <img src="img/busy.gif" alt="loading" /></span></span>';
if (typeof poll !== "object") {
    poll = {};
}
$(document).ready(function () {
    $("#apinsert").modalBox({boxTimer: 0});
});

poll.admin = function () {
    var s = this;
    s.edit = function (id) {
        $.modalBox.settings.ajax = "ajax.server.php?poll=gefrm&pid=" + id;
        $.modalBox.showBox($.modalBox.settings);
    };
    s.update = function (id) {
        $.modalBox.hideBox(function () {
            $('div.cnt_' + id).each(function (i) {
                $(this).css({'position': 'relative'});
                var height = $(this).height(), width = $(this).width(), $.modalBox.generateId('hider');

                $(this).append('<div id="' + hid + '" class="hider' + id + '">' + busy + '</div>');
                $('#' + hid).css({
                    'position': 'absolute',
                    'background-color': '#ffffff',
                    'top': 0,
                    'left': 0,
                    'width': width,
                    'height': height
                });
            });
            $.ajax({
                type: "POST",
                url: "ajax.server.php?poll=update",
                dataType: "html",
                cache: false,
                data: {'d': $('#polledit').serialize()},
                success: function (data) {
                    $('.hider' + id).remove();
                    $('div.cnt_' + id).html(data);
                }
            });
        });
        return false;
    };
    s.del = function (id) {};
    s.close = function (id) {};
};