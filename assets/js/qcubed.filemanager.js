(function ($) {
    $.fn.fileManager = function (options) {
        options = $.extend({
            language: null,
            path: null,
            rootPath: null,
            rootUrl: null,
            tempPath: null,
            tempUrl: null,
            dateTimeFormat: null,
            isImageListView: true,
            isListView: false,
            isBoxView: false,
            updatedHash: null,
            lockedDocuments: false,
            lockedImages: false
        }, options)

        /////////////////////////////////////////

        let currentPath = "";
        let breadcrumbsUrls = [];

        /////////////////////////////////////////

        // Resetting body
        const body = document.querySelector("body");
        body.classList.add("no-scroll");

        // Get a reference to the buttons
        const launch_start = document.querySelector(".launch-start");
        const back = document.querySelector(".back");

        const btn_image_list= document.querySelector(".btn-imageList");
        const btn_list= document.querySelector(".btn-list");
        const btn_box= document.querySelector(".btn-box");

        const search = document.querySelector("[type='search']");

        /////////////////////////////////////////

        // Get a reference to the elements
        const scroll_wrapper = document.querySelector(".scroll-wrapper");
        const dialog_wrapper = document.querySelector(".dialog-wrapper");
        const files = document.querySelector(".files");
        const media_items = document.querySelector(".media-items");
        const imageList_layout = document.querySelector(".media-items.imageList-layout");
        const list_layout = document.querySelector(".media-items.list-layout");
        const box_layout = document.querySelector(".media-items.box-layout");
        // Get a reference to the empty folder info wrapper
        const empty = document.querySelector(".empty");
        const no_results = document.querySelector(".no-results");
        const breadcrumbs = document.querySelector(".breadcrumbs");
        const search_results = document.querySelector(".search-results");
        const head = document.querySelector(".head a");
        const homelink = document.querySelector(".homelink");

        /////////////////////////////////////////

        // Button and link to trigger the corresponding events
        launch_start.addEventListener("click", fileHandler);

        head.addEventListener("click", function() {
            setTimeout(breadcrumbs.innerHTML = "");
            switchHelper();
        });

        /////////////////////////////////////////

        async function fetchLanguageJson(language) {
            //const response = await fetch(`../assets/lang/${language}.json`);
            const response = await fetch(options.path + `/lang/${language}.json`);

            if (!response.ok) {
                console.error(`Failed to fetch ${language}.json`);
                return null;
            }

            return response.json();
        }

        async function translateLanguage(language) {
            const translations = await fetchLanguageJson(language);

            if (!translations) {
                console.error(`Translations not available for ${language}`);
                return;
            }

            var elements = document.querySelectorAll('[data-lang]');

            elements.forEach(function (element) {
                var key = element.getAttribute('data-lang');
                var translation = translations[key];

                if (translation) {
                    element.innerHTML = translation;
                } else {
                    // Handle missing translation
                    element.innerHTML = "Translation not available";
                }
            });
        }

        //The default language is English (en) unless another language is selected
        options.language = options.language ? options.language : 'en';

        translateLanguage(options.language); // Set language to English

        /////////////////////////////////////////

        //fetch("../assets/php/json.php")
        fetch(options.path + '/php/json.php')
            .then((response) => {
                if(!response.ok){ // Before parsing (i.e. decoding) the JSON data,
                    // check for any errors.
                    // In case of an error, throw.
                    throw new Error("Something went wrong!");
                }
                return response.json(); // Parse the JSON data.
            })
            .then((data) => {
                process(data);
            });

        /////////////////////////////////////////

        // This function manages the clicking of different views according to the user's request

        panelHandler();

        // This little code below will hash the URL and keep the clicked location in the file manager when
        // the folder name is changed in the file manager

        if (Boolean(options.updatedHash)) {
            if (window.location.hash !== null) {
                replaceHash(options.updatedHash);
            }
        }

        /////////////////////////////////////////

        function process(data) {

            const response = data;

            /////////////////////////////////////////

            // This event listener monitors changes on the URL.
            // We use it to capture back/forward navigation in the browser.
            $(window).on('hashchange', function(){
                goto(window.location.hash);
                // We are triggering the event. This will execute
                // this function on page load, so that we show the correct folder:
            }).trigger('hashchange');

            // Listening for keyboard input on the search field.
            // We are using the "input" event which detects cut and paste
            // in addition to keyboard input.
            search.addEventListener('input', function (e) {
                const value = this.value.trim();

                // Deny input if value contains "?"
                if (value.includes('?')) {
                    this.value = value.replace('?', ''); // Remove "?"
                    return; // Do nothing more
                }

                if (search) {
                    if (value.length > 0) {
                        empty.classList.add('hidden');
                    }
                }

                if (value.length) {
                    imageList_layout.classList.add('searching');
                    list_layout.classList.add('searching');
                    box_layout.classList.add('searching');

                    homelink.classList.add('hidden');
                    breadcrumbs.classList.add('hidden');
                    search_results.classList.remove('hidden');

                    // Update the hash on every key stroke
                    window.location.hash = 'search=' + value.trim();
                } else {
                    imageList_layout.classList.remove('searching');
                    list_layout.classList.remove('searching');
                    box_layout.classList.remove('searching');

                    homelink.classList.remove('hidden');
                    breadcrumbs.classList.remove('hidden');
                    search_results.classList.add('hidden');

                    window.location.hash = encodeURIComponent(currentPath);
                }
            });

            search.addEventListener('keyup', function (e) {
                // Clicking 'ESC' button triggers focusout and cancels the search
                if (e.keyCode == 27) {
                    search.blur();
                }
            });

            search.addEventListener('focusout', function (e) {
                // Cancel the search
                if (!search.value.trim().length) {
                    window.location.hash = encodeURIComponent(currentPath);
                }
            });

            /////////////////////////////////////////

            function goto(hash)
            {
                hash = decodeURIComponent(hash).slice(1).split('=');

                if (hash.length) {
                    let rendered = '';

                    if (hash[0] === 'search') { // if hash has search in it
                        imageList_layout.classList.add('searching');
                        list_layout.classList.add('searching');
                        box_layout.classList.add('searching');

                        rendered = searchData(response, hash[1].toLowerCase());
                    } else if (hash[0].trim().length) { // if hash is some path
                        rendered = searchByPath(hash[0]);
                        if (rendered.length) {
                            currentPath = hash[0];
                            breadcrumbsUrls = generateBreadcrumbs(hash[0]);
                            render(rendered);
                        } else {
                            currentPath = hash[0];
                            breadcrumbsUrls = generateBreadcrumbs(hash[0]);
                            render(rendered);
                        }
                    } else { // if there is no hash
                        currentPath = "/";
                        breadcrumbsUrls.push("/");
                        render(searchByPath("/"));
                    }
                }
            }

            /////////////////////////////////////////

            // Splits a file path and turns it into clickable breadcrumbs

            function generateBreadcrumbs(nextDir){
                var path = nextDir.split('/').slice(0);
                for(let i = 1; i < path.length; i++) {
                    path[i] = path[i-1] + '/' + path[i];
                }
                return path;
            }

            /////////////////////////////////////////

            // Locates a file and folder by path

            function searchByPath(dir) {
                let demo = response;
                let flag = 0;

                for(let i= 0; i < demo.length; i++) {
                    if (demo[i].path === dir) {
                        flag = 1;
                        demo = demo[i].items;
                        break;
                    }
                }
                demo = flag ? demo : [];

                return demo;
            }

            function searchData(data, searchTerms) {
                const storedIds = []; // Stores data-id values of already added elements
                const storedCounts = [];

                // A function that checks if a name matches the search criteria
                function checkName(item) {
                    return item.name && item.name.toLowerCase().includes(searchTerms);
                }

                // A function that checks if data has already been added
                function isIdAlreadyAdded(id) {
                    return storedIds.includes(id);
                }

                // A recursive function that searches for folder and file names
                function searchItems(items) {
                    items.forEach(function (item, index, arr) {
                        if (checkName(item) && !isIdAlreadyAdded(item.id)) {
                            appendToImageList(arr[index]);
                            appendToList(arr[index]);
                            appendToBox(arr[index]);
                            storedIds.push(item.id);
                            storedCounts.push(1);
                        }

                        if (item.items) {
                            searchItems(item.items);
                        }
                    });
                }

                // Clear old results and make new ones
                imageList_layout.innerHTML = "";
                list_layout.innerHTML = "";
                box_layout.innerHTML = "";

                if (Array.isArray(data)) {
                    data.forEach(function (item) {
                        if (checkName(item) && !isIdAlreadyAdded(item.id)) {
                            appendToImageList(item);
                            appendToList(item);
                            appendToBox(item);
                            storedIds.push(item.id);
                            storedCounts.push(1);
                        }

                        if (item.items) {
                            searchItems(item.items);
                        }
                    });

                    unixTimeConvention();
                    clickstToLinks();
                }

                checkSearchLength(storedCounts);
            }

            /////////////////////////////////////////

            // Render the HTML for the file manager

            function render(data) {

                // Empty the old result and make the new one
                imageList_layout.innerHTML = "";
                list_layout.innerHTML = "";
                box_layout.innerHTML = "";

                checkLength(data);

                if (data.length) {
                    data.forEach(function (f) {
                        appendToImageList(f);
                        appendToList(f);
                        appendToBox(f);
                    });
                }
                unixTimeConvention();
                clickstToLinks();

                // Generate the breadcrumbs

                let url = '';

                breadcrumbsUrls.forEach(function (u, i) {

                    let name = u.split('/');
                    let filename = location.pathname.substring(location.pathname.lastIndexOf('/') + 1) + '#';

                    if (i !== breadcrumbsUrls.length - 1) {
                        url += '<a href="' + filename + u + '">' + name[name.length - 1] + '</span></a> <span class="arrow"> / </span>';
                    } else {
                        url += '<span>' + name[name.length - 1] + '</span>';
                    }
                });

                breadcrumbs.innerHTML = url;
            }
        }

        /////////////////////////////////////////

        function checkLength(arr)
        {
            if (!arr.length) {
                empty.classList.remove("hidden");
            } else {
                empty.classList.add("hidden");
            }
        }

        function checkSearchLength(arr)
        {
            if (!arr.length) {
                no_results.classList.remove("hidden");
            } else {
                no_results.classList.add("hidden");
            }
        }

        /////////////////////////////////////////

        function clickstToLinks()
        {
            // Clicking on folders // dblclick
            const links = document.querySelectorAll(".folder-a");

            links.forEach(function (event) {
                event.addEventListener("dblclick", function(e) {
                    e.preventDefault();

                    const nextDir = event.attributes.href.nodeValue;
                    breadcrumbsUrls.push(nextDir);

                    window.location.hash = encodeURIComponent(nextDir);
                    currentPath = nextDir;

                    if (search) {
                        if (search.value.trim().length > 0 || search.value.trim().length == 0) {
                            imageList_layout.classList.remove('searching');
                            list_layout.classList.remove('searching');
                            box_layout.classList.remove('searching');

                            homelink.classList.remove('hidden');
                            breadcrumbs.classList.remove('hidden');
                            search_results.classList.add('hidden');
                            search.value = '';
                        } else {
                            imageList_layout.classList.add('searching');
                            list_layout.classList.add('searching');
                            box_layout.classList.add('searching');

                            homelink.classList.add('hidden');
                            breadcrumbs.classList.add('hidden');
                            search_results.classList.remove('hidden');
                        }
                    }
                });
            });

            // Clicking on breadcrumbs

            const a_links = document.querySelectorAll(".breadcrumbs a")

            a_links.forEach(function (event) {
                event.addEventListener("click", function(e) {
                    e.preventDefault();
                    let index = a_links.index(this);
                    const nextDir = breadcrumbsUrls[index];

                    breadcrumbsUrls.length = Number(index);
                    window.location.hash = encodeURIComponent(nextDir);
                });
            });
        }

        /////////////////////////////////////////

        // Please see https://day.js.org/docs/en/display/format
        // The date format can be conveniently set $obj->DateTimeFormat = "set date format"

        function unixTimeConvention()
        {
            const events = document.querySelectorAll('.event');
            events.forEach(function (event) {
                const mtime = event.dataset.time;
                const date = dayjs.unix(mtime).format(options.dateTimeFormat);
                const dateElement = event.querySelector(".date");
                dateElement.innerText = date;
            })
        }

        /////////////////////////////////////////

        function dateHelper(mtime)
        {
            return dayjs.unix(mtime).format(options.dateTimeFormat);
        }

        /////////////////////////////////////////

        function appendToImageList(e)
        {
            const allowedExt = ['jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif', 'svg'];
            const extension = e.name.split('.').pop().toLowerCase();

            if (e.type === "dir") {
                const a = document.createElement("a");
                a.href = e.path;
                a.title = "";
                a.className = "files-a folder-a";
                a.setAttribute("data-type", "media-item");
                a.setAttribute("data-id", e.id);
                a.setAttribute("data-parent-id", e.parent_id);
                a.setAttribute("data-path", e.path);
                a.setAttribute("data-name", e.name);
                a.setAttribute("data-item-type", e.type);
                a.setAttribute("data-date", dateHelper(e.mtime));
                a.setAttribute("data-locked", e.locked_file);
                a.setAttribute("data-activities-locked", e.activities_locked);

                const svg = document.createElement("div");
                svg.className = "preview";
                svg.innerHTML = '<svg viewBox="0 0 48 48" class="svg-folder files-svg">\n' +
                    '        <path class="svg-folder-bg" d="M40 12H22l-4-4H8c-2.2 0-4 1.8-4 4v8h40v-4c0-2.2-1.8-4-4-4z"></path>\n' +
                    '        <path class="svg-folder-fg" d="M40 12H8c-2.2 0-4 1.8-4 4v20c0 2.2 1.8 4 4 4h32c2.2 0 4-1.8 4-4V16c0-2.2-1.8-4-4-4z"></path>\n' +
                    '    </svg>';
                const files_data = document.createElement("div");
                files_data.className = "files-data";
                const name = document.createElement("span");
                name.className = "name";
                name.innerHTML = e.name;
                const status = document.createElement("span");
                status.className = "status";
                const status_i = document.createElement("i");
                status_i.className = "fa fa-circle fa-lg";
                if (e.locked_file === 0) {
                    status_i.style = "color:#449d44";
                } else {
                    status_i.style = "color:#ff0000";
                }
                const status_y = document.createElement("i");
                status_y.className = "fa fa-circle fa-lg";
                status_y.style = "color:#e6a91a";
                const dimensions = document.createElement("span");
                dimensions.className = "dimensions";
                dimensions.innerText = String.fromCharCode(160);
                const ext = document.createElement("span");
                ext.className = "ext";
                ext.innerText = String.fromCharCode(160);
                const size = document.createElement("span");
                size.className = "size";
                size.innerText = String.fromCharCode(160);
                const date = document.createElement("span");
                date.className = "event";
                date.setAttribute("data-time", e.mtime);
                const dayjs = document.createElement("time");
                dayjs.className = "date";

                imageList_layout.appendChild(a);
                a.appendChild(svg);
                a.appendChild(files_data);
                files_data.appendChild(name);
                files_data.appendChild(status);
                status.appendChild(status_i);
                if (e.activities_locked === 1) {
                    status.appendChild(status_y);
                }
                files_data.appendChild(ext);
                files_data.appendChild(size);
                files_data.appendChild(dimensions);
                files_data.appendChild(date);
                date.appendChild(dayjs);

            } else if (e.type === "file") {
                const div = document.createElement("div");
                div.className = "files-a";
                div.setAttribute("data-type", "media-item");
                div.setAttribute("data-id", e.id);
                div.setAttribute("data-name", e.name);
                div.setAttribute("data-item-type", e.type);
                div.setAttribute("data-path", e.path);
                div.setAttribute("data-extension", e.extension);
                div.setAttribute("data-mime-type", e.mime_type);
                div.setAttribute("data-size", readableBytes(e.size));
                div.setAttribute("data-date", dateHelper(e.mtime));
                div.setAttribute("data-dimensions", e.dimensions);
                div.setAttribute("data-locked", e.locked_file);
                div.setAttribute("data-activities-locked", e.activities_locked);

                if (options.lockedDocuments == true) {
                    if (!allowedExt.includes(div.getAttribute("data-extension"))) {
                        div.classList.add("locked");
                    }
                }
                if (options.lockedImages == true) {
                    if (allowedExt.includes(div.getAttribute("data-extension"))) {
                        div.classList.add("locked");
                    }
                }
                const previewDiv = document.createElement("div");
                previewDiv.className = "preview";
                if (allowedExt.includes(extension)) {
                    const img = document.createElement("img");
                    if (extension !== 'svg') {
                        img.src = options.tempUrl + '/_files/thumbnail' + e.path;
                    } else {
                        img.src = options.rootUrl + e.path;
                    }
                    img.alt = e.name;
                    previewDiv.appendChild(img);
                } else {
                    previewDiv.innerHTML = getFileIconExtension(extension);
                }
                const files_data = document.createElement("div");
                files_data.className = "files-data";
                const name = document.createElement("span");
                name.className = "name";
                name.innerHTML = e.name;
                const status = document.createElement("span");
                status.className = "status";
                const status_i = document.createElement("i");
                status_i.className = "fa fa-circle fa-lg";
                if (e.locked_file === 0) {
                    status_i.style = "color:#449d44";
                } else {
                    status_i.style = "color:#ff0000";
                }
                const status_y = document.createElement("i");
                status_y.className = "fa fa-circle fa-lg";
                status_y.style = "color:#e6a91a";
                const ext = document.createElement("span");
                ext.className = "ext";
                ext.innerText = e.extension;
                const dimensions = document.createElement("span");
                if (allowedExt.includes(extension)) {
                    dimensions.className = "dimensions";
                    dimensions.innerText = e.dimensions;
                } else {
                    dimensions.className = "dimensions";
                    dimensions.innerText = String.fromCharCode(160);
                }
                const size = document.createElement("span");
                size.className = "size";
                size.innerText = readableBytes(e.size);
                const date = document.createElement("span");
                date.className = "event";
                date.setAttribute("data-time", e.mtime);
                const dayjs = document.createElement("time");
                dayjs.className = "date";

                imageList_layout.appendChild(div);
                div.appendChild(previewDiv);
                div.appendChild(files_data);
                files_data.appendChild(name);
                files_data.appendChild(status);
                status.appendChild(status_i);
                if (e.activities_locked === 1) {
                    status.appendChild(status_y);
                }
                files_data.appendChild(ext);
                files_data.appendChild(size);
                files_data.appendChild(dimensions);
                files_data.appendChild(date);
                date.appendChild(dayjs);
            }
        }

        function appendToList(e)
        {
            const allowedExt = ['jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif', 'svg'];
            const extension = e.name.split('.').pop().toLowerCase();

            if (e.type === "dir") {
                const a = document.createElement("a");
                a.href = e.path;
                a.title = "";
                a.className = "files-a folder-a";
                a.setAttribute("data-type", "media-item");
                a.setAttribute("data-id", e.id);
                a.setAttribute("data-parent-id", e.parent_id);
                a.setAttribute("data-path", e.path);
                a.setAttribute("data-name", e.name);
                a.setAttribute("data-item-type", e.type);
                a.setAttribute("data-date", dateHelper(e.mtime));
                a.setAttribute("data-locked", e.locked_file);
                a.setAttribute("data-activities-locked", e.activities_locked);

                const files_data = document.createElement("div");
                files_data.className = "files-data";
                const span = document.createElement("span");
                span.className = "icon";
                span.innerHTML = '<svg viewBox="0 0 48 48" class="svg-folder svg-icon"><path class="svg-folder-bg" d="M40 12H22l-4-4H8c-2.2 0-4 1.8-4 4v8h40v-4c0-2.2-1.8-4-4-4z"></path> <path class="svg-folder-fg" d="M40 12H8c-2.2 0-4 1.8-4 4v20c0 2.2 1.8 4 4 4h32c2.2 0 4-1.8 4-4V16c0-2.2-1.8-4-4-4z"></path></svg>';
                const name = document.createElement("span");
                name.className = "name";
                name.innerHTML = e.name;
                const status = document.createElement("span");
                status.className = "status";
                const status_i = document.createElement("i");
                status_i.className = "fa fa-circle fa-lg";
                if (e.locked_file === 0) {
                    status_i.style = "color:#449d44";
                } else {
                    status_i.style = "color:#ff0000";
                }
                const status_y = document.createElement("i");
                status_y.className = "fa fa-circle fa-lg";
                status_y.style = "color:#e6a91a";
                const ext = document.createElement("span");
                ext.className = "ext";
                ext.innerText = String.fromCharCode(160);
                const size = document.createElement("span");
                size.className = "size";
                size.innerText = String.fromCharCode(160);
                const dimensions = document.createElement("span");
                dimensions.className = "dimensions";
                dimensions.innerText = String.fromCharCode(160);
                const date = document.createElement("span");
                date.className = "event";
                date.setAttribute("data-time", e.mtime);
                const dayjs = document.createElement("time");
                dayjs.className = "date";

                list_layout.appendChild(a);
                a.appendChild(files_data);
                files_data.appendChild(span);
                files_data.appendChild(name);
                files_data.appendChild(status);
                status.appendChild(status_i);
                if (e.activities_locked === 1) {
                    status.appendChild(status_y);
                }
                files_data.appendChild(ext);
                files_data.appendChild(size);
                files_data.appendChild(dimensions);
                files_data.appendChild(date);
                date.appendChild(dayjs);

            } else if (e.type === "file") {
                const div = document.createElement("div");
                div.className = "files-a";
                div.setAttribute("data-type", "media-item");
                div.setAttribute("data-id", e.id);
                div.setAttribute("data-name", e.name);
                div.setAttribute("data-item-type", e.type);
                div.setAttribute("data-path", e.path);
                div.setAttribute("data-extension", e.extension);
                div.setAttribute("data-mime-type", e.mime_type);
                div.setAttribute("data-size", readableBytes(e.size));
                div.setAttribute("data-date", dateHelper(e.mtime));
                div.setAttribute("data-dimensions", e.dimensions);
                div.setAttribute("data-locked", e.locked_file);
                div.setAttribute("data-activities-locked", e.activities_locked);

                if (options.lockedDocuments == true) {
                    if (!allowedExt.includes(div.getAttribute("data-extension"))) {
                        div.classList.add("locked");
                    }
                }
                if (options.lockedImages == true) {
                    if (allowedExt.includes(div.getAttribute("data-extension"))) {
                        div.classList.add("locked");
                    }
                }
                const files_data = document.createElement("div");
                files_data.className = "files-data";
                const span = document.createElement("span");
                span.className = "icon";
                span.innerHTML = getTinyIconExtension(extension);
                const name = document.createElement("span");
                name.className = "name";
                name.innerHTML = e.name;
                const status = document.createElement("span");
                status.className = "status";
                const status_i = document.createElement("i");
                status_i.className = "fa fa-circle fa-lg";
                if (e.locked_file === 0) {
                    status_i.style = "color:#449d44";
                } else {
                    status_i.style = "color:#ff0000";
                }
                const status_y = document.createElement("i");
                status_y.className = "fa fa-circle fa-lg";
                status_y.style = "color:#e6a91a";
                const ext = document.createElement("span");
                ext.className = "ext";
                ext.innerText = e.extension;
                const size = document.createElement("span");
                size.className = "size";
                size.innerText = readableBytes(e.size);
                const date = document.createElement("span");
                const dimensions = document.createElement("span");
                dimensions.className = "dimensions";
                if (allowedExt.includes(extension)) {
                    dimensions.innerText = e.dimensions;
                } else {
                    dimensions.innerText = String.fromCharCode(160);
                }
                date.className = "event";
                date.setAttribute("data-time", e.mtime);
                const dayjs = document.createElement("time");
                dayjs.className = "date";

                list_layout.appendChild(div);
                div.appendChild(files_data);
                files_data.appendChild(span);
                files_data.appendChild(name);
                files_data.appendChild(status);
                status.appendChild(status_i);
                if (e.activities_locked === 1) {
                    status.appendChild(status_y);
                }
                files_data.appendChild(ext);
                files_data.appendChild(size);
                files_data.appendChild(dimensions);
                files_data.appendChild(date);
                date.appendChild(dayjs);
            }
        }

        function appendToBox(e)
        {
            const allowedExt = ['jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif', 'svg'];
            const extension = e.name.split('.').pop().toLowerCase();

            if (e.type === "dir") {
                const a = document.createElement("a");
                a.href = e.path;
                a.title = "";
                a.className = "files-a folder-a files-blocks";
                a.setAttribute("data-type", "media-item");
                a.setAttribute("data-id", e.id);
                a.setAttribute("data-parent-id", e.parent_id);
                a.setAttribute("data-path", e.path);
                a.setAttribute("data-name", e.name);
                a.setAttribute("data-item-type", e.type);
                a.setAttribute("data-date", dateHelper(e.mtime));
                a.setAttribute("data-locked", e.locked_file);
                a.setAttribute("data-activities-locked", e.activities_locked);

                const preview = document.createElement("div");
                preview.className = "preview";
                preview.innerHTML = '<svg viewBox="0 0 48 48" class="svg-folder files-svg"><path class="svg-folder-bg" d="M40 12H22l-4-4H8c-2.2 0-4 1.8-4 4v8h40v-4c0-2.2-1.8-4-4-4z"></path><path class="svg-folder-fg" d="M40 12H8c-2.2 0-4 1.8-4 4v20c0 2.2 1.8 4 4 4h32c2.2 0 4-1.8 4-4V16c0-2.2-1.8-4-4-4z"></path></svg>';
                const files_data = document.createElement("div");
                files_data.className = "files-data";
                const name = document.createElement("span");
                name.className = "name";
                name.innerHTML = e.name;
                const date = document.createElement("span");
                date.className = "event";
                date.setAttribute("data-time", e.mtime);
                const dayjs = document.createElement("time");
                dayjs.className = "date";
                const status = document.createElement("span");
                status.className = "status";
                const status_i = document.createElement("i");
                status_i.className = "fa fa-circle fa-lg";
                if (e.locked_file === 0) {
                    status_i.style = "color:#449d44";
                } else {
                    status_i.style = "color:#ff0000";
                }
                const status_y = document.createElement("i");
                status_y.className = "fa fa-circle fa-lg";
                status_y.style = "color:#e6a91a";

                box_layout.appendChild(a);
                a.appendChild(preview);
                a.appendChild(files_data);
                files_data.appendChild(name);
                files_data.appendChild(date);
                date.appendChild(dayjs);
                files_data.appendChild(status);
                status.appendChild(status_i);
                if (e.activities_locked === 1) {
                    status.appendChild(status_y);
                }

            } else if (e.type === "file") {
                const files_blocks = document.createElement("div");
                files_blocks.className = "files-a files-blocks";
                files_blocks.setAttribute("data-type", "media-item");
                files_blocks.setAttribute("data-id", e.id);
                files_blocks.setAttribute("data-name", e.name);
                files_blocks.setAttribute("data-item-type", e.type);
                files_blocks.setAttribute("data-path", e.path);
                files_blocks.setAttribute("data-extension", e.extension);
                files_blocks.setAttribute("data-mime-type", e.mime_type);
                files_blocks.setAttribute("data-size", readableBytes(e.size));
                files_blocks.setAttribute("data-date", dateHelper(e.mtime));
                files_blocks.setAttribute("data-dimensions", e.dimensions);
                files_blocks.setAttribute("data-locked", e.locked_file);
                files_blocks.setAttribute("data-activities-locked", e.activities_locked);

                if (options.lockedDocuments == true) {
                    if (!allowedExt.includes(files_blocks.getAttribute("data-extension"))) {
                        files_blocks.classList.add("locked");
                    }
                }
                if (options.lockedImages == true) {
                    if (allowedExt.includes(files_blocks.getAttribute("data-extension"))) {
                        files_blocks.classList.add("locked");
                    }
                }

                const preview = document.createElement("div");
                preview.className = "preview";
                if (allowedExt.includes(extension)) {
                    const img = document.createElement("img");
                    if (extension !== 'svg') {
                        img.src = options.tempUrl + '/_files/thumbnail' + e.path;
                    } else {
                        img.src = options.rootUrl + e.path;
                    }
                    img.alt = e.name;
                    preview.appendChild(img);
                } else {
                    preview.innerHTML = getFileIconExtension(extension);
                }
                const files_data = document.createElement("div");
                files_data.className = "files-data";
                const name = document.createElement("span");
                name.className = "name";
                name.innerHTML = e.name;
                const size = document.createElement("span");
                size.className = "size";
                size.innerText = readableBytes(e.size);
                const date = document.createElement("span");
                date.className = "event";
                date.setAttribute("data-time", e.mtime);
                const dayjs = document.createElement("time");
                dayjs.className = "date";
                const status = document.createElement("span");
                status.className = "status";
                const status_i = document.createElement("i");
                status_i.className = "fa fa-circle fa-lg";
                if (e.locked_file === 0) {
                    status_i.style = "color:#449d44";
                } else {
                    status_i.style = "color:#ff0000";
                }
                const status_y = document.createElement("i");
                status_y.className = "fa fa-circle fa-lg";
                status_y.style = "color:#e6a91a";

                box_layout.appendChild(files_blocks);
                files_blocks.appendChild(preview);
                files_blocks.appendChild(files_data);
                files_data.appendChild(name);
                files_data.appendChild(size);
                files_data.appendChild(date);
                date.appendChild(dayjs);
                files_data.appendChild(status);
                status.appendChild(status_i);
                if (e.activities_locked === 1) {
                    status.appendChild(status_y);
                }
            }
        }

        /////////////////////////////////////////

        function fileHandler()
        {
            back.classList.remove("disabled");
            back.removeAttribute("disabled", "disabled");
            switchHelper();
        }

        function switchHelper()
        {
            const file_info_no_wrapper = document.querySelector(".file-info-no-wrapper");
            const multiple_items_selected = document.querySelector(".file-info-multiple-items-selected");
            const file_info_unavailable = document.querySelector(".file-info-unavailable");
            const file_info_wrapper = document.querySelector(".file-info-wrapper");
            const image_wrapper = document.querySelector(".image-wrapper");
            const video_wrapper = document.querySelector(".video-wrapper");
            const audio_wrapper = document.querySelector(".audio-wrapper");
            const ico_wrapper = document.querySelector(".ico-wrapper");

            file_info_no_wrapper.classList.remove ("hidden");
            file_info_wrapper.classList.add("hidden");
            file_info_unavailable.classList.add("hidden");
            multiple_items_selected.classList.add("hidden");

            image_wrapper.classList.add("hidden");
            video_wrapper.classList.add("hidden");
            audio_wrapper.classList.add("hidden");
            ico_wrapper.classList.add("hidden");

            const ui_selected = Array.from(document.querySelectorAll(".media-items .ui-selected"));
            for (let i = 0, len = ui_selected.length; i < len; i++) {
                ui_selected[i].classList.remove("ui-selected");
            }
        }

        /////////////////////////////////////////

        function panelHandler()
        {
            if (options.isImageListView) {
                imageList_layout.classList.remove("hidden");
                list_layout.classList.add("hidden");
                box_layout.classList.add("hidden");
            } else if (options.isListView) {
                imageList_layout.classList.add("hidden");
                list_layout.classList.remove("hidden");
                box_layout.classList.add("hidden");
            } else if(options.isBoxView) {
                imageList_layout.classList.add("hidden");
                list_layout.classList.add("hidden");
                box_layout.classList.remove("hidden");
            }
        }

        /////////////////////////////////////////

        function replaceHash(new_hash)
        {

            if (location.hash !== new_hash || location.hash !== '#/') {
                history.replaceState(undefined, undefined, '#' + new_hash)
            }
        }

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
                case 'svg':
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