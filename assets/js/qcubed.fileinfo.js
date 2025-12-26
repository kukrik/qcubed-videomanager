(function ($) {
    $.fn.fileInfo = function (options) {
        options = $.extend({
            rootPath: null,
            rootUrl: null,
            tempPath: null,
            tempUrl: null,
        }, options)

        /////////////////////////////////////////

        // // Get a reference to the file-info
        const file_info_body = document.querySelector(".file-info-body");

        /////////////////////////////////////////

        fileInfoTemplate();

        /////////////////////////////////////////

        function fileInfoTemplate()
        {
            const file_info_no_wrapper = document.createElement("div");
            file_info_no_wrapper.className = "file-info-no-wrapper";
            const file_info_no_wrapper_i = document.createElement("i");
            file_info_no_wrapper_i.className = "fa fa-crop fa-5x";
            file_info_no_wrapper_i.setAttribute("aria-hidden", "true");
            const file_info_no_wrapper_p = document.createElement("p");
            file_info_no_wrapper_p.setAttribute("data-lang", "nothing_is_selected");
            file_info_no_wrapper_p.innerHTML = "Nothing is selected";

            file_info_body.appendChild(file_info_no_wrapper);
            file_info_no_wrapper.appendChild(file_info_no_wrapper_i);
            file_info_no_wrapper.appendChild(file_info_no_wrapper_p);

            const multiple_items_selected = document.createElement("div");
            multiple_items_selected.className = "file-info-multiple-items-selected hidden";
            const multiple_items_selected_i = document.createElement("i");
            multiple_items_selected_i.className = "fa fa-asterisk fa-5x";
            multiple_items_selected_i.setAttribute("aria-hidden", "true");
            const multiple_items_selected_p = document.createElement("p");
            multiple_items_selected_p.setAttribute("data-lang", "multiple_items_selected");
            multiple_items_selected_p.innerHTML = "Multiple items selected";

            file_info_body.appendChild(multiple_items_selected);
            multiple_items_selected.appendChild(multiple_items_selected_i);
            multiple_items_selected.appendChild(multiple_items_selected_p);

            const file_info_unavailable = document.createElement("div");
            file_info_unavailable.className = "file-info-unavailable hidden";
            const file_info_unavailable_i = document.createElement("i");
            file_info_unavailable_i.className = "fa fa-crop fa-5x";
            file_info_unavailable_i.setAttribute("aria-hidden", "true");
            const file_info_unavailable_p = document.createElement("p");
            file_info_unavailable_p.setAttribute("data-lang", "file_info_unavailable");
            file_info_unavailable_p.innerHTML = "The selected file type is unavailable";

            file_info_body.appendChild(file_info_unavailable);
            file_info_unavailable.appendChild(file_info_unavailable_i);
            file_info_unavailable.appendChild(file_info_unavailable_p);

            /////////////////////////////////////////

            // COMMON ITEMS
            const file_info_wrapper = document.createElement("div");
            file_info_wrapper.className = "file-info-wrapper hidden";
            const table = document.createElement("table");
            table.className = "name-value-list";
            const tbody = document.createElement("tbody");
            const title_tr = document.createElement("tr");
            const title_th = document.createElement("th");
            title_th.innerHTML = "Name";
            title_th.setAttribute("data-lang", "info_name");
            const title_td = document.createElement("td");
            const name_tr = document.createElement("tr");
            const name_td = document.createElement("td");
            name_td.className = "name";
            name_td.colSpan = 2;
            name_td.style = "padding-bottom: 10px";
            const size_tr = document.createElement("tr");
            const size_th = document.createElement("th");
            size_th.innerHTML = "Size";
            size_th.setAttribute("data-lang", "info_size");
            const size_td = document.createElement("td");
            size_td.className = "size";
            const mime_tr = document.createElement("tr");
            const mime_th = document.createElement("th");
            mime_th.innerHTML = "MIME-type";
            mime_th.setAttribute("data-lang", "info_mime");
            const mime_td = document.createElement("td");
            mime_td.className = "mime";
            const dimensions_tr = document.createElement("tr");
            const dimensions_th = document.createElement("th");
            dimensions_th.innerHTML = "Dimensions";
            dimensions_th.setAttribute("data-lang", "info_dimensions");
            const dimensions_td = document.createElement("td");
            dimensions_td.className = "dimension";
            const modified_tr = document.createElement("tr");
            const modified_th = document.createElement("th");
            modified_th.innerHTML = "Last Modified";
            modified_th.setAttribute("data-lang", "info_modified");
            const modified_td = document.createElement("td");
            modified_td.className = "modified";

            /////////////////////////////////////////

            // IMAGE_WRAPPER
            const image_wrapper = document.createElement("div");
            image_wrapper.className = "image-wrapper hidden";
            const img = document.createElement("img");
            img.alt = "";
            img.className = "img-fluid ofi-img-contain";

            file_info_body.appendChild(image_wrapper);
            image_wrapper.appendChild(img);

            // VIDEO_WRAPPER
            const video_wrapper = document.createElement("div");
            video_wrapper.className = "video-wrapper hidden";
            const embed = document.createElement("div");
            embed.className = "embed-container";
            const video = document.createElement("video");
            video.controls = "controls";
            video.preload = "metadata";

            file_info_body.appendChild(video_wrapper);
            video_wrapper.appendChild(embed);
            embed.appendChild(video);

            // AUDIO_WRAPPER
            const audio_wrapper = document.createElement("div");
            audio_wrapper.className = "audio-wrapper hidden";
            const audio = document.createElement("audio");
            audio.controls = "controls";
            audio.preload = "metadata";

            file_info_body.appendChild(audio_wrapper);
            audio_wrapper.appendChild(audio);

            // TXT_WRAPPER
            const ico_wrapper = document.createElement("div");
            ico_wrapper.className = "ico-wrapper hidden";
            const extension = document.createElement("span");
            extension.className = "icon";

            file_info_body.appendChild(ico_wrapper);
            ico_wrapper.appendChild(extension);

            /////////////////////////////////////////

            // COMMON ITEMS
            file_info_body.appendChild(file_info_wrapper);
            file_info_wrapper.appendChild(table);
            table.appendChild(tbody);
            tbody.appendChild(title_tr);
            title_tr.appendChild(title_th);
            title_tr.appendChild(title_td);
            tbody.appendChild(name_tr);
            name_tr.appendChild(name_td);
            tbody.appendChild(size_tr);
            size_tr.appendChild(size_th);
            size_tr.appendChild(size_td);
            tbody.appendChild(mime_tr);
            mime_tr.appendChild(mime_th);
            mime_tr.appendChild(mime_td);
            tbody.appendChild(dimensions_tr);
            dimensions_tr.appendChild(dimensions_th);
            dimensions_tr.appendChild(dimensions_td);
            tbody.appendChild(modified_tr);
            modified_tr.appendChild(modified_th);
            modified_tr.appendChild(modified_td);
        }

        /////////////////////////////////////////

        qcubed.getFileInfo = function(e)
        {
            const count = e.length;

            const file_info_no_wrapper = document.querySelector(".file-info-no-wrapper");
            const multiple_items_selected = document.querySelector(".file-info-multiple-items-selected");
            const file_info_unavailable = document.querySelector(".file-info-unavailable");
            const image_wrapper = document.querySelector(".image-wrapper");
            const video_wrapper = document.querySelector(".video-wrapper");
            const audio_wrapper = document.querySelector(".audio-wrapper");
            const ico_wrapper = document.querySelector(".ico-wrapper");
            const file_info_wrapper = document.querySelector(".file-info-wrapper");

            const img = document.querySelector(".image-wrapper img");
            const extension = document.querySelector(".ico-wrapper .icon");
            const video = document.querySelector(".video-wrapper video");
            const audio = document.querySelector(".audio-wrapper audio");
            const name = document.querySelector("td.name");
            const size = document.querySelector("td.size");
            const mime = document.querySelector("td.mime");
            const info_dimension = document.querySelector('[data-lang="info_dimensions"]');
            const dimension = document.querySelector("td.dimension");
            const modified = document.querySelector("td.modified");

            const imageExt = ['jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif', 'svg'];
            const videoExt = ['mov', 'mpeg', 'mpg', 'mp4', 'm4v'];
            const audioExt = ['wav', 'mp3', 'mp2',  'm4a', 'aac'];
            const txtExt = ['pdf', 'docx', 'doc', 'odt', 'xlsx',  'xls', 'pptx',  'ppt', 'rtf',  'txt',  'zip', 'rar', 'asice', 'cdoc', 'php', 'js', 'css',  'json',  'xml', 'html',  'htm', 'sql', 'yml'];

            if (count === 1) {
                if (e[0]['data-item-type'] === "dir") {
                    file_info_no_wrapper.classList.remove("hidden");
                    multiple_items_selected.classList.add("hidden");
                    file_info_unavailable.classList.add("hidden");
                    image_wrapper.classList.add("hidden");
                    video_wrapper.classList.add("hidden");
                    ico_wrapper.classList.add("hidden");
                    audio_wrapper.classList.add("hidden");
                    file_info_wrapper.classList.add("hidden");
                } else if (e[0]['data-item-type'] === "file") {
                    if (imageExt.includes(e[0]['data-extension'])) {
                        if (e[0]['data-extension'] !== 'svg') {
                            img.src = options.tempUrl + '/_files/thumbnail' + e[0]['data-path'];
                        } else {
                            img.src = options.rootUrl + e[0]['data-path'];
                        }
                        dimension.innerText = e[0]['data-dimensions'];
                        dimension.classList.remove("hidden");
                        info_dimension.classList.remove("hidden");

                        name.innerText = e[0]['data-name'];
                        extension.innerHTML = getFileIconExtension(e[0]['data-extension']);
                        size.innerText = e[0]['data-size'];
                        mime.innerText = e[0]['data-mimetype'];
                        modified.innerText = e[0]['data-date'];

                        image_wrapper.classList.remove("hidden");
                        file_info_wrapper.classList.remove("hidden");
                        video_wrapper.classList.add("hidden");
                        audio_wrapper.classList.add("hidden");
                        ico_wrapper.classList.add("hidden");
                        file_info_no_wrapper.classList.add("hidden");
                        multiple_items_selected.classList.add("hidden");
                        file_info_unavailable.classList.add("hidden");
                    } else if (videoExt.includes(e[0]['data-extension'])) {
                        video.src = options.rootUrl + e[0]['data-path'];
                        name.innerText = e[0]['data-name'];
                        extension.innerHTML = getFileIconExtension(e[0]['data-extension']);
                        size.innerText = e[0]['data-size'];
                        mime.innerText = e[0]['data-mimetype'];
                        modified.innerText = e[0]['data-date'];

                        video_wrapper.classList.remove("hidden");
                        file_info_wrapper.classList.remove("hidden");
                        info_dimension.classList.add("hidden");
                        dimension.classList.add("hidden");
                        image_wrapper.classList.add("hidden");
                        audio_wrapper.classList.add("hidden");
                        ico_wrapper.classList.add("hidden");
                        file_info_no_wrapper.classList.add("hidden");
                        multiple_items_selected.classList.add("hidden");
                        file_info_unavailable.classList.add("hidden");
                    } else if (audioExt.includes(e[0]['data-extension'])) {
                        audio.src = options.rootUrl + e[0]['data-path'];
                        name.innerText = e[0]['data-name'];
                        extension.innerHTML = getFileIconExtension(e[0]['data-extension']);
                        size.innerText = e[0]['data-size'];
                        mime.innerText = e[0]['data-mimetype'];
                        modified.innerText = e[0]['data-date'];

                        audio_wrapper.classList.remove("hidden");
                        file_info_wrapper.classList.remove("hidden");
                        info_dimension.classList.add("hidden");
                        dimension.classList.add("hidden");
                        image_wrapper.classList.add("hidden");
                        video_wrapper.classList.add("hidden");
                        ico_wrapper.classList.add("hidden");
                        file_info_no_wrapper.classList.add("hidden");
                        multiple_items_selected.classList.add("hidden");
                        file_info_unavailable.classList.add("hidden");
                    } else if (txtExt.includes(e[0]['data-extension'])) {
                        name.innerText = e[0]['data-name'];
                        extension.innerHTML = getFileIconExtension(e[0]['data-extension']);
                        size.innerText = e[0]['data-size'];
                        mime.innerText = e[0]['data-mimetype'];
                        modified.innerText = e[0]['data-date'];

                        ico_wrapper.classList.remove("hidden");
                        file_info_wrapper.classList.remove("hidden");
                        info_dimension.classList.add("hidden");
                        dimension.classList.add("hidden");
                        image_wrapper.classList.add("hidden");
                        video_wrapper.classList.add("hidden");
                        audio_wrapper.classList.add("hidden");
                        file_info_no_wrapper.classList.add("hidden");
                        multiple_items_selected.classList.add("hidden");
                        file_info_unavailable.classList.add("hidden");
                    }
                } else {
                    image_wrapper.classList.add("hidden");
                    video_wrapper.classList.add("hidden");
                    audio_wrapper.classList.add("hidden");
                    ico_wrapper.classList.add("hidden");
                    file_info_wrapper.classList.add("hidden");
                    file_info_no_wrapper.classList.add("hidden");
                    multiple_items_selected.classList.add("hidden");
                    file_info_unavailable.classList.remove("hidden");
                }
            } else if (count > 1) {
                image_wrapper.classList.add("hidden");
                video_wrapper.classList.add("hidden");
                audio_wrapper.classList.add("hidden");
                ico_wrapper.classList.add("hidden");
                file_info_wrapper.classList.add("hidden");
                file_info_no_wrapper.classList.add ("hidden");
                file_info_unavailable.classList.add("hidden");
                multiple_items_selected.classList.remove("hidden");
            }
        }

        /////////////////////////////////////////

        function readableBytes(bytes) {
            const i = Math.floor(Math.log(bytes) / Math.log(1024)),
                sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
        }

        function sprintf() {
            let args = arguments,
                string = args[0],
                i = 1;
            return string.replace(/%((%)|s|d)/g, function (m) {
                // m is the matched format, e.g. %s, %d
                let val = null;
                if (m[2]) {
                    val = m[2];
                } else {
                    val = args[i];
                    // A switch statement so that the formatter can be extended. Default is %s
                    switch (m) {
                        case '%d':
                            val = parseFloat(val);
                            if (isNaN(val)) {
                                val = 0;
                            }
                            break;
                    }
                    i++;
                }
                return val;
            });
        }

        function getFileIconExtension(ext) {
            let icon;
            switch (ext) {
                case 'gif':
                case 'jpg':
                case 'jpeg':
                case 'jpc':
                case 'png':
                case 'bmp':
                    icon = '<svg class="svg-file svg-image files-svg" viewBox="0 0 56 56"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><circle cx="18.931" cy="14.431" r="4.569" style="fill:#f3d55b"></circle><polygon points="6.5,39 17.5,39 49.5,39 49.5,28 39.5,18.5 29,30 23.517,24.517" style="fill:#88c057"></polygon></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'pdf':
                    icon = '<svg class="svg-file svg-pdf files-svg" viewBox="0 0 56 56"> <path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path d="M19.514,33.324L19.514,33.324c-0.348,0-0.682-0.113-0.967-0.326 c-1.041-0.781-1.181-1.65-1.115-2.242c0.182-1.628,2.195-3.332,5.985-5.068c1.504-3.296,2.935-7.357,3.788-10.75 c-0.998-2.172-1.968-4.99-1.261-6.643c0.248-0.579,0.557-1.023,1.134-1.215c0.228-0.076,0.804-0.172,1.016-0.172 c0.504,0,0.947,0.649,1.261,1.049c0.295,0.376,0.964,1.173-0.373,6.802c1.348,2.784,3.258,5.62,5.088,7.562 c1.311-0.237,2.439-0.358,3.358-0.358c1.566,0,2.515,0.365,2.902,1.117c0.32,0.622,0.189,1.349-0.39,2.16 c-0.557,0.779-1.325,1.191-2.22,1.191c-1.216,0-2.632-0.768-4.211-2.285c-2.837,0.593-6.15,1.651-8.828,2.822 c-0.836,1.774-1.637,3.203-2.383,4.251C21.273,32.654,20.389,33.324,19.514,33.324z M22.176,28.198 c-2.137,1.201-3.008,2.188-3.071,2.744c-0.01,0.092-0.037,0.334,0.431,0.692C19.685,31.587,20.555,31.19,22.176,28.198z M35.813,23.756c0.815,0.627,1.014,0.944,1.547,0.944c0.234,0,0.901-0.01,1.21-0.441c0.149-0.209,0.207-0.343,0.23-0.415 c-0.123-0.065-0.286-0.197-1.175-0.197C37.12,23.648,36.485,23.67,35.813,23.756z M28.343,17.174 c-0.715,2.474-1.659,5.145-2.674,7.564c2.09-0.811,4.362-1.519,6.496-2.02C30.815,21.15,29.466,19.192,28.343,17.174z M27.736,8.712c-0.098,0.033-1.33,1.757,0.096,3.216C28.781,9.813,27.779,8.698,27.736,8.712z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'docx':
                case 'doc':
                case 'odt':
                    icon = '<svg class="svg-file svg-word files-svg" viewBox="0 0 56 56"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path d="M12.5,13h6c0.553,0,1-0.448,1-1s-0.447-1-1-1h-6c-0.553,0-1,0.448-1,1S11.947,13,12.5,13z"></path><path d="M12.5,18h9c0.553,0,1-0.448,1-1s-0.447-1-1-1h-9c-0.553,0-1,0.448-1,1S11.947,18,12.5,18z"></path><path d="M25.5,18c0.26,0,0.52-0.11,0.71-0.29c0.18-0.19,0.29-0.45,0.29-0.71c0-0.26-0.11-0.52-0.29-0.71 c-0.38-0.37-1.04-0.37-1.42,0c-0.181,0.19-0.29,0.44-0.29,0.71s0.109,0.52,0.29,0.71C24.979,17.89,25.24,18,25.5,18z"></path><path d="M29.5,18h8c0.553,0,1-0.448,1-1s-0.447-1-1-1h-8c-0.553,0-1,0.448-1,1S28.947,18,29.5,18z"></path><path d="M11.79,31.29c-0.181,0.19-0.29,0.44-0.29,0.71s0.109,0.52,0.29,0.71 C11.979,32.89,12.229,33,12.5,33c0.27,0,0.52-0.11,0.71-0.29c0.18-0.19,0.29-0.45,0.29-0.71c0-0.26-0.11-0.52-0.29-0.71 C12.84,30.92,12.16,30.92,11.79,31.29z"></path><path d="M24.5,31h-8c-0.553,0-1,0.448-1,1s0.447,1,1,1h8c0.553,0,1-0.448,1-1S25.053,31,24.5,31z"></path><path d="M41.5,18h2c0.553,0,1-0.448,1-1s-0.447-1-1-1h-2c-0.553,0-1,0.448-1,1S40.947,18,41.5,18z"></path><path d="M12.5,23h22c0.553,0,1-0.448,1-1s-0.447-1-1-1h-22c-0.553,0-1,0.448-1,1S11.947,23,12.5,23z"></path><path d="M43.5,21h-6c-0.553,0-1,0.448-1,1s0.447,1,1,1h6c0.553,0,1-0.448,1-1S44.053,21,43.5,21z"></path><path d="M12.5,28h4c0.553,0,1-0.448,1-1s-0.447-1-1-1h-4c-0.553,0-1,0.448-1,1S11.947,28,12.5,28z"></path><path d="M30.5,26h-10c-0.553,0-1,0.448-1,1s0.447,1,1,1h10c0.553,0,1-0.448,1-1S31.053,26,30.5,26z"></path><path d="M43.5,26h-9c-0.553,0-1,0.448-1,1s0.447,1,1,1h9c0.553,0,1-0.448,1-1S44.053,26,43.5,26z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'xlsx':
                case 'xls':
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-excel files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path style="fill:#c8bdb8" d="M23.5,16v-4h-12v4v2v2v2v2v2v2v2v4h10h2h21v-4v-2v-2v-2v-2v-2v-4H23.5z M13.5,14h8v2h-8V14z M13.5,18h8v2h-8V18z M13.5,22h8v2h-8V22z M13.5,26h8v2h-8V26z M21.5,32h-8v-2h8V32z M42.5,32h-19v-2h19V32z M42.5,28h-19v-2h19V28 z M42.5,24h-19v-2h19V24z M23.5,20v-2h19v2H23.5z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'pptx':
                case 'ppt':
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-powerpoint files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path style="fill:#c8bdb8" d="M39.5,30h-24V14h24V30z M17.5,28h20V16h-20V28z"></path><path style="fill:#c8bdb8" d="M20.499,35c-0.175,0-0.353-0.046-0.514-0.143c-0.474-0.284-0.627-0.898-0.343-1.372l3-5 c0.284-0.474,0.898-0.627,1.372-0.343c0.474,0.284,0.627,0.898,0.343,1.372l-3,5C21.17,34.827,20.839,35,20.499,35z"></path><path style="fill:#c8bdb8" d="M34.501,35c-0.34,0-0.671-0.173-0.858-0.485l-3-5c-0.284-0.474-0.131-1.088,0.343-1.372 c0.474-0.283,1.088-0.131,1.372,0.343l3,5c0.284,0.474,0.131,1.088-0.343,1.372C34.854,34.954,34.676,35,34.501,35z"></path><path style="fill:#c8bdb8" d="M27.5,16c-0.552,0-1-0.447-1-1v-3c0-0.553,0.448-1,1-1s1,0.447,1,1v3C28.5,15.553,28.052,16,27.5,16 z"></path><rect x="17.5" y="16" style="fill:#d3ccc9" width="20" height="12"></rect></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'mov':
                case 'mpeg':
                case 'mpg':
                case 'mp4':
                case 'm4v':
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-video files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path>\<polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path d="M24.5,28c-0.166,0-0.331-0.041-0.481-0.123C23.699,27.701,23.5,27.365,23.5,27V13 c0-0.365,0.199-0.701,0.519-0.877c0.321-0.175,0.71-0.162,1.019,0.033l11,7C36.325,19.34,36.5,19.658,36.5,20 s-0.175,0.66-0.463,0.844l-11,7C24.874,27.947,24.687,28,24.5,28z M25.5,14.821v10.357L33.637,20L25.5,14.821z"></path><path d="M28.5,35c-8.271,0-15-6.729-15-15s6.729-15,15-15s15,6.729,15,15S36.771,35,28.5,35z M28.5,7 c-7.168,0-13,5.832-13,13s5.832,13,13,13s13-5.832,13-13S35.668,7,28.5,7z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'wav':
                case 'mp3':
                case 'mp2':
                case 'm4a':
                case 'aac':
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-audio files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path d="M35.67,14.986c-0.567-0.796-1.3-1.543-2.308-2.351c-3.914-3.131-4.757-6.277-4.862-6.738V5 c0-0.553-0.447-1-1-1s-1,0.447-1,1v1v8.359v9.053h-3.706c-3.882,0-6.294,1.961-6.294,5.117c0,3.466,2.24,5.706,5.706,5.706 c3.471,0,6.294-2.823,6.294-6.294V16.468l0.298,0.243c0.34,0.336,0.861,0.72,1.521,1.205c2.318,1.709,6.2,4.567,5.224,7.793 C35.514,25.807,35.5,25.904,35.5,26c0,0.43,0.278,0.826,0.71,0.957C36.307,26.986,36.404,27,36.5,27c0.43,0,0.826-0.278,0.957-0.71 C39.084,20.915,37.035,16.9,35.67,14.986z M26.5,27.941c0,2.368-1.926,4.294-4.294,4.294c-2.355,0-3.706-1.351-3.706-3.706 c0-2.576,2.335-3.117,4.294-3.117H26.5V27.941z M31.505,16.308c-0.571-0.422-1.065-0.785-1.371-1.081l-1.634-1.34v-3.473 c0.827,1.174,1.987,2.483,3.612,3.783c0.858,0.688,1.472,1.308,1.929,1.95c0.716,1.003,1.431,2.339,1.788,3.978 C34.502,18.515,32.745,17.221,31.505,16.308z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'rtf':
                case 'txt':
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-text files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path d="M12.5,13h6c0.553,0,1-0.448,1-1s-0.447-1-1-1h-6c-0.553,0-1,0.448-1,1S11.947,13,12.5,13z"></path><path d="M12.5,18h9c0.553,0,1-0.448,1-1s-0.447-1-1-1h-9c-0.553,0-1,0.448-1,1S11.947,18,12.5,18z"></path><path d="M25.5,18c0.26,0,0.52-0.11,0.71-0.29c0.18-0.19,0.29-0.45,0.29-0.71c0-0.26-0.11-0.52-0.29-0.71 c-0.38-0.37-1.04-0.37-1.42,0c-0.181,0.19-0.29,0.44-0.29,0.71s0.109,0.52,0.29,0.71C24.979,17.89,25.24,18,25.5,18z"></path><path d="M29.5,18h8c0.553,0,1-0.448,1-1s-0.447-1-1-1h-8c-0.553,0-1,0.448-1,1S28.947,18,29.5,18z"></path><path d="M11.79,31.29c-0.181,0.19-0.29,0.44-0.29,0.71s0.109,0.52,0.29,0.71 C11.979,32.89,12.229,33,12.5,33c0.27,0,0.52-0.11,0.71-0.29c0.18-0.19,0.29-0.45,0.29-0.71c0-0.26-0.11-0.52-0.29-0.71 C12.84,30.92,12.16,30.92,11.79,31.29z"></path><path d="M24.5,31h-8c-0.553,0-1,0.448-1,1s0.447,1,1,1h8c0.553,0,1-0.448,1-1S25.053,31,24.5,31z"></path><path d="M41.5,18h2c0.553,0,1-0.448,1-1s-0.447-1-1-1h-2c-0.553,0-1,0.448-1,1S40.947,18,41.5,18z"></path><path d="M12.5,23h22c0.553,0,1-0.448,1-1s-0.447-1-1-1h-22c-0.553,0-1,0.448-1,1S11.947,23,12.5,23z"></path><path d="M43.5,21h-6c-0.553,0-1,0.448-1,1s0.447,1,1,1h6c0.553,0,1-0.448,1-1S44.053,21,43.5,21z"></path><path d="M12.5,28h4c0.553,0,1-0.448,1-1s-0.447-1-1-1h-4c-0.553,0-1,0.448-1,1S11.947,28,12.5,28z"></path><path d="M30.5,26h-10c-0.553,0-1,0.448-1,1s0.447,1,1,1h10c0.553,0,1-0.448,1-1S31.053,26,30.5,26z"></path><path d="M43.5,26h-9c-0.553,0-1,0.448-1,1s0.447,1,1,1h9c0.553,0,1-0.448,1-1S44.053,26,43.5,26z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'zip':
                case 'rar':
                case 'asice':
                case 'cdoc':
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-archive files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path d="M28.5,24v-2h2v-2h-2v-2h2v-2h-2v-2h2v-2h-2v-2h2V8h-2V6h-2v2h-2v2h2v2h-2v2h2v2h-2v2h2v2h-2v2h2v2 h-4v5c0,2.757,2.243,5,5,5s5-2.243,5-5v-5H28.5z M30.5,29c0,1.654-1.346,3-3,3s-3-1.346-3-3v-3h6V29z"></path><path d="M26.5,30h2c0.552,0,1-0.447,1-1s-0.448-1-1-1h-2c-0.552,0-1,0.447-1,1S25.948,30,26.5,30z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'php':
                case 'js':
                case 'css':
                case 'json':
                case 'xml':
                case 'html':
                case 'htm':
                case 'sql':
                case 'yml':
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-code files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path d="M15.5,24c-0.256,0-0.512-0.098-0.707-0.293c-0.391-0.391-0.391-1.023,0-1.414l6-6 c0.391-0.391,1.023-0.391,1.414,0s0.391,1.023,0,1.414l-6,6C16.012,23.902,15.756,24,15.5,24z"></path><path d="M21.5,30c-0.256,0-0.512-0.098-0.707-0.293l-6-6c-0.391-0.391-0.391-1.023,0-1.414 s1.023-0.391,1.414,0l6,6c0.391,0.391,0.391,1.023,0,1.414C22.012,29.902,21.756,30,21.5,30z"></path><path d="M33.5,30c-0.256,0-0.512-0.098-0.707-0.293c-0.391-0.391-0.391-1.023,0-1.414l6-6 c0.391-0.391,1.023-0.391,1.414,0s0.391,1.023,0,1.414l-6,6C34.012,29.902,33.756,30,33.5,30z"></path><path d="M39.5,24c-0.256,0-0.512-0.098-0.707-0.293l-6-6c-0.391-0.391-0.391-1.023,0-1.414 s1.023-0.391,1.414,0l6,6c0.391,0.391,0.391,1.023,0,1.414C40.012,23.902,39.756,24,39.5,24z"></path><path d="M24.5,32c-0.11,0-0.223-0.019-0.333-0.058c-0.521-0.184-0.794-0.755-0.61-1.276l6-17 c0.185-0.521,0.753-0.795,1.276-0.61c0.521,0.184,0.794,0.755,0.61,1.276l-6,17C25.298,31.744,24.912,32,24.5,32z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                default:
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-none files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext f_10" x="28" y="51.5">' + ext + '</text></svg>';
            }
            return icon
        }

        function getTinyIconExtension(ext) {
            let icon;
            switch (ext) {
                case 'gif':
                case 'jpg':
                case 'jpeg':
                case 'jpc':
                case 'png':
                case 'bmp':
                case 'svg':
                    icon = '<svg viewBox="0 0 24 24" class="svg-icon svg-image"><path class="svg-path-image" d="M8.5,13.5L11,16.5L14.5,12L19,18H5M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19Z"</path></svg>';
                    break;
                case 'pdf':
                    icon = '<svg viewBox="0 0 24 24" class="svg-icon svg-pdf"><path class="svg-path-pdf" d="M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5C3,3.89 3.89,3 5,3H19M10.59,10.08C10.57,10.13 10.3,11.84 8.5,14.77C8.5,14.77 5,16.58 5.83,17.94C6.5,19 8.15,17.9 9.56,15.27C9.56,15.27 11.38,14.63 13.79,14.45C13.79,14.45 17.65,16.19 18.17,14.34C18.69,12.5 15.12,12.9 14.5,13.09C14.5,13.09 12.46,11.75 12,9.89C12,9.89 13.13,5.95 11.38,6C9.63,6.05 10.29,9.12 10.59,10.08M11.4,11.13C11.43,11.13 11.87,12.33 13.29,13.58C13.29,13.58 10.96,14.04 9.9,14.5C9.9,14.5 10.9,12.75 11.4,11.13M15.32,13.84C15.9,13.69 17.64,14 17.58,14.32C17.5,14.65 15.32,13.84 15.32,13.84M8.26,15.7C7.73,16.91 6.83,17.68 6.6,17.67C6.37,17.66 7.3,16.07 8.26,15.7M11.4,8.76C11.39,8.71 11.03,6.57 11.4,6.61C11.94,6.67 11.4,8.71 11.4,8.76Z"></path></svg>';
                    break;
                case 'docx':
                case 'doc':
                case 'odt':
                    icon = '<svg viewBox="0 0 24 24" class="svg-icon svg-word"><path class="svg-path-word" d="M15.5,17H14L12,9.5L10,17H8.5L6.1,7H7.8L9.34,14.5L11.3,7H12.7L14.67,14.5L16.2,7H17.9M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z"></path></svg>';
                    break;
                case 'xlsx':
                case 'xls':
                    icon = '<svg viewBox="0 0 24 24" class="svg-icon svg-excel"><path class="svg-path-excel" d="M16.2,17H14.2L12,13.2L9.8,17H7.8L11,12L7.8,7H9.8L12,10.8L14.2,7H16.2L13,12M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z"></path></svg>';
                    break;
                case 'pptx':
                case 'ppt':
                    icon = '<svg viewBox="0 0 24 24" class="svg-icon svg-powerpoint"><path class="svg-path-powerpoint" d="M9.8,13.4H12.3C13.8,13.4 14.46,13.12 15.1,12.58C15.74,12.03 16,11.25 16,10.23C16,9.26 15.75,8.5 15.1,7.88C14.45,7.29 13.83,7 12.3,7H8V17H9.8V13.4M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5C3,3.89 3.9,3 5,3H19M9.8,12V8.4H12.1C12.76,8.4 13.27,8.65 13.6,9C13.93,9.35 14.1,9.72 14.1,10.24C14.1,10.8 13.92,11.19 13.6,11.5C13.28,11.81 12.9,12 12.22,12H9.8Z"></path></svg>';
                    break;
                case 'mov':
                case 'mpeg':
                case 'mpg':
                case 'mp4':
                case 'm4v':
                    icon = '<svg viewBox="0 0 24 24" class="svg-icon svg-video"><path class="svg-path-video" d="M17,10.5V7A1,1 0 0,0 16,6H4A1,1 0 0,0 3,7V17A1,1 0 0,0 4,18H16A1,1 0 0,0 17,17V13.5L21,17.5V6.5L17,10.5Z"></path></svg>';
                    break;
                case 'wav':
                case 'mp3':
                case 'mp2':
                case 'm4a':
                case 'aac':
                    icon = '<svg viewBox="0 0 24 24" class="svg-icon svg-audio"><path class="svg-path-audio" d="M14,3.23V5.29C16.89,6.15 19,8.83 19,12C19,15.17 16.89,17.84 14,18.7V20.77C18,19.86 21,16.28 21,12C21,7.72 18,4.14 14,3.23M16.5,12C16.5,10.23 15.5,8.71 14,7.97V16C15.5,15.29 16.5,13.76 16.5,12M3,9V15H7L12,20V4L7,9H3Z"></path></svg>';
                    break;
                case 'rtf':
                case 'txt':
                    icon = '<svg viewBox="0 0 24 24" class="svg-icon svg-text"><path class="svg-path-text" d="M14,17H7V15H14M17,13H7V11H17M17,9H7V7H17M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z"></path></svg>';
                    break;
                case 'zip':
                case 'rar':
                case 'asice':
                case 'cdoc':
                    icon = '<svg viewBox="0 0 24 24" class="svg-icon svg-archive"><path class="svg-path-archive" d="M14,17H12V15H10V13H12V15H14M14,9H12V11H14V13H12V11H10V9H12V7H10V5H12V7H14M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z"></path</svg>';
                    break;
                case 'php':
                case 'js':
                case 'css':
                case 'json':
                case 'xml':
                case 'html':
                case 'htm':
                case 'sql':
                case 'yml':
                    icon = '<svg viewBox="0 0 24 24" class="svg-icon svg-code"><path class="svg-path-code" d="M14.6,16.6L19.2,12L14.6,7.4L16,6L22,12L16,18L14.6,16.6M9.4,16.6L4.8,12L9.4,7.4L8,6L2,12L8,18L9.4,16.6Z"></path></svg>';
                    break;
                default:
                    icon = '<svg viewBox="0 0 24 24" class="svg-icon svg-file_default"><path class="svg-path-file_default" d="M14,10H19.5L14,4.5V10M5,3H15L21,9V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5C3,3.89 3.89,3 5,3M5,5V19H19V12H12V5H5Z"></path></svg>';
            }
            return icon
        }
        return this;
}
})(jQuery);