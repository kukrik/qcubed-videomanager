(function ($) {
    $.fn.croppieHandler = function (options) {
        options = $.extend({
            url: null,
            language: null,
            selectedImage: null, // default null
            theme: null,
            data: null,
            selectedType: 'square',  // Default value
            translatePlaceholder: '- Select a destination -',
            show: false
        }, options)

        // Use document.querySelector directly without a jQuery wrapper
        var modalElement = document.querySelector(".modal");
        var id  = $('#' + modalElement.id);
        var elementId = modalElement.id;

        $('.btn-crop').attr('disabled','disabled');

        // Let's parse the JSON string into a JavaScript object
        var parsedData = JSON.parse(options.data);

        var enableResize = false;  // Default false

        var cropper;
        var maxValue = 300; // Maximum value
        var selectedValue = null; // default null
        var crResizeWidth = 0;
        var crResizeHeight = 0;
        var timer;

        // Function to initialize Croppie with given parameters
        function initializeCropper(type, width, height) {
            if (cropper) {
                cropper.croppie('destroy');
            }

            var croppieOptions = {
                boundary: {
                    width: 350,
                    height: 350,
                },
                viewport: {
                    width: width,
                    height: height,
                    type: type
                },
                enableExif: true,
                enableOrientation: true,
                enableResize: enableResize // Enable or disable resizing based on the selected radio button
            };

            if (cropper) {
                cropper.croppie('destroy'); // Destroy the previous instance if it exists
            }

            cropper = $('#cropImage').croppie(croppieOptions);

            cropper.croppie('bind', { // Put here image selected from the file manager
                url: options.selectedImage,
                zoom: 0
            }).then(function() {
                console.log('Bind complete');
            });

            return cropper;
        }

        // Update viewport size on input change
        $('#viewportWidth, #viewportHeight').on('keyup', function () {
            var $this = $(this);
            var value = parseInt($this.val());

            // Check if the value exceeds the maximum value
            if (value > maxValue) {
                $this.val(maxValue);
                value = maxValue;
            }

            // If the value is NaN (eg input is empty), set it to 0 or some default value
            if (isNaN(value)) {
                value = '';
                $this.val(value);
            }

            var selectedType = $('#webVauuType').val();

            // If selectedType is circle, we synchronize the width and height
            if (selectedType === 'circle') {
                if ($this.attr('id') === 'viewportWidth') {
                    $('#viewportHeight').val(value);
                } else if ($this.attr('id') === 'viewportHeight') {
                    $('#viewportWidth').val(value);
                }
            }

            var viewportWidth = parseInt($('#viewportWidth').val());
            var viewportHeight = parseInt($('#viewportHeight').val());

            initializeCropper(selectedType, viewportWidth, viewportHeight);
        });

        // Event listener for change event
        $('.web-vauu-type').on('change', function () {
            var selectedType = $(this).val();
            var viewportWidth = parseInt($('#viewportWidth').val());
            var viewportHeight = parseInt($('#viewportHeight').val());

            if (selectedType === 'circle') {
                $('#viewportWidth').val(maxValue - 100);
                $('#viewportHeight').val(maxValue - 100);
                viewportWidth = viewportHeight = maxValue - 100;
            }

            initializeCropper(selectedType, viewportWidth, viewportHeight);
        });

        // Set the event handler to the enable-type checkbox
        $("#enable-type input[type=checkbox]").on('change', function() {
            var selectedType = $('#webVauuType').val();
            var viewportWidth = parseInt($('#viewportWidth').val());
            var viewportHeight = parseInt($('#viewportHeight').val());

            if ($(this).is(":checked")) {
                enableResize = true;
                $('#viewportWidth').attr('readonly','readonly');
                $('#viewportHeight').attr('readonly','readonly');
            } else {
                enableResize = false;
                $('#viewportWidth').removeAttr('readonly','readonly');
                $('#viewportHeight').removeAttr('readonly','readonly');
            }

            if (selectedType === 'circle') {
                $('#viewportWidth').val(maxValue - 100).trigger('change');
                $('#viewportHeight').val(maxValue - 100).trigger('change');
                viewportWidth = viewportHeight = maxValue - 100;
            }

            // Run according to enableResize
            initializeHandlers();

            // Define initializeCropper
            initializeCropper(selectedType, viewportWidth, viewportHeight);
        });

        // A function that determines how events are handled according to the enableResize value
        function initializeHandlers() {
            if (enableResize) {
                // Run checkResize and set the window's resize event handler
                $(window).on('resize', checkResize);
                timer = setInterval(checkResize, 10);
                checkResize();
            } else {
                // Remove window resize event handler and setInterval
                $(window).off('resize', checkResize);
                clearInterval(timer);
            }
        }

        function checkResize() {
            var selectedType = $('#webVauuType').val();
            var resizerVertical = $('.cr-resizer-vertical');
            var crViewport = $('.cr-viewport');
            var crResizer = $('.cr-resizer');
            var newWidth = crResizer.css('width');
            var newHeight = crResizer.css('height');

            if (selectedType === 'circle') {
                resizerVertical.hide();

                if (newHeight = newWidth) {
                    newHeight = newWidth;

                    crResizer.css('width', newWidth);
                    crResizer.css('height', newHeight);
                    crViewport.css('width', newWidth);
                    crViewport.css('height', newHeight);

                } else if (newWidth != crResizeWidth || newHeight != crResizeHeight) {
                    resizerVertical.show();

                    crResizeHeight = newHeight;
                    crResizeWidth = newWidth;
                }
            }

            $('#viewportWidth').val(newWidth);
            $('#viewportHeight').val(newHeight);
        }

        // Initial initialization
        initializeHandlers();

        $('.rotate-left, .rotate-right').on('click', function (ev) {
            cropper.croppie('rotate', parseInt($(this).data('deg')));
        });

        // Function to get folderId based on the selected value
        function getFolderIdById(data, selectedId) {
            var item = data.find(function(element) {
                return element.id === selectedId;
            });
            return item ? item.folderId : null;
        }

        // Event listener for Select2 change event
        $('.web-vauu-destination').on('change', function() {
            selectedValue = $(this).val();
            if (!selectedValue) {
                $('.btn-crop').attr('disabled','disabled');
            } else {
                $('.btn-crop').removeAttr('disabled');
            }
        });

        ////////////////////////////////////

        function formatResult(node) {
            var $result = $('<span style="padding-left:' + (20 * node.level) + 'px;">' + node.text + '</span>');
            return $result;
        };

        // Initialize the cropper when the modal is shown
        id.on('shown.bs.modal', function () {

            qcubed.recordControlModification(elementId + "_ctl", "_IsOpen", true);

            var viewportWidth = parseInt($('#viewportWidth').val());
            var viewportHeight = parseInt($('#viewportHeight').val());

            initializeCropper(options.selectedType, viewportWidth, viewportHeight);

            // Initialize Select2
            $('#webVauuType').select2({
                theme: options.theme,
                language: options.language,
                minimumResultsForSearch: -1,

                templateResult: function (state) {
                    if (!state.id) {
                        return state.text;
                    }
                    var $state = $('<span><i class="fa fa-' + state.id + ' fa-lg" style="color:#337ab7;line-height:0.6;"></i> ' + state.text + '</span>');
                    return $state;
                },
                templateSelection: function (state) {
                    if (!state.id) {
                        return state.text;
                    }
                    var $state = $('<span><i class="fa fa-' + state.id + ' fa-lg" style="color:#337ab7;line-height:0.6;"></i> ' + state.text + '</span>');
                    return $state;
                }
            });

            //parsedData.sort((a, b) => a.id.localeCompare(b.id));
            //console.log(parsedData);

            $('.web-vauu-destination').select2({
                theme: options.theme,
                language: options.language,
                placeholder: options.translatePlaceholder,
                minimumResultsForSearch: -1,
                data: parsedData,
                templateResult: formatResult,
                templateSelection: formatResult
            });
        })

        // Optional: Destroy the cropper when the modal is hidden to clean up
        id.on('hidden.bs.modal', function () {
            qcubed.recordControlModification(elementId + "_ctl", "_IsOpen", false);

            var cropper = $('#cropImage');
            if (cropper) {
                cropper.croppie('destroy');
                cropper = null;
            }

            $('.web-vauu-destination').select2('destroy'); // Destroy Select2 instance

            // Everything is reset to default
            $(window).off('resize', checkResize);
            clearInterval(timer);
            enableResize = false;

            $('#viewportWidth').val(250).trigger('change');
            $('#viewportHeight').val(250).trigger('change');
            $('#webVauuType').val('square').trigger('change');
            $('.web-vauu-destination').val('').trigger('change');
            $("#enable-type input[type=checkbox]").prop('checked', false);
            $('#viewportWidth').removeAttr('readonly','readonly');
            $('#viewportHeight').removeAttr('readonly','readonly');
        });

        /////////////////////////////////////

        var finalPath = null;

        $('.btn-crop').on('click', function () {
            var btnCrop = $(this); // A reference to a button

            $('#cropImage').croppie('result', {
                type: 'canvas',
                format: 'png',
                size: 'original'
            }).then(function (resp) {
                id.modal('hide');

                var pathSplit = options.selectedImage.split('\\').pop().split('/').pop();
                var fileName = pathSplit.substr(0, pathSplit.lastIndexOf("."));

                var xhr = new XMLHttpRequest();
                var data = new FormData();

                var folderId = getFolderIdById(parsedData, selectedValue);

                data.append("cropImage", resp);
                data.append("fileName", fileName);
                data.append("relativePath", selectedValue);
                data.append("folderId", folderId);
                xhr.open('POST', options.url, true);

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        finalPath = response.path;

                        if (finalPath) {
                            qcubed.recordControlModification(elementId, "_finalPath", finalPath);
                        }

                        btnCrop.data('event', true); // Change the event correctly
                        changeObject(btnCrop); // Pass the button to the function
                    }
                };

                xhr.send(data);
            });

            function changeObject(btnCrop) {
                if (btnCrop.data('event') === true) {
                    qcubed.recordControlModification(btnCrop, "_IsChangeObject", true);
                }

                var ChangeObjectEvent = $.Event("changeobject");
                btnCrop.trigger(ChangeObjectEvent);
            }
        });


        return this;
    }
})(jQuery);