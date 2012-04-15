function resize_text_area(dpixels)
{
    var box = (document.all) ? document.all.req_message : document.forms.post.req_message;
    var cur_height = parseInt(box.style.height, 10) ? parseInt(box.style.height, 10) : 180;
    var new_height = cur_height + dpixels;
    if (new_height > 0) {
        box.style.height = new_height + "px";
    }
}
