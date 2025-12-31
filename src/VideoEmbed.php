<?php

    namespace QCubed\Plugin;

    use QCubed\ApplicationBase;
    use QCubed\Project\Control\FormBase;
    use QCubed\Project\Control\ControlBase;
    use QCubed\Exception\InvalidCast;
    use QCubed\Exception\Caller;
    use QCubed\Project\Application;
    use QCubed\Type;
    /**
     * Class VideoEmbed
     *
     * @property string $EmptyVideoPath Default predefined image can be overridden and replaced with another image if desired
     * @property string $EmptyVideoAlt Default null. The recommendation is to add the following text: "Choose a video"
     * @property integer $SelectedVideoId Default null. In the case of a selected video, the id of the video is pushed.
     *                                 as well as the id of the selected video is transferred to the database in the column
     *                                 of the selected table.
     * @property string $SelectedVideoEmbed  Default null. For the selected video, the video embed will be pulled here.
     * @property string $SelectedVideoAlt Default null. The recommendation is to add the following text: "Selected video"
     *
     * @property string $RemoveAssociation Default "Remove association"
     * @property string $SaveItem
     * @property array $SaveItems
     * @property string $DeleteItem
     *
     * @package QCubed\Plugin
     */

    class VideoEmbed extends VideoEmbedGen
    {
        protected ?string $intSaveItem = null;
        protected ?array $arySaveItems = null;
        protected ?string $intDeleteItem = null;

        /** @var string EmptyVideoPath */
        protected string $strEmptyVideoPath = QCUBED_VIDEOMANAGER_ASSETS_URL . "/images/empty-videos-icon.png";
        /** @var null|string EmptyVideoAlt */
        protected ?string $strEmptyVideoAlt = null;
        /** @var null|integer SelectedVideoId */
        protected ?int $intSelectedVideoId = null;
        /** @var null|string SelectedVideoEmbed */
        protected ?string $strSelectedVideoEmbed = null;
        /** @var null|string SelectedVideoAlt */
        protected ?string $strSelectedVideoAlt = null;
        /** @var string RemoveAssociation */
        protected string $strRemoveAssociation = "Remove association";

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
            $this->addCssFile(QCUBED_VIDEOMANAGER_ASSETS_URL . "/css/qcubed.videoembed.css");
            $this->AddCssFile(QCUBED_BOOTSTRAP_CSS); // make sure they know
        }

        /**
         * Generates and returns the HTML content for the control.
         *
         * @return string The constructed HTML string for the control, including image container and templates.
         */
        protected function getControlHtml(): string
        {
            $strHtml = _nl('<div class="video-container">');
            $strHtml .= $this->chooseVideoTemplate();
            $strHtml .= $this->selectedVideoTemplate();
            $strHtml .= '</div>';

            return $strHtml;
        }

        /**
         * Generates an HTML string for displaying or hiding an image element
         * based on the selected image ID and alternate text availability.
         *
         * @return string The generated HTML string containing the image element.
         */
        protected function chooseVideoTemplate(): string
        {
            $strHtml = '';

            if (!$this->intSelectedVideoId) {
                $strHtml .= _nl(_indent('<div class="choose-video">', 1));
            } else {
                $strHtml .= _nl(_indent('<div class="choose-video hidden">', 1));
            }

            if ($this->strEmptyVideoAlt) {
                $strHtml .= _nl(_indent('<img src="' . $this->strEmptyVideoPath . '" alt="' . $this->strEmptyVideoAlt . '" class="image img-responsive">', 2));
            } else {
                $strHtml .= _nl(_indent('<img src="' . $this->strEmptyVideoPath . '" class="image img-responsive">', 2));
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

        protected function selectedVideoTemplate(): string
        {
            $strHtml = '';

            $strDataId = $this->intSelectedVideoId ? (string)$this->intSelectedVideoId : '';
            $strHiddenClass = $this->intSelectedVideoId ? '' : ' hidden';

            $strHtml .= _nl(_indent(
                '<div id="' . $this->ControlId . '" class="selected-video' . $strHiddenClass . '" data-id="' . $strDataId . '" data-event="save">',
                1
            ));

            if ($this->strSelectedVideoEmbed) {
                $strHtml .= _nl(_indent('<div class="embed-responsive embed-responsive-16by9">', 2));
                $strHtml .= _nl(_indent(" $this->strSelectedVideoEmbed ", 3));
                $strHtml .= _nl(_indent('</div>', 2));

                $strHtml .= _nl(_indent(
                    '<div class="selected-overlay" data-id="' . $strDataId . '" data-event="edit"></div>',
                    2
                ));
            }

            $strHtml .= _nl(_indent('</div>', 1));

            $strHtml .= _nl(_indent(
                '<div class="delete-wrapper' . $strHiddenClass . '" data-id="' . $strDataId . '">',
                1
            ));
            $strHtml .= _nl(_indent(
                '<div class="delete-overlay" data-id="' . $strDataId . '" data-event="delete">',
                2
            ));
            $strHtml .= _nl(_indent('<span class="overLay-right" aria-label="Eemalda seos">', 3));
            $strHtml .= _nl(_indent('<svg viewBox="-15 -15 56 56" class="svg-delete files-svg" focusable="false" aria-hidden="true">', 4));
            $strHtml .= _nl(_indent('<path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"></path>', 5));
            $strHtml .= _nl(_indent('</svg>', 4));
            $strHtml .= _nl(_indent('</span>', 3));
            $strHtml .= _nl(_indent('</div>', 2));
            $strHtml .= _nl(_indent('</div>', 1));

            return $strHtml;
        }



//        protected function selectedVideoTemplate(): string
//        {
//            $strHtml = '';
//
//            $strDataId = $this->intSelectedVideoId ? (string)$this->intSelectedVideoId : '';
//            $strHiddenClass = $this->intSelectedVideoId ? '' : ' hidden';
//
//            $strHtml .= _nl(_indent(
//                '<div id="' . $this->ControlId . '" class="selected-video' . $strHiddenClass . '" data-id="' . $strDataId . '" >',
//                1
//            ));
//
//            if ($this->strSelectedVideoEmbed) {
//                $strHtml .= _nl(_indent('<div class="embed-responsive embed-responsive-16by9">', 2));
//                $strHtml .= _nl(_indent(" $this->strSelectedVideoEmbed ", 3));
//                $strHtml .= _nl(_indent('</div>', 2));
//
//                $strHtml .= _nl(_indent(
//                    '<div class="selected-overlay" data-id="' . $strDataId . '" data-event="edit"></div>',
//                    2
//                ));
//            }
//
//            $strHtml .= _nl(_indent('</div>', 1));
//
//            $strHtml .= _nl(_indent(
//                '<div class="delete-wrapper' . $strHiddenClass . '" data-id="' . $strDataId . '">',
//                1
//            ));
//            $strHtml .= _nl(_indent(
//                '<div class="delete-overlay" data-id="' . $strDataId . '" data-event="delete">',
//                2
//            ));
//            $strHtml .= _nl(_indent('<span class="overLay-right" aria-label="Eemalda seos">', 3));
//            $strHtml .= _nl(_indent('<svg viewBox="-15 -15 56 56" class="svg-delete files-svg" focusable="false" aria-hidden="true">', 4));
//            $strHtml .= _nl(_indent('<path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"></path>', 5));
//            $strHtml .= _nl(_indent('</svg>', 4));
//            $strHtml .= _nl(_indent('</span>', 3));
//            $strHtml .= _nl(_indent('</div>', 2));
//            $strHtml .= _nl(_indent('</div>', 1));
//
//            return $strHtml;
//        }

        /**
         * Generates and returns the necessary JavaScript code to manage video-related UI interactions.
         *
         * This method constructs client-side JavaScript functionality that handles the behaviors of
         * video selection and deletion in a web-based interface. It ensures proper communication
         * between the frontend and backend by triggering appropriate events and sending modifications.
         *
         * @return string The finalized JavaScript code to be executed for video handling.
         * @throws Caller
         */

        public function getEndScript(): string
        {
            $strJS = parent::getEndScript();

            $strCtrlJs = <<<FUNC
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
 
        if (id && embed) {
            choose_video.classList.add('hidden');
            selected_video.classList.remove('hidden');
            delete_wrapper.classList.remove('hidden');
            //embed_wrap.innerHTML = embed;
            selected_video.setAttribute('data-id', id);
            delete_wrapper.setAttribute('data-id', id);
            delete_overlay.setAttribute('data-id', id);
        } else {
            choose_video.classList.remove('hidden');
            selected_video.classList.add('hidden');
            delete_wrapper.classList.add('hidden');
            //embed_wrap.innerHTML = '';
            selected_video.setAttribute('data-id', '');
            delete_wrapper.setAttribute('data-id', '');
            delete_overlay.setAttribute('data-id', '');
        }

        videoSave();
    }

    window.getVideoParams = getVideoParams;

    videoSave = function() {
        var selected_video = $(".selected-video");
        selected_video.on("videosave", function(event) {
            if (selected_video.data('id') !== "") {
                console.log("videosave event fired, ID=", selected_video.data('id'));
                qcubed.recordControlModification('$this->ControlId', '_SaveItem', selected_video.data('id'));
              
                
            }
        });

        var VideoSaveEvent = $.Event("videosave");
        selected_video.trigger(VideoSaveEvent);
    }

    $(".delete-overlay").on("click", function() {
        choose_video.classList.remove('hidden');
        selected_video.classList.add('hidden');
        delete_wrapper.classList.add('hidden');
        //embed_wrap.innerHTML = '';
        selected_video.setAttribute('data-id', '');
        delete_wrapper.setAttribute('data-id', '');
        delete_overlay.setAttribute('data-id', '');

        videoDelete();
    });

    videoDelete = function() {
        var delete_video = $(delete_overlay);
        delete_video.on("videodelete", function(event) {
            if (delete_video.data('id') !== "" && delete_video.data('event') === 'delete') {
                qcubed.recordControlModification('$this->ControlId', '_DeleteItem', delete_video.data('id'));
            }
        });

        var VideoDeleteEvent = $.Event("videodelete");
        delete_video.trigger(VideoDeleteEvent);
    }
});
FUNC;
            Application::executeJavaScript($strCtrlJs, ApplicationBase::PRIORITY_HIGH);

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
         * @throws \Exception
         */
        public function __get(string $strName): mixed
        {
            switch ($strName) {
                case 'SaveItem': return $this->intSaveItem;
                case 'SaveItems': return $this->arySaveItems;
                case 'DeleteItem': return $this->intDeleteItem;
                case "EmptyVideoPath": return $this->strEmptyVideoPath;
                case "EmptyVideoAlt": return $this->strEmptyVideoAlt;
                case 'SelectedVideoId': return $this->intSelectedVideoId;
                case "SelectedVideoEmbed": return $this->strSelectedVideoEmbed;
                case "SelectedVideoAlt": return $this->strSelectedVideoAlt;
                case "RemoveAssociation": return $this->strRemoveAssociation;

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
         * for supported properties. If the property is not specifically handled, it delegates the call to the parent
         * class.
         *
         * @param string $strName The name of the property being set.
         * @param mixed $mixValue The value being assigned to the property. The type is validated based on the
         *     property.
         *
         * @return void
         *
         * @throws InvalidCast If the value being assigned cannot be cast to the required type.
         * @throws Caller If the property name is not recognized or the parent class cannot handle the assignment.
         * @throws \Exception
         */
        public function __set(string $strName, mixed $mixValue): void
        {
            switch ($strName) {
                case "_SaveItem": // Internal only. Do not use. Used by JS above to track selections.
                    try {
                        $data = Type::cast($mixValue, Type::STRING);
                        $this->intSaveItem = $data;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case '_SaveItems': // Internal only. Do not use. Used by JS above to track selections.

                    try {
                        if (is_string($mixValue)) {
                            $jsonData = json_decode($mixValue, true);

                            if (json_last_error() !== JSON_ERROR_NONE) {
                                throw new InvalidCast('Invalid JSON in _SaveItems: ' . json_last_error_msg());
                            }
                        } elseif (is_array($mixValue)) {
                            $jsonData = $mixValue;
                        } else {
                            throw new InvalidCast('Unsupported type for _SaveItems: ' . gettype($mixValue));
                        }

                        $this->arySaveItems = Type::cast($jsonData, Type::ARRAY_TYPE);
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }
                    break;

                case "_DeleteItem": // Internal only. Do not use. Used by JS above to track selections.
                    try {
                        $data = Type::cast($mixValue, Type::STRING);
                        $this->intDeleteItem = $data;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }
                case "EmptyVideoPath":
                    try {
                        $this->strEmptyVideoPath = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "EmptyVideoAlt":
                    try {
                        $this->strEmptyVideoAlt = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "SelectedVideoId":
                    try {
                        $this->intSelectedVideoId = Type::Cast($mixValue, Type::INTEGER);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "SelectedVideoEmbed":
                    try {
                        $this->strSelectedVideoEmbed = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "SelectedVideoAlt":
                    try {
                        $this->strSelectedVideoAlt = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "RemoveAssociation":
                    try {
                        $this->strRemoveAssociation = Type::Cast($mixValue, Type::STRING);
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