<?php

    namespace QCubed\Plugin;

    use QCubed as Q;
    use QCubed\Project\Control\FormBase;
    use QCubed\Project\Control\ControlBase;
    use QCubed\Exception\InvalidCast;
    use QCubed\Exception\Caller;
    use QCubed\Project\Application;
    use QCubed\Type;
    /**
     * Class VideoEmbed
     *
     * @property integer $SelectedImageId Default null. In the case of a selected image, the id of the image is pushed.
     *                                 as well as the id of the selected image is transferred to the database in the column
     *                                 of the selected table.
     * @property string $SelectedImagePath Default null. The path of the selected image with the file name
     * @property string $SelectedImageName Default null. The file name of the selected image
     *
     * @property string $Item
     *
     * @package QCubed\Plugin
     */

    class VideoEmbed extends VideoEmbedGen
    {
        /** @var null|string */
        protected ?string $intItem = null;

        /** @var string EmptyImagePath */
        protected string $strEmptyImagePath = QCUBED_VIDEOMANAGER_ASSETS_URL . "/images/empty-images-icon.png";

        /** @var null|integer SelectedimageId */
        protected ?int $intSelectedImageId = null;


        /**
         * Constructor method for initializing the class.
         *
         * @param ControlBase|FormBase $objParentObject The parent object, which must be an instance of ControlBase or FormBase.
         * @param string|null $strControlId An optional control ID for identifying the object.
         *
         * @throws Caller
         */
        public function __construct(ControlBase|FormBase $objParentObject, ?string $strControlId = null)
        {
            parent::__construct($objParentObject, $strControlId);

            $this->registerFiles();
        }

        /**
         * Registers necessary JavaScript and CSS files used by the application.
         *
         * This method loads JavaScript and CSS files required for the functionality
         * of the video manager. It ensures the inclusion of scripts for a video embed, custom logic, and necessary CSS styles, including Bootstrap.
         *
         * @return void
         * @throws Caller
         */
        protected function registerFiles(): void
        {
            $this->AddJavascriptFile(QCUBED_VIDEOMANAGER_ASSETS_URL . "/js/qcubed.videoembed.js");
            $this->AddJavascriptFile(QCUBED_VIDEOMANAGER_ASSETS_URL . "/js/jquery.slimscroll.js");
            $this->AddJavascriptFile(QCUBED_VIDEOMANAGER_ASSETS_URL . "/js/custom-video.js");
            $this->addCssFile(QCUBED_VIDEOMANAGER_ASSETS_URL . "/css/qcubed.mediafinder.css");
            $this->AddCssFile(QCUBED_BOOTSTRAP_CSS); // make sure they know
        }

        /**
         * Generates and returns the HTML content for the control.
         *
         * @return string The constructed HTML string for the control, including image container and templates.
         */
        protected function getControlHtml(): string
        {
            $strHtml = _nl('<div class="image-container">');
            $strHtml .= $this->chooseImageTemplate();
            $strHtml .= $this->selectedImageTemplate();
            $strHtml .= '</div>';

            return $strHtml;
        }

        /**
         * Generates an HTML string for displaying or hiding an image element
         * based on the selected image ID and alternate text availability.
         *
         * @return string The generated HTML string containing the image element.
         */
        protected function chooseImageTemplate(): string
        {
            $strHtml = '';

            if (!$this->intSelectedImageId) {
                $strHtml .= _nl(_indent('<div class="choose-image">', 1));
            } else {
                $strHtml .= _nl(_indent('<div class="choose-image hidden">', 1));
            }

            if ($this->strEmptyImageAlt) {
                $strHtml .= _nl(_indent('<img src="' . $this->strEmptyImagePath . '" alt="' . $this->strEmptyImageAlt . '" class="image img-responsive">', 2));
            } else {
                $strHtml .= _nl(_indent('<img src="' . $this->strEmptyImagePath . '" class="image img-responsive">', 2));
            }

            $strHtml .= _nl(_indent('</div>', 1));

            return $strHtml;
        }

        /**
         * Generates an HTML template for displaying the selected image, including its details and controls.
         *
         * The method constructs an HTML structure that visually represents a selected image
         * along with its properties such as ID, path, and optional name and alt text.
         * It also includes overlay controls for handling actions like deletion.
         *
         * @return string The generated HTML for the selected image template.
         */
        protected function selectedImageTemplate(): string
        {
            $strHtml = '';

            if (!$this->intSelectedImageId) {
                $strHtml .= _nl(_indent( '<div id="' . $this->ControlId . '" class="selected-image hidden">', 1));
            } else {
                $strHtml .= _nl(_indent( '<div id="' . $this->ControlId . '" class="selected-image">', 1));
            }

            if ($this->strSelectedImageAlt) {
                $strHtml .= _nl(_indent('<img src="' . $this->strSelectedImagePath . '" data-id ="' . $this->intSelectedImageId . '" data-event= "save" alt="' . $this->strSelectedImageAlt . '" class="image overlay-path img-responsive">', 2));
            } else {
                $strHtml .= _nl(_indent('<img src="' . $this->strSelectedImagePath . '" data-id ="' . $this->intSelectedImageId . '" data-event= "save" class="image overlay-path img-responsive">', 2));
            }

            $strHtml .= _nl(_indent('<div id="' . $this->ControlId . '"  class="overlay" data-id ="' . $this->intSelectedImageId . '" data-event= "delete">', 3));
            if ($this->strSelectedImageName) {
                $strHtml .= _nl(_indent('<span class="overLay-left">' . $this->strSelectedImageName . '</span>', 4));
            } else {
                $strHtml .= _nl(_indent('<span class="overLay-left"></span>', 4));
            }

            $strHtml .= _nl(_indent('<span class="overLay-right">', 4));
            $strHtml .= _nl(_indent('<svg viewBox="-15 -15 56 56" class="svg-delete files-svg">', 5));
            $strHtml .= _nl(_indent('<path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"></path>', 6));
            $strHtml .= _nl(_indent('</svg>', 5));
            $strHtml .= _nl(_indent('</span>', 4));
            $strHtml .= _nl(_indent('</div>', 2));
            $strHtml .= _nl(_indent('</div>', 1));

            return $strHtml;
        }

        /**
         * Generates and appends the JavaScript necessary for managing image selection and related actions.
         *
         * This method constructs JavaScript code for handling user interactions with image selection,
         * such as showing and hiding selected images, processing data parameters, and triggering
         * actions like saving or deleting selected images. The script binds event listeners to
         * DOM elements to facilitate these functionalities.
         *
         * @return string The parent class's end script along with the appended JavaScript for image handling.
         * @throws Caller
         */
        public function getEndScript(): string
        {
            $strJS = parent::getEndScript();

            $strCtrlJs = <<<FUNC
$(document).ready(function() {
    var choose_image = document.querySelector(".choose-image");
    var selected_image = document.querySelector(".selected-image");
    var overlay = document.querySelector(".overlay");
    var overlay_path = document.querySelector(".overlay-path");
    var overlay_left = document.querySelector(".overLay-left");
    
    function getDataParams(params) {
        var data = JSON.parse(params);
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
FUNC;
            Application::executeJavaScript($strCtrlJs, Q\ApplicationBase::PRIORITY_HIGH);

            return $strJS;
        }

        /**
         * Magic method to retrieve the value of specified properties dynamically.
         *
         * This method provides access to certain defined properties of the object, such as item details,
         * image-related paths, names, alt texts, and other configurations. If the requested property cannot
         * be found in the current class, it attempts to fetch it from the parent class.
         *
         * @param string $strName The name of the property to retrieve.
         *
         * @return mixed The value of the requested property, or an exception if the property does not exist.
         * @throws Caller
         */
        public function __get(string $strName): mixed
        {
            switch ($strName) {
                case 'Item': return $this->intItem;
                case "TempUrl": return $this->strTempUrl;
                case "EmptyImagePath": return $this->strEmptyImagePath;
                case "EmptyImageAlt": return $this->strEmptyImageAlt;
                case 'SelectedImageId': return $this->intSelectedImageId;
                case "SelectedImagePath": return $this->strSelectedImagePath;
                case "SelectedImageName": return $this->strSelectedImageName;
                case "SelectedImageAlt": return $this->strSelectedImageAlt;

                default:
                    try {
                        return parent::__get($strName);
                    } catch (Caller $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }
            }
        }

        /**
         * Overrides the magic __set method to handle dynamically setting the values of defined properties.
         *
         * This method manages property assignments and performs validation or type casting where necessary
         * for supported properties. If the property is not specifically handled, it delegates the call to the parent class.
         *
         * @param string $strName The name of the property being set.
         * @param mixed $mixValue The value being assigned to the property. The type is validated based on the property.
         *
         * @return void
         *
         * @throws InvalidCast If the value being assigned cannot be cast to the required type.
         * @throws Caller If the property name is not recognized or the parent class cannot handle the assignment.
         */
        public function __set(string $strName, mixed $mixValue): void
        {
            switch ($strName) {
                case "_Item": // Internal only. Do not use. Used by JS above to track selections.
                    try {
                        $data = Type::cast($mixValue, Type::INTEGER);
                        $this->intItem = $data;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }
                case "TempUrl":
                    try {
                        $this->strTempUrl = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "EmptyImagePath":
                    try {
                        $this->strEmptyImagePath = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "EmptyImageAlt":
                    try {
                        $this->strEmptyImageAlt = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "SelectedImageId":
                    try {
                        $this->intSelectedImageId = Type::Cast($mixValue, Type::INTEGER);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "SelectedImagePath":
                    try {
                        $this->strSelectedImagePath = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "SelectedImageName":
                    try {
                        $this->strSelectedImageName = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "SelectedImageAlt":
                    try {
                        $this->strSelectedImageAlt = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }

                default:
                    try {
                        parent::__set($strName, $mixValue);
                        break;
                    } catch (Caller $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }
            }
        }
    }