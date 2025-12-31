$(document).ready(function() {
    var choose_video = document.querySelector(".choose-video");
    var selected_video = document.querySelector(".selected-video");
    var overlay = document.querySelector(".overlay");
    var overlay_path = document.querySelector(".overlay-path");
    var overlay_left = document.querySelector(".overLay-left");

    function getDataParams(params) {
        var data = JSON.parse(params);
        // var id = data.id;
        // var name = data.name;
        // var path = data.path;
        //
        // if (id && name && path) {
        //     choose_video.classList.add('hidden');
        //     selected_video.classList.remove('hidden');
        //     overlay.setAttribute('data-id', id);
        //     overlay_path.setAttribute('data-id', id);
        //     //
        //     overlay_left.textContent = name;
        // } else {
        //     choose_video.classList.remove('hidden');
        //     selected_video.classList.add('hidden');
        //     overlay.setAttribute('data-id', '');
        //     overlay_path.setAttribute('data-id', '');
        //     overlay_path.src = "";
        // }
        //
        // videoSave();
    }

    window.getDataParams = getDataParams;

    videoSave = function() {
        var overlay_path = $(".overlay-path");
        overlay_path.on("videosave", function(event) {
            if (overlay_path.data('id') !== "" && overlay_path.data('event') === 'save') {
                qcubed.recordControlModification("$this->ControlId", "_Item", overlay_path.data('id'));
            }
        });

        var VideoSaveEvent = $.Event("videosave");
        overlay_path.trigger(VideoSaveEvent);
    }

    $(".overlay").on("click", function() {
        var id = overlay.getAttribute('data-id')

        choose_video.classList.remove('hidden');
        selected_video.classList.add('hidden');
        overlay.setAttribute('data-id', '');
        overlay_path.setAttribute('data-id', '');
        overlay_path.src = '';
        overlay_left.textContent = '';

        videoDelete();
    });

    videoDelete = function() {
        var overlay = $(".overlay");
        overlay.on("videodelete", function(event) {
            if (overlay.data('id') !== "" && overlay.data('event') === 'delete') {
                qcubed.recordControlModification("$this->ControlId", "_Item", overlay.data('id'));
            }
        });

        var VideoDeleteEvent = $.Event("videodelete");
        overlay.trigger(VideoDeleteEvent);
    }
});

window.parent.opener.getDataParams('$data');
window.close();

console.log("imagesave event fired, data.id=", overlay_path.data('id'));

$(document).ready(function() {
    var choose_video = document.querySelector(".choose-video");
    var selected_video = document.querySelector(".selected-video");
    var embed_wrap = document.querySelector(".embed-responsive");
    var selected_overlay = document.querySelector(".selected-overlay");
    var delete_wrapper = document.querySelector(".delete-wrapper");
    var delete_overlay = document.querySelector(".delete-overlay");

    function getVideoParams(params) {
        var data = JSON.parse(params);
        console.log(data);
        var id = data.id;
        var embed = data.embed;
        var params = {id: data.id, embed: data.embed}; // Kas on võimalik???

        if (id && embed) {
            choose_video.classList.add('hidden');
            selected_video.classList.remove('hidden');
            delete_wrapper.classList.remove('hidden');
            embed_wrap.innerHTML = embed;
            selected_video.setAttribute('data-id', id);
            delete_wrapper.setAttribute('data-id', id);
            delete_overlay.setAttribute('data-id', id);
        } else {
            choose_video.classList.remove('hidden');
            selected_video.classList.add('hidden');
            delete_wrapper.classList.add('hidden');
            embed_wrap.innerHTML = '';
            selected_video.setAttribute('data-id', '');
            delete_wrapper.setAttribute('data-id', '');
            delete_overlay.setAttribute('data-id', '');
        }

        videoSave(params);
    }

    window.getVideoParams = getVideoParams;

    videoSave = function(data) {
        var selected_video = $(".selected-video");
        selected_video.on("videosave", function(event) {
            if (selected_video.data('id') !== "" && selected_video.data('event') === 'save') {
                console.log("videosave INPUT:", selected_video);
                console.log("imagesave event fired, data.id=", selected_video.data('id'));
                qcubed.recordControlModification("$this->ControlId", "_Item", selected_video.data('id'));
                qcubed.recordControlModification("$this->ControlId", "_SaveItems", JSON.stringify(data));
            }
        });

        var VideoSaveEvent = $.Event("videosave");
        selected_video.trigger(VideoSaveEvent);
    }

    $(".delete-overlay").on("click", function() {
        choose_video.classList.remove('hidden');
        selected_video.classList.add('hidden');

        selected_video.setAttribute('data-id', '');
        delete_wrapper.setAttribute('data-id', '');
        delete_overlay.setAttribute('data-id', '');

        videoDelete();
    });

    videoDelete = function() {
        var delete_video = $(delete_overlay);
        delete_video.on("videodelete", function(event) {
            if (delete_video.data('id') !== "") {
                qcubed.recordControlModification("$this->ControlId", "_DeleteItem", delete_video.data('id'));
            }
        });

        var VideoDeleteEvent = $.Event("videodelete");
        delete_video.trigger(VideoDeleteEvent);
    }
});

//////////////////////////////////

$(document).ready(function() {
    var choose_image = document.querySelector(".choose-image");
    var selected_image = document.querySelector(".selected-image");
    var overlay = document.querySelector(".overlay");
    var overlay_path = document.querySelector(".overlay-path");
    var overlay_left = document.querySelector(".overLay-left");

    function getDataParams(params) {
        var data = JSON.parse(params);
        console.log(data);
        var id = data.id;
        var name = data.name;
        var path = data.path;

        if (id && name && path) {
            choose_image.classList.add('hidden');
            selected_image.classList.remove('hidden');
            overlay.setAttribute('data-id', id);
            overlay_path.setAttribute('data-id', id);
            overlay_path.src = '$this->strTempUrl' + path;
            overlay_left.textContent = name;
        } else {
            choose_image.classList.remove('hidden');
            selected_image.classList.add('hidden');
            overlay.setAttribute('data-id', '');
            overlay_path.setAttribute('data-id', '');
            overlay_path.src = "";
        }

        imageSave();
    }

    window.getDataParams = getDataParams;

    imageSave = function() {
        var overlay_path = $(".overlay-path");
        overlay_path.on("imagesave", function(event) {
            if (overlay_path.data('id') !== "" && overlay_path.data('event') === 'save') {

                console.log("imagesave event fired, data.id=", overlay_path.data('id'));
                qcubed.recordControlModification("$this->ControlId", "_Item", overlay_path.data('id'));
            }
        });

        var ImageSaveEvent = $.Event("imagesave");
        overlay_path.trigger(ImageSaveEvent);
    }

    $(".overlay").on("click", function() {
        var id = overlay.getAttribute('data-id')

        choose_image.classList.remove('hidden');
        selected_image.classList.add('hidden');
        overlay.setAttribute('data-id', '');
        overlay_path.setAttribute('data-id', '');
        overlay_path.src = '';
        overlay_left.textContent = '';

        imageDelete();
    });

    imageDelete = function() {
        var overlay = $(".overlay");
        overlay.on("imagedelete", function(event) {
            if (overlay.data('id') !== "" && overlay.data('event') === 'delete') {
                qcubed.recordControlModification("$this->ControlId", "_Item", overlay.data('id'));
            }
        });

        var ImageDeleteEvent = $.Event("imagedelete");
        overlay.trigger(ImageDeleteEvent);
    }
});

/////////////////////////////

console.log('json_encode($saveIds)')