<?php
    require_once('qcubed.inc.php');
    require_once ('../src/FileInfo.php');
    require_once ("../src/DestinationInfo.class.php");

    error_reporting(E_ALL); // Error engine - always ON!
    ini_set('display_errors', TRUE); // Error display - OFF in production env or real server
    ini_set('log_errors', TRUE); // Error logging

    use QCubed as Q;
    use QCubed\Bootstrap as Bs;
    use QCubed\Event\Change;
    use QCubed\Event\Click;
    use QCubed\Event\DialogButton;
    use QCubed\Folder;
    use QCubed\Html;
    use QCubed\QString;
    use QCubed\Project\Control\FormBase as Form;
    use QCubed\Action\ActionParams;
    use QCubed\Project\Application;
    use QCubed\Action\Ajax;
    use QCubed\Jqui\Event\SelectableStop;
    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;
    use Random\RandomException;
    use QCubed\Database\Exception\UndefinedPrimaryKey;

    /**
     * Class DialogForm
     *
     * Represents a form designed for file management and upload functionalities.
     * It includes various modals and components that facilitate operations such as
     * file uploading, renaming, moving, copying, deleting, and image cropping.
     * The form also integrates validation and error-handling features.
     *
     * Key attributes and components:
     * - Modals for providing feedback and handling specific actions (e.g., adding folders, cropping images).
     * - File upload handler to manage the uploading process with support for configurations (e.g., max file size).
     * - Buttons and labels for user interaction and form navigation.
     * - Text boxes and dropdowns for user inputs related to file and folder operations.
     * - Arrays and properties to manage file and directory states, allowed file formats, and temporary storage during interactions.
     *
     * This class extends the `Form` class and implements additional functionalities specific to file operations.
     */
    class DialogForm extends Form
    {
        protected Bs\Modal $dlgModal1; // A corrupted table "folders" in the database or folder "upload" in the file system! ...
        protected Bs\Modal $dlgModal2; // Sorry, files cannot be added to this reserved folder! ...
        protected Bs\Modal $dlgModal3; // Please choose only a specific folder to upload files!
        protected Bs\Modal $dlgModal4; // Cannot select multiple folders to upload files!
        protected Bs\Modal $dlgModal5; // Please check if the destination is correct!
        protected Bs\Modal $dlgModal6; // Sorry, a new folder cannot be added to this reserved folder! ...
        protected Bs\Modal $dlgModal7; // Please select only one folder to create a new folder in!
        protected Bs\Modal $dlgModal8; // Please check if the destination is correct!
        protected Bs\Modal $dlgModal9; // Different comments are available depending on the user's behavior,
        // associated with helper functions or helper classes related to this modal
        protected Bs\Modal $dlgModal10; // A new folder created successfully!
        protected Bs\Modal $dlgModal11; // Failed to create a new folder!
        protected Bs\Modal $dlgModal12; // Sorry, this reserved folder or file cannot be renamed! ...
        protected Bs\Modal $dlgModal13; // Please select a folder or file!
        protected Bs\Modal $dlgModal14; // Please select only one folder or file to rename!
        protected Bs\Modal $dlgModal15; // Different comments are available depending on the user's behavior,
        // associated with helper functions or helper classes related to this modal
        protected Bs\Modal $dlgModal16; // Folder name changed successfully!
        protected Bs\Modal $dlgModal17; // Failed to rename a folder!
        protected Bs\Modal $dlgModal18; // File name changed successfully!
        protected Bs\Modal $dlgModal19; // Failed to rename a file!
        protected Bs\Modal $dlgModal20; // Please select a specific folder(s) or file(s)!
        protected Bs\Modal $dlgModal21; // It is not possible to copy the main directory!
        protected Bs\Modal $dlgModal22; // Different comments are available depending on the user's behavior,
        // associated with helper functions or helper classes related to this modal
        protected Bs\Modal $dlgModal23; // Selected files and folders have been copied successfully!
        protected Bs\Modal $dlgModal24; // Error while copying items!
        protected Bs\Modal $dlgModal25; // Sorry, this reserved folder or file cannot be deleted!
        protected Bs\Modal $dlgModal26; // It is not possible to delete the main directory!
        protected Bs\Modal $dlgModal27; // Different comments are available depending on the user's behavior,
        // associated with helper functions or helper classes related to this modal
        protected Bs\Modal $dlgModal28; // The selected files and folders have been successfully deleted!
        protected Bs\Modal $dlgModal29; // Error while deleting items!
        protected Bs\Modal $dlgModal30; // Error while deleting items!
        protected Bs\Modal $dlgModal31; // Sorry, this reserved folder or file cannot be moved! ...
        protected Bs\Modal $dlgModal32; // Different comments are available depending on the user's behavior,
        // associated with helper functions or helper classes related to this modal
        protected Bs\Modal $dlgModal33; // The selected files and folders have been successfully moved!
        protected Bs\Modal $dlgModal34; // Error while moving items!
        protected Bs\Modal $dlgModal35; // Sorry, be cannot insert into a reserved file! ...

        protected Bs\Modal $dlgModal40; // Please select an image!
        protected Bs\Modal $dlgModal41; // Please select only one image to crop! ...
        protected Bs\Modal $dlgModal42; // Please select only one image to crop!
        protected Bs\Modal $dlgModal43; // Image cropping succeeded!
        protected Bs\Modal $dlgModal44; // Image cropping failed!
        protected Bs\Modal $dlgModal45; // The image is invalid for cropping!
        protected Bs\Modal $dlgModal46; // Sorry, be cannot crop a reserved file!
        protected Bs\Modal $dlgModal47; // CSRF Token is invalid

        protected Q\Plugin\FileUploadHandler $objUpload;
        protected Q\Plugin\FileManager $objManager;
        protected Q\Plugin\FilePopupCroppie $dlgPopup;
        protected Q\Plugin\FileInfo $objInfo;
        protected Q\Plugin\Label $lblSearch;
        protected Q\Plugin\Label $objHomeLink;

        protected Q\Plugin\BsFileControl $btnAddFiles;
        protected Bs\Button $btnAllStart;
        protected Bs\Button $btnAllCancel;
        protected Bs\Button $btnBack;
        protected Bs\Button $btnDone;

        protected Bs\Button $btnUploadStart;
        protected Bs\Button $btnAddFolder;
        protected Bs\Button $btnRefresh;
        protected Bs\Button $btnRename;
        protected Bs\Button $btnCrop;
        protected Bs\Button $btnCopy;
        protected Bs\Button $btnDelete;
        protected Bs\Button $btnMove;
        protected Bs\Button $btnDownload;

        protected Bs\RadioList $lstSize;
        protected Bs\Button $btnInsert;
        protected Bs\Button $btnCancel;

        protected Bs\Button $btnImageListView;
        protected Bs\Button $btnListView;
        protected Bs\Button $btnBoxView;
        protected Bs\TextBox $txtFilter;

        protected Bs\TextBox $txtAddFolder;
        protected Q\Plugin\Label $lblError;
        protected Q\Plugin\Label $lblSameName;
        protected Q\Plugin\Label $lblRenameName;
        protected Q\Plugin\Label $lblDirectoryError;
        protected Bs\TextBox $txtRename;

        protected Q\Plugin\Label $lblDestinationError;
        protected Q\Plugin\Label $lblCourseTitle;
        protected Q\Plugin\Label $lblCoursePath;
        protected Q\Plugin\Label $lblCopyingTitle;
        protected Q\Plugin\Select2 $dlgCopyingDestination;

        protected Q\Plugin\Label $lblMovingError;
        protected Q\Plugin\Label $lblMoveInfo;
        protected Q\Plugin\Label $lblMovingDestinationError;
        protected Q\Plugin\Label $lblMovingCourseTitle;
        protected Q\Plugin\Label $lblMovingCoursePath;
        protected Q\Plugin\Label $lblMovingTitle;
        protected Q\Plugin\Select2 $dlgMovingDestination;

        protected Q\Plugin\Label $lblDeletionWarning;
        protected Q\Plugin\Label $lblDeletionInfo;
        protected Q\Plugin\Label $lblDeleteError;
        protected Q\Plugin\Label $lblDeleteInfo;
        protected Q\Plugin\Label $lblDeleteTitle;
        protected Q\Plugin\Label $lblDeletePath;

        protected array $arrSomeArray = [];
        protected array $tempItems = [];
        protected array $tempSelectedItems = [];
        protected int $objLockedFiles = 0;
        protected array $objLockedDirs = [];

        protected ?int $intDataId = null;
        protected ?string $strDataName = "";
        protected ?string $strDataPath = "";
        protected ?string $strDataExtension = "";
        protected ?string $strDataType = "";
        protected ?int $intDataLocked = 0;
        protected string $strNewPath;
        protected int $intStoredChecks = 0;
        protected array $arrAllowed = array('jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif', 'svg');
        protected array $tempFolders = array('thumbnail', 'medium', 'large');
        protected array $arrCroppieTypes = array('jpg', 'jpeg', 'png');

        protected ?bool $blnMove = false;

        /**
         * Initializes and configures the form components for file management and upload.
         *
         * @return void
         * @throws Caller
         */
        protected function formCreate(): void
        {
            parent::formCreate();

            $this->objUpload = new Q\Plugin\FileUploadHandler($this);
            $this->objUpload->Language = "et"; // Default en
            //$this->objUpload->ShowIcons = true; // Default false
            // $this->objUpload->AcceptFileTypes = ['gif', 'jpg', 'jpeg', 'png', 'pdf', 'ppt', 'docx', 'mp4']; // Default null
            //$this->objUpload->MaxNumberOfFiles = 5; // Default null
            //$this->objUpload->MaxFileSize = 1024 * 1024 * 2; // 2 MB // Default null
            //$this->objUpload->MinFileSize = 500000; // 500 kb // Default null
            //$this->objUpload->ChunkUpload = false; // Default true
            //$this->objUpload->MaxChunkSize = 1024 * 1024; // 10 MB // Default 5 MB
            //$this->objUpload->LimitConcurrentUploads = 5; // Default 2
            $this->objUpload->Url = 'php/upload.php'; // Default 'php/upload.php'
            //$this->objUpload->PreviewMaxWidth = 120; // Default 80
            //$this->objUpload->PreviewMaxHeight = 120; // Default 80
            //$this->objUpload->WithCredentials = true; // Default false
            $this->objUpload->UseWrapper = false;

            $this->objManager = new Q\Plugin\FileManager($this);
            $this->objManager->Language = 'et'; // Default en
            $this->objManager->RootPath = APP_UPLOADS_DIR;
            $this->objManager->RootUrl = APP_UPLOADS_URL;
            $this->objManager->TempPath = APP_UPLOADS_TEMP_DIR;
            $this->objManager->TempUrl = APP_UPLOADS_TEMP_URL;
            $this->objManager->DateTimeFormat = 'DD.MM.YYYY HH:mm:ss';
            //$this->objManager->LockedDocuments = true;
            // $this->objManager->LockedImages = true;
            $this->objManager->UseWrapper = false;
            $this->objManager->addAction(new SelectableStop(), new Ajax ('selectable_stop'));

            $this->dlgPopup = new Q\Plugin\FilePopupCroppie($this);
            $this->dlgPopup->Url = "php/crop_upload.php";
            $this->dlgPopup->Language = "et";
            $this->dlgPopup->TranslatePlaceholder = t("- Select a destination -");
            $this->dlgPopup->Theme = "web-vauu";
            $this->dlgPopup->HeaderTitle = t("Crop image");
            $this->dlgPopup->SaveText = t("Crop and save");
            $this->dlgPopup->CancelText = t("Cancel");

            $this->dlgPopup->addAction(new Q\Plugin\Event\ChangeObject(), new Ajax('objManagerRefresh_Click'));

            if ($this->dlgPopup->Language) {
                $this->dlgPopup->AddJavascriptFile(QCUBED_FILEMANAGER_ASSETS_URL . "/js/i18n/". $this->dlgPopup->Language . ".js");
            }

            $this->objInfo = new Q\Plugin\FileInfo($this);
            $this->objInfo->RootUrl = APP_UPLOADS_URL;
            $this->objInfo->TempUrl = APP_UPLOADS_TEMP_URL;
            $this->objInfo->UseWrapper = false;

            $this->lblSearch = new Q\Plugin\Label($this);
            $this->lblSearch->addCssClass('search-results hidden');
            $this->lblSearch->setHtmlAttribute("data-lang", "search_results");
            $this->lblSearch->setCssStyle('font-weight', 600);
            $this->lblSearch->setCssStyle('font-size', '14px;');
            $this->lblSearch->Text = t('Search results:');

            $this->objHomeLink = new Q\Plugin\Label($this);
            $this->objHomeLink->addCssClass('homelink');
            $this->objHomeLink->setCssStyle('font-weight', 600);
            $this->objHomeLink->setCssStyle('font-size', '14px;');
            $this->objHomeLink->Text = Html::renderLink("dialog.php#/", "Repository", ["data-lang" => "repository"]);
            $this->objHomeLink->HtmlEntities = false;
            $this->objHomeLink->addAction(new Click(), new Ajax('appendData_Click'));

            $this->CreateButtons();
            $this->createModals();
            $this->portedAddFolderTextBox();
            $this->portedRenameTextBox();
            $this->portedCheckDestination();
            $this->portedCopyingListBox();
            $this->portedDeleteBox();
            $this->portedMovingListBox();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Processes selected items and updates the UI state by enabling or disabling the "Insert" button
         * based on the type and properties of the selected item.
         *
         * @param ActionParams $params The parameters of the current action.
         *
         * @return array The decoded array of selected items.
         * @throws Caller
         */
        public function selectable_stop(ActionParams $params): array
        {
            $arr = $this->objManager->SelectedItems;
            $this->arrSomeArray = json_decode($arr, true);

            // Here comes a small check that when you select a file, the "Insert" button becomes active or not.
            Application::executeJavaScript("
            const insert = document.querySelector('.insert');
            const size = document.querySelector('.size');
            const radios = document.querySelectorAll('[type=radio]');
        
            // Check if the first radio input is checked
            if (radios[0].checked == false) {
                // Assign the checked status to the second radio input
                 radios[0].checked = true;
            }
            
            if ('{$this->arrSomeArray[0]["data-item-type"]}' === 'file' ) {
                insert.removeAttribute('disabled', 'disabled');
            } else {
                insert.setAttribute('disabled', 'disabled');
            } 

            if (isFileExtensionAllowed('{$this->arrSomeArray[0]["data-name"]}')) {
                size.removeAttribute('disabled', 'disabled');
                radios.forEach(function (radio) {
                    radio.removeAttribute('disabled', 'disabled');
                });
            } else {
                size.setAttribute('disabled', 'disabled');
                radios.forEach(function (radio) {
                    radio.setAttribute('disabled', 'disabled');
                });
            }
        ");

            return $this->arrSomeArray;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Create various buttons and controls used in the UI.
         *
         * This method initializes and configures different buttons
         * and user interface elements with their respective properties,
         * event actions, and styling. These buttons include functionalities
         * for adding files, starting upload, canceling uploads, navigation,
         * and other common file actions such as copy, delete, rename, and more.
         *
         * @return void
         * @throws Caller
         */
        public function CreateButtons(): void
        {
            $this->btnAddFiles = new Q\Plugin\BsFileControl($this, 'files');
            $this->btnAddFiles->Text = t(' Add files');
            $this->btnAddFiles->Glyph = 'fa fa-upload';
            $this->btnAddFiles->Multiple = true;
            $this->btnAddFiles->CssClass = 'btn btn-orange fileinput-button';
            $this->btnAddFiles->UseWrapper = false;

            $this->btnAllStart = new Bs\Button($this);
            $this->btnAllStart->Text = t('Start upload');
            $this->btnAllStart->CssClass = 'btn btn-darkblue all-start disabled';
            $this->btnAllStart->UseWrapper = false;
            $this->btnAllStart->addAction(new Click(), new Ajax('confirmParent_Click'));

            $this->btnAllCancel = new Bs\Button($this);
            $this->btnAllCancel->Text = t('Cancel all uploads');
            $this->btnAllCancel->CssClass = 'btn btn-warning all-cancel disabled';
            $this->btnAllCancel->UseWrapper = false;

            $this->btnBack = new Bs\Button($this);
            $this->btnBack->Text = t('Back to file manager');
            $this->btnBack->CssClass = 'btn btn-default back';
            $this->btnBack->UseWrapper = false;
            $this->btnBack->addAction(new Click(), new Ajax('btnBack_Click'));
            $this->btnBack->addAction(new Click(), new Ajax('dataClearing_Click'));

            $this->btnDone = new Bs\Button($this);
            $this->btnDone->Text = t('Done');
            $this->btnDone->CssClass = 'btn btn-success pull-right done';
            $this->btnDone->UseWrapper = false;
            $this->btnDone->addAction(new Click(), new Ajax('btnDone_Click'));

            $this->btnUploadStart = new Bs\Button($this);
            $this->btnUploadStart->Text = t(' Upload');
            $this->btnUploadStart->Glyph = 'fa fa-upload';
            $this->btnUploadStart->CssClass = 'btn btn-orange launch-start';
            $this->btnUploadStart->CausesValidation = false;
            $this->btnUploadStart->UseWrapper = false;
            $this->btnUploadStart->addAction(new Click(), new Ajax('uploadStart_Click'));

            $this->btnAddFolder = new Bs\Button($this);
            $this->btnAddFolder->Text = t(' Add folder');
            $this->btnAddFolder->Glyph = 'fa fa-folder';
            $this->btnAddFolder->CssClass = 'btn btn-orange';
            $this->btnAddFolder->CausesValidation = false;
            $this->btnAddFolder->UseWrapper = false;
            $this->btnAddFolder->addAction(new Click(), new Ajax('btnAddFolder_Click'));

            $this->btnRefresh = new Bs\Button($this);
            $this->btnRefresh->Glyph = 'fa fa-refresh';
            $this->btnRefresh->CssClass = 'btn btn-darkblue';
            $this->btnRefresh->CausesValidation = false;
            $this->btnRefresh->addAction(new Click(), new Ajax('btnRefresh_Click'));

            $this->btnRename = new Bs\Button($this);
            $this->btnRename->Text = t(' Rename');
            $this->btnRename->Glyph = 'fa fa-pencil';
            $this->btnRename->CssClass = 'btn btn-darkblue';
            $this->btnRename->CausesValidation = false;
            $this->btnRename->UseWrapper = false;
            $this->btnRename->addAction(new Click(), new Ajax('btnRename_Click'));

            $this->btnCrop = new Bs\Button($this);
            $this->btnCrop->Text = t(' Crop');
            $this->btnCrop->Glyph = 'fa fa-crop';
            $this->btnCrop->CssClass = 'btn btn-darkblue';
            $this->btnCrop->CausesValidation = false;
            $this->btnCrop->UseWrapper = false;
            $this->btnCrop->addAction(new Click(), new Ajax('btnCrop_Click'));

            $this->btnCopy = new Bs\Button($this);
            $this->btnCopy->Text = t(' Copy');
            $this->btnCopy->Glyph = 'fa fa-files-o';
            $this->btnCopy->CssClass = 'btn btn-darkblue';
            $this->btnCopy->CausesValidation = false;
            $this->btnCopy->UseWrapper = false;
            $this->btnCopy->addAction(new Click(), new Ajax('btnCopy_Click'));

            $this->btnDelete = new Bs\Button($this);
            $this->btnDelete->Text = t(' Delete');
            $this->btnDelete->Glyph = 'fa fa-trash-o';
            $this->btnDelete->CssClass = 'btn btn-darkblue';
            $this->btnDelete->CausesValidation = false;
            $this->btnDelete->UseWrapper = false;
            $this->btnDelete->addAction(new Click(), new Ajax('btnDelete_Click'));

            $this->btnMove = new Bs\Button($this);
            $this->btnMove->Text = t(' Move');
            $this->btnMove->Glyph = 'fa fa-reply-all';
            $this->btnMove->CssClass = 'btn btn-darkblue';
            $this->btnMove->CausesValidation = false;
            $this->btnMove->UseWrapper = false;
            $this->btnMove->addAction(new Click(), new Ajax('btnMove_Click'));

            $this->btnImageListView = new Bs\Button($this);
            $this->btnImageListView->Glyph = 'fa fa-list'; //  fa-align-justify
            $this->btnImageListView->CssClass = 'btn btn-darkblue';
            $this->btnImageListView->addCssClass('btn-imageList active');
            $this->btnImageListView->UseWrapper = false;
            $this->btnImageListView->addAction(new Click(), new Ajax('btnImageListView_Click'));

            $this->btnListView = new Bs\Button($this);
            $this->btnListView->Glyph = 'fa fa-align-justify';
            $this->btnListView->CssClass = 'btn btn-darkblue';
            $this->btnListView->addCssClass('btn-list');
            $this->btnListView->UseWrapper = false;
            $this->btnListView->addAction(new Click(), new Ajax('btnListView_Click'));

            $this->btnBoxView = new Bs\Button($this);
            $this->btnBoxView->Glyph = 'fa fa-th-large';
            $this->btnBoxView->CssClass = 'btn btn-darkblue';
            $this->btnBoxView->addCssClass('btn-box');
            $this->btnBoxView->UseWrapper = false;
            $this->btnBoxView->addAction(new Click(), new Ajax('btnBoxView_Click'));

            $this->txtFilter = new Bs\TextBox($this);
            $this->txtFilter->Placeholder = t('Search...');
            $this->txtFilter->TextMode = Q\Control\TextBoxBase::SEARCH;
            $this->txtFilter->setHtmlAttribute('autocomplete', 'off');
            $this->txtFilter->addCssClass('search-trigger');
            //$this->addFilterActions();

            $this->btnInsert = new Bs\Button($this);
            $this->btnInsert->Text = t('Insert');
            $this->btnInsert->CssClass = 'btn btn-orange insert';
            $this->btnInsert->setHtmlAttribute("disabled", "disabled");
            $this->btnInsert->CausesValidation = false;
            $this->btnInsert->UseWrapper = false;
            $this->btnInsert->addAction(new Click(), new Ajax('btnInsert_Click'));

            $this->btnCancel = new Bs\Button($this);
            $this->btnCancel->Text = t('Cancel');
            $this->btnCancel->CssClass = 'btn btn-default';
            $this->btnCancel->CausesValidation = false;
            $this->btnCancel->UseWrapper = false;
            $this->btnCancel->addAction(new Click(), new Ajax('btnCancel_Click'));

            $this->lstSize = new Bs\RadioList($this);
            $this->lstSize->addItems(['_files/thumbnail' => t('Small'), '_files/medium' => t('Medium'), '_files/large' => t('Large')]);
            $this->lstSize->SelectedValue = '_files/thumbnail';
            $this->lstSize->addCssClass('size');
            $this->lstSize->ButtonGroupClass = 'radio radio-inline radio-orange';
            $this->lstSize->UseWrapper = false;
            $this->lstSize->Enabled = false;
        }

        /**
         * Creates multiple modal dialog instances with preconfigured properties.
         *
         * This method initializes and configures various modal dialogs for different purposes,
         * such as displaying warnings, tips, information messages, success messages, or errors
         * related to tasks such as uploading, creating new folders, or renaming files and folders.
         * Each modal instance contains specific properties such as title, text, buttons,
         * and event actions for handling user interactions.
         *
         * @return void
         */
        public function createModals(): void
        {
            $this->dlgModal1 = new Bs\Modal($this);
            $this->dlgModal1->Title = t('Warning');
            $this->dlgModal1->Text = t('<p style="margin-top: 15px;">A corrupted table "folders" in the database or folder "upload" in the file system!</p>
                                    <p style="margin-top: 15px;">The table and the file system must be in sync.</p>
                                    <p style="margin-top: 15px;">Please contact the developer or webmaster!</p>');
            $this->dlgModal1->HeaderClasses = 'btn-danger';
            $this->dlgModal1->addCloseButton(t("I take note and ask for help"));

            ///////////////////////////////////////////////////////////////////////////////////////////
            // UPLOAD

            $this->dlgModal2 = new Bs\Modal($this);
            $this->dlgModal2->Title = t('Tip');
            $this->dlgModal2->Text = t('<p style="margin-top: 15px;">Sorry, files cannot be added to this reserved folder!</p>
                                    <p style="margin-top: 15px;">Choose another folder!</p>');
            $this->dlgModal2->HeaderClasses = 'btn-darkblue';
            $this->dlgModal2->addCloseButton(t("I close the window"));

            $this->dlgModal3 = new Bs\Modal($this);
            $this->dlgModal3->Title = t('Tip');
            $this->dlgModal3->Text = t('<p style="margin-top: 15px;">Please choose only specific folder to upload files!</p>');
            $this->dlgModal3->HeaderClasses = 'btn-darkblue';
            $this->dlgModal3->addCloseButton(t("I close the window"));

            $this->dlgModal4 = new Bs\Modal($this);
            $this->dlgModal4->Title = t('Tip');
            $this->dlgModal4->Text = t('<p style="margin-top: 15px;">Cannot select multiple folders to upload files!</p>');
            $this->dlgModal4->HeaderClasses = 'btn-darkblue';
            $this->dlgModal4->addCloseButton(t("I close the window"));

            $this->dlgModal5 = new Bs\Modal($this);
            $this->dlgModal5->AutoRenderChildren = true;
            $this->dlgModal5->Title = t('Info');
            $this->dlgModal5->Text = t('<p style="line-height: 25px; margin-bottom: 10px;">Please check if the destination is correct!</p>');
            $this->dlgModal5->HeaderClasses = 'btn-default';
            $this->dlgModal5->addButton(t("I will continue"), null, false, false, null,
                ['class' => 'btn btn-orange']);
            $this->dlgModal5->addCloseButton(t("I'll cancel"));
            $this->dlgModal5->addAction(new DialogButton(), new Ajax('startUploadProcess_Click'));
            $this->dlgModal5->addAction(new Bs\Event\ModalHidden(), new Ajax('dataClearing_Click'));

            ///////////////////////////////////////////////////////////////////////////////////////////
            // NEW FOLDER

            $this->dlgModal6 = new Bs\Modal($this);
            $this->dlgModal6->Title = t('Tip');
            $this->dlgModal6->Text = t('<p style="margin-top: 15px;">Sorry, a new folder cannot be added to this reserved folder!</p>
                                    <p style="margin-top: 15px;">Choose another folder!</p>');
            $this->dlgModal6->HeaderClasses = 'btn-darkblue';
            $this->dlgModal6->addCloseButton(t("I close the window"));

            $this->dlgModal7 = new Bs\Modal($this);
            $this->dlgModal7->Title = t('Tip');
            $this->dlgModal7->Text = t('<p style="margin-top: 15px;">Please select only one folder to create a new folder in!</p>');
            $this->dlgModal7->HeaderClasses = 'btn-darkblue';
            $this->dlgModal7->addCloseButton(t("I close the window"));

            $this->dlgModal8 = new Bs\Modal($this);
            $this->dlgModal8->AutoRenderChildren = true;
            $this->dlgModal8->Title = t('Info');
            $this->dlgModal8->Text = t('<p style="line-height: 25px; margin-bottom: 10px;">Please check if the destination is correct!</p>');
            $this->dlgModal8->HeaderClasses = 'btn-default';
            $this->dlgModal8->addButton(t("I will continue"), null, false, false, null,
                ['class' => 'btn btn-orange']);
            $this->dlgModal8->addCloseButton(t("I'll cancel"));
            $this->dlgModal8->addAction(new DialogButton(), new Ajax('startAddFolderProcess_Click'));
            $this->dlgModal8->addAction(new Bs\Event\ModalHidden(), new Ajax('dataClearing_Click'));

            $this->dlgModal9 = new Bs\Modal($this);
            $this->dlgModal9->AutoRenderChildren = true;
            $this->dlgModal9->Title = t('Name of a new folder');
            $this->dlgModal9->HeaderClasses = 'btn-default';
            $this->dlgModal9->addButton(t("I accept"), null, false, false, null,
                ['class' => 'btn btn-orange']);
            $this->dlgModal9->addCloseButton(t("I'll cancel"));
            $this->dlgModal9->addAction(new DialogButton(), new Ajax('addFolderName_Click'));
            $this->dlgModal9->addAction(new Bs\Event\ModalHidden(), new Ajax('dataClearing_Click'));

            $this->dlgModal10 = new Bs\Modal($this);
            $this->dlgModal10->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">A new folder created successfully!</p>');
            $this->dlgModal10->Title = t("Success");
            $this->dlgModal10->HeaderClasses = 'btn-success';
            $this->dlgModal10->addCloseButton(t("I close the window"));

            $this->dlgModal11 = new Bs\Modal($this);
            $this->dlgModal11->Title = t('Warning');
            $this->dlgModal11->Text = t('<p style="margin-top: 15px;">Failed to create a new folder!</p>');
            $this->dlgModal11->HeaderClasses = 'btn-danger';
            $this->dlgModal11->addCloseButton(t("I understand"));

            ///////////////////////////////////////////////////////////////////////////////////////////
            // RENAME

            $this->dlgModal12 = new Bs\Modal($this);
            $this->dlgModal12->Title = t('Tip');
            $this->dlgModal12->Text = t('<p style="margin-top: 15px;">Sorry, this reserved folder or file cannot be renamed!</p>
                                    <p style="margin-top: 15px;">Choose another folder or file!</p>');
            $this->dlgModal12->HeaderClasses = 'btn-darkblue';
            $this->dlgModal12->addCloseButton(t("I close the window"));

            $this->dlgModal13 = new Bs\Modal($this);
            $this->dlgModal13->Title = t('Tip');
            $this->dlgModal13->Text = t('<p style="margin-top: 15px;">Please select a folder or file!</p>');
            $this->dlgModal13->HeaderClasses = 'btn-darkblue';
            $this->dlgModal13->addCloseButton(t("I close the window"));

            $this->dlgModal14 = new Bs\Modal($this);
            $this->dlgModal14->Title = t('Tip');
            $this->dlgModal14->Text = t('<p style="margin-top: 15px;">Please select only one folder or file to rename!</p>');
            $this->dlgModal14->HeaderClasses = 'btn-darkblue';
            $this->dlgModal14->addCloseButton(t("I close the window"));

            $this->dlgModal15 = new Bs\Modal($this);
            $this->dlgModal15->AutoRenderChildren = true;
            $this->dlgModal15->Title = t('Rename the folder or file name');
            $this->dlgModal15->HeaderClasses = 'btn-default';
            $this->dlgModal15->addButton(t("I accept"), null, false, false, null,
                ['class' => 'btn btn-orange']);
            $this->dlgModal15->addCloseButton(t("I'll cancel"));
            $this->dlgModal15->addAction(new DialogButton(), new Ajax('renameName_Click'));
            $this->dlgModal15->addAction(new Bs\Event\ModalHidden(), new Ajax('dataClearing_Click'));

            $this->dlgModal16 = new Bs\Modal($this);
            $this->dlgModal16->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Folder name changed successfully!</p>');
            $this->dlgModal16->Title = t("Success");
            $this->dlgModal16->HeaderClasses = 'btn-success';
            $this->dlgModal16->addCloseButton(t("I close the window"));

            $this->dlgModal17 = new Bs\Modal($this);
            $this->dlgModal17->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Failed to rename a folder!</p>');
            $this->dlgModal17->Title = t("Warning");
            $this->dlgModal17->HeaderClasses = 'btn-danger';
            $this->dlgModal17->addCloseButton(t("I understand"));

            $this->dlgModal18 = new Bs\Modal($this);
            $this->dlgModal18->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">File name changed successfully!</p>');
            $this->dlgModal18->Title = t("Success");
            $this->dlgModal18->HeaderClasses = 'btn-success';
            $this->dlgModal18->addCloseButton(t("I close the window"));

            $this->dlgModal19 = new Bs\Modal($this);
            $this->dlgModal19->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Failed to rename a file!</p>');
            $this->dlgModal19->Title = t("Warning");
            $this->dlgModal19->HeaderClasses = 'btn-danger';
            $this->dlgModal19->addCloseButton(t("I understand"));

            ///////////////////////////////////////////////////////////////////////////////////////////
            // COPY

            $this->dlgModal20 = new Bs\Modal($this);
            $this->dlgModal20->Title = t('Tip');
            $this->dlgModal20->Text = t('<p style="margin-top: 15px;">Please select a specific folder(s) or file(s)!</p>');
            $this->dlgModal20->HeaderClasses = 'btn-darkblue';
            $this->dlgModal20->addCloseButton(t("I close the window"));

            $this->dlgModal21 = new Bs\Modal($this);
            $this->dlgModal21->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">It is not possible to copy the main directory!</p>');
            $this->dlgModal21->Title = t("Warning");
            $this->dlgModal21->HeaderClasses = 'btn-danger';
            $this->dlgModal21->addCloseButton(t("I understand"));

            $this->dlgModal22 = new Bs\Modal($this);
            $this->dlgModal22->AutoRenderChildren = true;
            $this->dlgModal22->Title = t('Copy files or folders');
            $this->dlgModal22->HeaderClasses = 'btn-default';
            $this->dlgModal22->addButton(t("I will continue"), null, false, false, null,
                ['class' => 'btn btn-orange']);
            $this->dlgModal22->addCloseButton(t("I'll cancel"));
            $this->dlgModal22->addAction(new DialogButton(), new Ajax('startCopyingProcess_Click'));
            $this->dlgModal22->addAction(new Bs\Event\ModalHidden(), new Ajax('dataClearing_Click'));

            $this->dlgModal23 = new Bs\Modal($this);
            $this->dlgModal23->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Selected files and folders have been copied successfully!</p>');
            $this->dlgModal23->Title = t("Success");
            $this->dlgModal23->HeaderClasses = 'btn-success';
            $this->dlgModal23->addCloseButton(t("Ok"));

            $this->dlgModal24 = new Bs\Modal($this);
            $this->dlgModal24->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Error while copying items!</p>');
            $this->dlgModal24->Title = t("Warning");
            $this->dlgModal24->HeaderClasses = 'btn-danger';
            $this->dlgModal24->addCloseButton(t("I understand"));

            ///////////////////////////////////////////////////////////////////////////////////////////
            // DELETE

            $this->dlgModal25 = new Bs\Modal($this);
            $this->dlgModal25->Title = t('Tip');
            $this->dlgModal25->Text = t('<p style="margin-top: 15px;">Sorry, this reserved folder or file cannot be deleted!</p>
                                    <p style="margin-top: 15px;">Choose another folder or file!</p>');
            $this->dlgModal25->HeaderClasses = 'btn-darkblue';
            $this->dlgModal25->addCloseButton(t("I close the window"));

            $this->dlgModal26 = new Bs\Modal($this);
            $this->dlgModal26->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">It is not possible to delete the main directory!</p>');
            $this->dlgModal26->Title = t("Warning");
            $this->dlgModal26->HeaderClasses = 'btn-danger';
            $this->dlgModal26->addCloseButton(t("I understand"));

            $this->dlgModal27 = new Bs\Modal($this);
            $this->dlgModal27->AutoRenderChildren = true;
            $this->dlgModal27->Title = t('Delete files or folders');
            $this->dlgModal27->HeaderClasses = 'btn-danger';
            $this->dlgModal27->addButton(t("I will continue"), null, false, false, null,
                ['class' => 'btn btn-orange']);
            $this->dlgModal27->addCloseButton(t("I'll cancel"));
            $this->dlgModal27->addAction(new DialogButton(), new Ajax('startDeletionProcess_Click'));
            $this->dlgModal27->addAction(new Bs\Event\ModalHidden(), new Ajax('dataClearing_Click'));

            $this->dlgModal28 = new Bs\Modal($this);
            $this->dlgModal28->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">The selected files and folders have been successfully deleted!</p>');
            $this->dlgModal28->Title = t("Success");
            $this->dlgModal28->HeaderClasses = 'btn-success';
            $this->dlgModal28->addCloseButton(t("Ok"));

            $this->dlgModal29 = new Bs\Modal($this);
            $this->dlgModal29->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Error while deleting items!</p>');
            $this->dlgModal29->Title = t("Warning");
            $this->dlgModal29->HeaderClasses = 'btn-danger';
            $this->dlgModal29->addCloseButton(t("I understand"));

            ///////////////////////////////////////////////////////////////////////////////////////////
            // MOVE

            $this->dlgModal30 = new Bs\Modal($this);
            $this->dlgModal30->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">It is not possible to move the main directory!</p>');
            $this->dlgModal30->Title = t("Warning");
            $this->dlgModal30->HeaderClasses = 'btn-danger';
            $this->dlgModal30->addCloseButton(t("I understand"));

            $this->dlgModal31 = new Bs\Modal($this);
            $this->dlgModal31->Title = t('Tip');
            $this->dlgModal31->Text = t('<p style="margin-top: 15px;">Sorry, this reserved folder or file cannot be moved!</p>
                                    <p style="margin-top: 15px;">Choose another folder or file!</p>');
            $this->dlgModal31->HeaderClasses = 'btn-darkblue';
            $this->dlgModal31->addCloseButton(t("I close the window"));

            $this->dlgModal32 = new Bs\Modal($this);
            $this->dlgModal32->AutoRenderChildren = true;
            $this->dlgModal32->Title = t('Move files or folders');
            $this->dlgModal32->HeaderClasses = 'btn-default move-class';
            $this->dlgModal32->addCssClass("move-class");
            $this->dlgModal32->addButton(t("I will continue"), null, false, false, null,
                ['class' => 'btn btn-orange']);
            $this->dlgModal32->addCloseButton(t("I'll cancel"));
            $this->dlgModal32->addAction(new DialogButton(), new Ajax('startMovingProcess_Click'));
            $this->dlgModal32->addAction(new Bs\Event\ModalHidden(), new Ajax('dataClearing_Click'));

            $this->dlgModal33 = new Bs\Modal($this);
            $this->dlgModal33->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">The selected files and folders have been successfully moved!</p>');
            $this->dlgModal33->Title = t("Success");
            $this->dlgModal33->HeaderClasses = 'btn-success';
            $this->dlgModal33->addCloseButton(t("Ok"));

            $this->dlgModal34 = new Bs\Modal($this);
            $this->dlgModal34->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Error while moving items!</p>');
            $this->dlgModal34->Title = t("Warning");
            $this->dlgModal34->HeaderClasses = 'btn-danger';
            $this->dlgModal34->addCloseButton(t("I understand"));

            ///////////////////////////////////////////////////////////////////////////////////////////
            // INSERT

            $this->dlgModal35 = new Bs\Modal($this);
            $this->dlgModal35->Title = t('Tip');
            $this->dlgModal35->Text = t('<p style="margin-top: 15px;">Sorry, be cannot insert into a reserved file!</p>
                                    <p style="margin-top: 15px;">Select and copy this file to another location, then insert!</p>');
            $this->dlgModal35->HeaderClasses = 'btn-darkblue';
            $this->dlgModal35->addCloseButton(t("I close the window"));

            ///////////////////////////////////////////////////////////////////////////////////////////
            // CROP

            $this->dlgModal40 = new Bs\Modal($this);
            $this->dlgModal40->Title = t('Tip');
            $this->dlgModal40->Text = t('<p style="margin-top: 15px;">Please select an image!</p>');
            $this->dlgModal40->HeaderClasses = 'btn-darkblue';
            $this->dlgModal40->addCloseButton(t("I close the window"));

            $this->dlgModal41 = new Bs\Modal($this);
            $this->dlgModal41->Title = t('Tip');
            $this->dlgModal41->Text = t('<p style="margin-top: 15px;">Please select only one image to crop!</p>
                                    <p style="margin-top: 15px;">Allowed file types: jpg, jpeg, png.</p>');
            $this->dlgModal41->HeaderClasses = 'btn-darkblue';
            $this->dlgModal41->addCloseButton(t("I close the window"));

            $this->dlgModal42 = new Bs\Modal($this);
            $this->dlgModal42->Title = t('Tip');
            $this->dlgModal42->Text = t('<p style="margin-top: 15px;">Please select only one image to crop!</p>');
            $this->dlgModal42->HeaderClasses = 'btn-darkblue';
            $this->dlgModal42->addCloseButton(t("I close the window"));

            $this->dlgModal43 = new Bs\Modal($this);
            $this->dlgModal43->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Image cropping succeeded!</p>');
            $this->dlgModal43->Title = t("Success");
            $this->dlgModal43->HeaderClasses = 'btn-success';
            $this->dlgModal43->addCloseButton(t("I close the window"));

            $this->dlgModal44 = new Bs\Modal($this);
            $this->dlgModal44->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Image cropping failed!</p>');
            $this->dlgModal44->Title = t("Warning");
            $this->dlgModal44->HeaderClasses = 'btn-danger';
            $this->dlgModal44->addCloseButton(t("I understand"));

            $this->dlgModal45 = new Bs\Modal($this);
            $this->dlgModal45->Text = t('<p style="margin-top: 15px;">The image is invalid for cropping!</p>
                                    <p style="margin-top: 15px;">It is recommended to delete this image and upload it again!</p>');
            $this->dlgModal45->Title = t("Warning");
            $this->dlgModal45->HeaderClasses = 'btn-danger';
            $this->dlgModal45->addCloseButton(t("I understand"));

            $this->dlgModal46 = new Bs\Modal($this);
            $this->dlgModal46->Title = t('Tip');
            $this->dlgModal46->Text = t('<p style="margin-top: 15px;">Sorry, be cannot crop a reserved file!</p>
                                    <p style="margin-top: 15px;">Select and copy this file to another location, then crop!</p>');
            $this->dlgModal46->HeaderClasses = 'btn-darkblue';
            $this->dlgModal46->addCloseButton(t("I close the window"));

            ///////////////////////////////////////////////////////////////////////////////////////////
            // CSRF PROTECTION

            $this->dlgModal47 = new Bs\Modal($this);
            $this->dlgModal47->Text = t('<p style="margin-top: 15px;">CSRF Token is invalid! The request was aborted.</p>');
            $this->dlgModal47->Title = t("Warning");
            $this->dlgModal47->HeaderClasses = 'btn-danger';
            $this->dlgModal47->addCloseButton(t("I understand"));
        }

        /**
         * Perform actions to check the destination using destination information panels.
         *
         * @return void
         * @throws Caller
         */
        public function portedCheckDestination(): void
        {
            $pnl1 = new Q\Plugin\DestinationInfo($this->dlgModal5);
            $pnl2 = new Q\Plugin\DestinationInfo($this->dlgModal8);
        }

        /**
         * Initializes and configures UI components for adding a folder text box.
         *
         * @return void
         * @throws Caller
         */
        public function portedAddFolderTextBox(): void
        {
            $this->lblError = new Q\Plugin\Label($this->dlgModal9);
            $this->lblError->Text = t('Folder cannot be created without a name!');
            $this->lblError->addCssClass("modal-error-text hidden");
            $this->lblError->setCssStyle('color', '#ff0000');
            $this->lblError->setCssStyle('font-weight', 600);
            $this->lblError->setCssStyle('padding-top', '5px');
            $this->lblError->UseWrapper = false;

            $this->lblSameName = new Q\Plugin\Label($this->dlgModal9);
            $this->lblSameName->Text = t('Cannot create a folder with the same name!');
            $this->lblSameName->addCssClass("modal-error-same-text hidden");
            $this->lblSameName->setCssStyle('color', '#ff0000');
            $this->lblSameName->setCssStyle('font-weight', 600);
            $this->lblSameName->setCssStyle('padding-top', '5px');
            $this->lblSameName->UseWrapper = false;

            $this->txtAddFolder = new Bs\TextBox($this->dlgModal9);
            $this->txtAddFolder->setHtmlAttribute('autocomplete', 'off');
            $this->txtAddFolder->addCssClass("modal-check-textbox");
            $this->txtAddFolder->setCssStyle('margin-top', '15px');
            $this->txtAddFolder->setCssStyle('margin-bottom', '15px');
            $this->txtAddFolder->setHtmlAttribute('required', 'required');
            $this->txtAddFolder->UseWrapper = false;
        }

        /**
         * Initializes and configures the UI components for renaming functionality.
         *
         * This method sets up error labels and a text box to handle user input during the rename process.
         * It includes various error messages to guide users when naming conflicts or invalid names occur.
         *
         * @return void
         * @throws Caller
         */
        public function portedRenameTextBox(): void
        {
            $this->lblDirectoryError = new Q\Plugin\Label($this->dlgModal15);
            $this->lblDirectoryError->Text = t('The name of the main directory cannot be changed!');
            $this->lblDirectoryError->addCssClass("modal-error-directory hidden");
            $this->lblDirectoryError->setCssStyle('font-weight', 400);
            $this->lblDirectoryError->setCssStyle('padding-top', '5px');
            $this->lblDirectoryError->UseWrapper = false;

            $this->lblError = new Q\Plugin\Label($this->dlgModal15);
            $this->lblError->Text = t('Cannot rename a folder or file without a name!');
            $this->lblError->addCssClass("modal-error-text hidden");
            $this->lblError->setCssStyle('color', '#ff0000');
            $this->lblError->setCssStyle('font-weight', 600);
            $this->lblError->setCssStyle('padding-top', '5px');
            $this->lblError->UseWrapper = false;

            $this->lblRenameName = new Q\Plugin\Label($this->dlgModal15);
            $this->lblRenameName->Text = t('This name cannot be used because it is already in use!');
            $this->lblRenameName->addCssClass("modal-error-rename-text hidden");
            $this->lblRenameName->setCssStyle('color', '#ff0000');
            $this->lblRenameName->setCssStyle('font-weight', 600);
            $this->lblRenameName->setCssStyle('padding-top', '5px');
            $this->lblRenameName->UseWrapper = false;

            $this->txtRename = new Bs\TextBox($this->dlgModal15);
            $this->txtRename->setHtmlAttribute('autocomplete', 'off');
            $this->txtRename->addCssClass("modal-check-rename-textbox");
            $this->txtRename->setCssStyle('margin-top', '15px');
            $this->txtRename->setCssStyle('margin-bottom', '15px');
            $this->txtRename->setHtmlAttribute('required', 'required');
            $this->txtRename->UseWrapper = false;
        }

        /**
         * Initialize and configure the UI components for copying functionality, including labels
         * and a selection dropdown for choosing a destination folder.
         *
         * @return void
         * @throws Caller
         */
        public function portedCopyingListBox(): void
        {
            $this->lblDestinationError = new Q\Plugin\Label($this->dlgModal22);
            $this->lblDestinationError->Text = t('Please select a destination folder!');
            $this->lblDestinationError->addCssClass('destination-error hidden');
            $this->lblDestinationError->setCssStyle('width', '100%');
            $this->lblDestinationError->setCssStyle('color', '#ff0000');
            $this->lblDestinationError->setCssStyle('font-weight', 600);
            $this->lblDestinationError->setCssStyle('padding-top', '5px');
            $this->lblDestinationError->UseWrapper = false;

            $this->lblCourseTitle = new Q\Plugin\Label($this->dlgModal22);
            $this->lblCourseTitle->Text = t('Source folder: ');
            $this->lblCourseTitle->addCssClass('source-title');
            $this->lblCourseTitle->setCssStyle('width', '100%');
            $this->lblCourseTitle->setCssStyle('font-weight', 600);
            $this->lblCourseTitle->setCssStyle('padding-right', '5px');
            $this->lblCourseTitle->setCssStyle('padding-bottom', '5px');
            $this->lblCourseTitle->UseWrapper = false;

            $this->lblCoursePath = new Q\Plugin\Label($this->dlgModal22);
            $this->lblCoursePath->addCssClass('source-path');
            $this->lblCoursePath->setCssStyle('width', '100%');
            $this->lblCoursePath->setCssStyle('font-weight', 400);
            $this->lblCoursePath->setCssStyle('padding-right', '5px');
            $this->lblCoursePath->setCssStyle('padding-bottom', '5px');
            $this->lblCoursePath->UseWrapper = false;

            $this->lblCopyingTitle = new Q\Plugin\Label($this->dlgModal22);
            $this->lblCopyingTitle->Text = t('Destination folder: ');
            $this->lblCopyingTitle->setCssStyle('width', '100%');
            $this->lblCopyingTitle->setCssStyle('font-weight', 600);
            $this->lblCopyingTitle->setCssStyle('padding-right', '5px');
            $this->lblCopyingTitle->setCssStyle('padding-bottom', '5px');
            $this->lblCopyingTitle->UseWrapper = false;

            $this->dlgCopyingDestination = new Q\Plugin\Select2($this->dlgModal22);
            $this->dlgCopyingDestination->Width = '100%';
            $this->dlgCopyingDestination->MinimumResultsForSearch = -1; // If you want to remove the search box, set it to "-1"
            $this->dlgCopyingDestination->SelectionMode = Q\Control\ListBoxBase::SELECTION_MODE_SINGLE;
            $this->dlgCopyingDestination->AddItem(t('- Select One -'), null);
            $this->dlgCopyingDestination->Theme = 'web-vauu';
            $this->dlgCopyingDestination->AllowClear = true;
            $this->dlgCopyingDestination->AddAction(new Change(), new Ajax('dlgDestination_Change'));
        }

        /**
         * Configure and initialize labels for the deletion confirmation dialog box, including warnings, information,
         * errors, and file details.
         *
         * @return void
         * @throws Caller
         */
        public function portedDeleteBox(): void
        {
            $this->lblDeletionWarning = new Q\Plugin\Label($this->dlgModal27);
            $this->lblDeletionWarning->Text = t('Are you sure you want to permanently delete these files and folders?');
            $this->lblDeletionWarning->addCssClass("deletion-warning-text");
            $this->lblDeletionWarning->setCssStyle('width', '100%');
            $this->lblDeletionWarning->setCssStyle('color', '#ff0000');
            $this->lblDeletionWarning->setCssStyle('font-weight', 600);
            $this->lblDeletionWarning->setCssStyle('padding-top', '5px');
            $this->lblDeletionWarning->UseWrapper = false;

            $this->lblDeletionInfo = new Q\Plugin\Label($this->dlgModal27);
            $this->lblDeletionInfo->Text = t("Can\'t undo it afterwards!");
            $this->lblDeletionInfo->addCssClass("deletion-info-text");
            $this->lblDeletionInfo->setCssStyle('width', '100%');
            $this->lblDeletionInfo->setCssStyle('color', '#ff0000');
            $this->lblDeletionInfo->setCssStyle('font-weight', 600);
            $this->lblDeletionInfo->setCssStyle('padding-top', '5px');
            $this->lblDeletionInfo->UseWrapper = false;

            $this->lblDeleteError = new Q\Plugin\Label($this->dlgModal27);
            $this->lblDeleteError->Text = t('Files are locked or cannot be deleted together with folders!');
            $this->lblDeleteError->addCssClass("delete-error-text hidden");
            $this->lblDeleteError->setCssStyle('width', '100%');
            $this->lblDeleteError->setCssStyle('color', '#ff0000');
            $this->lblDeleteError->setCssStyle('font-weight', 600);
            $this->lblDeleteError->setCssStyle('padding-top', '5px');
            $this->lblDeleteError->UseWrapper = false;

            $this->lblDeleteInfo = new Q\Plugin\Label($this->dlgModal27);
            $this->lblDeleteInfo->Text = t('Unlocked files can be deleted!');
            $this->lblDeleteInfo->addCssClass("delete-info-text hidden");
            $this->lblDeleteInfo->setCssStyle('width', '100%');
            $this->lblDeleteInfo->setCssStyle('font-weight', 600);
            $this->lblDeleteInfo->setCssStyle('padding-top', '5px');
            $this->lblDeleteInfo->setCssStyle('padding-bottom', '15px');
            $this->lblDeleteInfo->UseWrapper = false;

            $this->lblDeleteTitle = new Q\Plugin\Label($this->dlgModal27);
            $this->lblDeleteTitle->Text = t('Files and folders to be deleted: ');
            $this->lblDeleteTitle->setCssStyle('font-weight', 600);
            $this->lblDeleteTitle->setCssStyle('padding-right', '5px');
            $this->lblDeleteTitle->setCssStyle('padding-bottom', '5px');
            $this->lblDeleteTitle->UseWrapper = false;

            $this->lblDeletePath = new Q\Plugin\Label($this->dlgModal27);
            $this->lblDeletePath->addCssClass('delete-path');
            $this->lblDeletePath->setCssStyle('font-weight', 400);
            $this->lblDeletePath->setCssStyle('padding-right', '5px');
            $this->lblDeletePath->setCssStyle('padding-bottom', '5px');
            $this->lblDeletePath->UseWrapper = false;
        }

        /**
         * Configures and initializes labels and a dropdown list for managing file and folder moving operations.
         * This method sets up UI elements with appropriate text, styles, and behaviors to indicate errors,
         * provide instructions, and allows users to select a destination folder for moving items.
         *
         * @return void
         * @throws Caller
         */
        public function portedMovingListBox(): void
        {
            $this->lblMovingError = new Q\Plugin\Label($this->dlgModal32);
            $this->lblMovingError->Text = t('Files are locked or cannot be moved together with folders!');
            $this->lblMovingError->addCssClass("move-error-text hidden");
            $this->lblMovingError->setCssStyle('width', '100%');
            $this->lblMovingError->setCssStyle('color', '#ff0000');
            $this->lblMovingError->setCssStyle('font-weight', 600);
            $this->lblMovingError->setCssStyle('padding-top', '5px');
            $this->lblMovingError->UseWrapper = false;

            $this->lblMoveInfo = new Q\Plugin\Label($this->dlgModal32);
            $this->lblMoveInfo->Text = t('Unlocked files can be moved!');
            $this->lblMoveInfo->addCssClass("move-info-text hidden");
            $this->lblMoveInfo->setCssStyle('width', '100%');
            $this->lblMoveInfo->setCssStyle('font-weight', 600);
            $this->lblMoveInfo->setCssStyle('padding-top', '5px');
            $this->lblMoveInfo->setCssStyle('padding-bottom', '15px');
            $this->lblMoveInfo->UseWrapper = false;

            $this->lblMovingDestinationError = new Q\Plugin\Label($this->dlgModal32);
            $this->lblMovingDestinationError->Text = t('Please select a destination folder!');
            $this->lblMovingDestinationError->addCssClass('destination-moving-error hidden');
            $this->lblMovingDestinationError->setCssStyle('color', '#ff0000');
            $this->lblMovingDestinationError->setCssStyle('font-weight', 600);
            $this->lblMovingDestinationError->setCssStyle('padding-top', '5px');
            $this->lblMovingDestinationError->UseWrapper = false;

            $this->lblMovingCourseTitle = new Q\Plugin\Label($this->dlgModal32);
            $this->lblMovingCourseTitle ->Text = t('Source folder: ');
            $this->lblMovingCourseTitle ->addCssClass('moving-source-title');
            $this->lblMovingCourseTitle ->setCssStyle('font-weight', 600);
            $this->lblMovingCourseTitle ->setCssStyle('padding-right', '5px');
            $this->lblMovingCourseTitle ->setCssStyle('padding-bottom', '5px');
            $this->lblMovingCourseTitle ->UseWrapper = false;

            $this->lblMovingCoursePath = new Q\Plugin\Label($this->dlgModal32);
            $this->lblMovingCoursePath->addCssClass('moving-source-path');
            $this->lblMovingCoursePath->setCssStyle('font-weight', 400);
            $this->lblMovingCoursePath->setCssStyle('padding-right', '5px');
            $this->lblMovingCoursePath->setCssStyle('padding-bottom', '5px');
            $this->lblMovingCoursePath->UseWrapper = false;

            $this->lblMovingTitle = new Q\Plugin\Label($this->dlgModal32);
            $this->lblMovingTitle->Text = t('Destination folder: ');
            $this->lblMovingTitle->Width = '100%';
            $this->lblMovingTitle->setCssStyle('font-weight', 600);
            $this->lblMovingTitle->setCssStyle('padding-right', '5px');
            $this->lblMovingTitle->setCssStyle('padding-bottom', '5px');
            $this->lblMovingTitle->UseWrapper = false;

            $this->dlgMovingDestination = new Q\Plugin\Select2($this->dlgModal32);
            $this->dlgMovingDestination->Width = '100%';
            $this->dlgMovingDestination->MinimumResultsForSearch = -1; // If you want to remove the search box, set it to "-1"
            $this->dlgMovingDestination->SelectionMode = Q\Control\ListBoxBase::SELECTION_MODE_SINGLE;
            $this->dlgMovingDestination->addCssClass('js-moving-destination');
            $this->dlgMovingDestination->AddItem(t('- Select One -'), null);
            $this->dlgMovingDestination->Theme = 'web-vauu';
            $this->dlgMovingDestination->AllowClear = true;
            $this->dlgMovingDestination->AddAction(new Change(), new Ajax('dlgDestination_Change'));
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // REPOSITORY LINK

        /**
         * Handles the append data click event.
         *
         * @param ActionParams $params The parameters passed to the action.
         *
         * @return void
         * @throws Caller
         */
        public function appendData_Click(ActionParams $params): void
        {
            $this->arrSomeArray = ["data-id" => 1, "data-parent-id" => null, "data-path" => "", "data-item-type" => "dir", "data-locked" => 0, "data-activities-locked" => 0];
            Application::executeJavaScript("$('.breadcrumbs').empty()");
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // UPLOAD

        /**
         * Handles the start of the file upload process by validating the folder structure,
         * selected path, and related conditions before proceeding.
         *
         * @param ActionParams $params Parameters related to the current action execution.
         *
         * @return void
         * @throws Caller
         */
        public function uploadStart_Click(ActionParams $params): void
        {
            clearstatcache();

            if ($this->dataScan() !== $this->scan($this->objManager->RootPath)) {
                $this->dlgModal1->showDialogBox();
                return;
            }

            if (!$this->arrSomeArray) {
                $this->showDialog(3);
                return;
            }

            $locked = $this->arrSomeArray[0]["data-activities-locked"];

            if ($locked == 1) {
                $this->showDialog(2);
                return;
            }

            if ($this->arrSomeArray[0]["data-item-type"] !== "dir") {
                $this->showDialog(3);
                return;
            }

            if (count($this->arrSomeArray) !== 1) {
                $this->showDialog(7);
                return;
            }

            $this->showDialog(5);

            $this->intDataId = $this->arrSomeArray[0]["data-id"];
            $this->strDataPath = $this->arrSomeArray[0]["data-path"];
            $_SESSION['folderId'] = $this->intDataId;
            $_SESSION['filePath'] = $this->strDataPath;

            if ($this->strDataPath == "") {
                $_SESSION['folderId'] = 1;
                $_SESSION['filePath'] = "";
                Application::executeJavaScript("$('.modalPath').append('/')");
            } else {
                Application::executeJavaScript("$('.modalPath').append('$this->strDataPath')");
            }
        }

        /**
         * Display a dialog based on the provided modal number.
         *
         * @param int $modalNumber The number identifying the modal dialog to display.
         *
         * @return void
         */
        private function showDialog(int $modalNumber): void
        {
            $dialog = $this->getDialogByNumber($modalNumber);
            $dialog->showDialogBox();
        }

        /**
         * Retrieve the dialog object based on the provided modal number.
         *
         * @param int $modalNumber The number corresponding to the desired dialog.
         *
         * @return object The dialog object associated with the given modal number, or dlgModal3 by default.
         */
        private function getDialogByNumber(int $modalNumber): object
        {
            return match ($modalNumber) {
                2 => $this->dlgModal2,
                5 => $this->dlgModal5,
                7 => $this->dlgModal7,
                default => $this->dlgModal3,
            };
        }

        /**
         * Handles the initiation of the upload process triggered by a click action.
         *
         * @param ActionParams $params Parameters associated with the action triggering the upload process.
         *
         * @return void This method does not return a value.
         * @throws Caller
         */
        public function startUploadProcess_Click(ActionParams $params): void
        {
            $script = "
                $('.fileupload-buttonbar').removeClass('hidden');
                $('.upload-wrapper').removeClass('hidden');
                $('.fileupload-donebar').addClass('hidden');
                $('body').removeClass('no-scroll');
                $('.head').addClass('hidden');
                $('.files-heading').addClass('hidden');
                $('.dialog-wrapper').addClass('hidden');
                $('.alert').remove();
            ";

            Application::executeJavaScript($script);

            $this->dlgModal5->hideDialogBox(); // Please check if the destination is correct!
        }

        /**
         * Handles the confirmation of operations on a parent folder triggered by a user action.
         *
         * @param ActionParams $params The parameters associated with the user action.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         * @throws RandomException
         */
        public function confirmParent_Click(ActionParams $params): void
        {
            if (!Application::verifyCsrfToken()) {
                $this->dlgModal47->showDialogBox();
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                return;
            }

            $path = $this->objManager->RootPath . $this->strDataPath;

            $folderId = $_SESSION['folderId'] ?? null;

            if ($folderId) {
                $objFolder = Folders::loadById($folderId);

                // Check if the folder exists before updating properties
                if ($objFolder) {
                    $objFolder->setLockedFile(1);
                    $objFolder->setMtime(filemtime($path));
                    $objFolder->save();
                }
            }
        }

        /**
         * Handles the click event when the back button is pressed.
         * This method updates the UI elements by executing JavaScript
         * and performs a refresh operation using the manager instance.
         *
         * @param ActionParams $params The parameters associated with the action triggering the back button click.
         *
         * @return void This method does not return a value.
         * @throws Caller
         */
        public function btnBack_Click(ActionParams $params): void
        {
            $script = "
                $('.fileupload-buttonbar').addClass('hidden');
                $('.upload-wrapper').addClass('hidden');
                $('body').addClass('no-scroll');
                $('.head').removeClass('hidden');
                $('.files-heading').removeClass('hidden');
                $('.dialog-wrapper').removeClass('hidden');
                $('.alert').remove();
            ";

            Application::executeJavaScript($script);

            $this->objManager->refresh();
        }

        /**
         * Handles the "Done" button click event, resetting session variables and updating the UI.
         *
         * @param ActionParams $params Parameters associated with the action event.
         *
         * @return void
         * @throws Caller
         */
        protected function btnDone_Click(ActionParams $params): void
        {
            unset($_SESSION['folderId']);
            unset($_SESSION['filePath']);

            Application::executeJavaScript("
                $('.fileupload-buttonbar').addClass('hidden');
                $('.upload-wrapper').addClass('hidden');
                $('body').addClass('no-scroll');
                $('.head').removeClass('hidden');
                $('.files-heading').removeClass('hidden');
                $('.dialog-wrapper').removeClass('hidden');
                $('.alert').remove();
            ");

            $this->objManager->refresh();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // NEW FOLDER

        /**
         * Handles the click event for adding a folder.
         *
         * This method performs several checks on the current state of the application's data,
         * including verifying folder data consistency, checking permissions, and ensuring
         * that only valid folder data can proceed. Depending on the validation results,
         * appropriate modal dialogs are displayed to the user.
         *
         * @param ActionParams $params Event parameters associated with the button click action.
         *
         * @return void This method does not return a value.
         * @throws Caller
         */
        public function btnAddFolder_Click(ActionParams $params): void
        {
            clearstatcache();

            if ($this->dataScan() !== $this->scan($this->objManager->RootPath)) {
                $this->dlgModal1->showDialogBox();
                return;
            }

            if (!$this->arrSomeArray) {
                $this->dlgModal7->showDialogBox();
                return;
            }

            $locked = $this->arrSomeArray[0]["data-activities-locked"];

            if ($locked == 1) {
                $this->dlgModal6->showDialogBox();
                return;
            }

            if (count($this->arrSomeArray) !== 1 || $this->arrSomeArray[0]["data-item-type"] !== "dir") {
                $this->dlgModal7->showDialogBox();
                return;
            }

            $this->dlgModal8->showDialogBox();
            $this->intDataId = $this->arrSomeArray[0]["data-id"];
            $this->strDataPath = $this->arrSomeArray[0]["data-path"];

            if ($this->strDataPath == "") {
                $this->intDataId = 1;
                $this->strDataPath = "";
                Application::executeJavaScript("$('.modalPath').append('/')");
            } else {
                Application::executeJavaScript("$('.modalPath').append('$this->strDataPath')");
            }
        }

        /**
         * Initiates the process of adding a new folder. This method handles CSRF token verification,
         * dialog box interactions, and configures the necessary JavaScript for user input validation.
         *
         * @param ActionParams $params Parameters related to the triggered action.
         *
         * @return void
         * @throws Caller
         * @throws RandomException
         */
        public function startAddFolderProcess_Click(ActionParams $params): void
        {
            if (!Application::verifyCsrfToken()) {
                $this->dlgModal47->showDialogBox();
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                return;
            }

            $_SESSION['fileId'] = $this->intDataId;
            $_SESSION['filePath'] = $this->strDataPath;

            $this->dlgModal8->hideDialogBox();
            $this->dlgModal9->showDialogBox();
            $this->txtAddFolder->Text = '';

            $javascript = "
                $('.modal-check-textbox').on('keyup keydown', function() {
                    var length = $(this).val().length;
                    var modalHeader = $('.modal-header');
                    var modalFooterBtn = $('.modal-footer .btn-orange');
        
                    if (length === 0) {
                        modalHeader.removeClass('btn-default').addClass('btn-danger');
                        $('.modal-error-same-text').addClass('hidden');
                        $('.modal-error-text').removeClass('hidden');
                        modalFooterBtn.attr('disabled', 'disabled');
                    } else {
                        modalHeader.removeClass('btn-danger').addClass('btn-default');
                        $('.modal-error-same-text').addClass('hidden');
                        $('.modal-error-text').addClass('hidden');
                        modalFooterBtn.removeAttr('disabled', 'disabled');
                    }
                });
            ";

            Application::executeJavaScript($javascript);
        }

        /**
         * Handles the action of adding a new folder when the corresponding button is clicked.
         *
         * @param ActionParams $params Parameters related to the action triggered.
         *
         * @return void This method does not return a value.
         * @throws Caller
         * @throws InvalidCast
         * @throws RandomException
         */
        public function addFolderName_Click(ActionParams $params): void
        {
            if (!Application::verifyCsrfToken()) {
                $this->dlgModal47->showDialogBox();
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                return;
            }

            $path = $this->objManager->RootPath . $_SESSION['filePath'];
            $scanned_directory = array_diff(scandir($path), array('..', '.'));

            if (trim($this->txtAddFolder->Text) == "") {
                Application::executeJavaScript($this->getJavaScriptForEmptyFolder());
                return;
            }

            if (in_array(trim($this->txtAddFolder->Text), $scanned_directory)) {
                Application::executeJavaScript($this->getJavaScriptForDuplicateFolder());
                return;
            }

            $this->makeFolders($this->txtAddFolder->Text, $_SESSION['fileId'], $path);
            $this->dlgModal9->hideDialogBox();
        }

        /**
         * Generates the JavaScript code required to update the modal dialog
         * for cases where an empty folder condition is encountered.
         *
         * @return string The JavaScript code as a string to handle UI adjustments for an empty folder scenario.
         */
        private function getJavaScriptForEmptyFolder(): string
        {
            return "
                $('.modal-header').removeClass('btn-default').addClass('btn-danger');
                $('.modal-error-same-text').addClass('hidden');
                $('.modal-error-text').removeClass('hidden');
                $('.modal-footer .btn-orange').attr('disabled', 'disabled');
            ";
            }

        /**
         * Generates the JavaScript code required to update the modal dialog
         * for cases where a duplicate folder condition is detected.
         *
         * @return string The JavaScript code as a string to handle UI adjustments for a duplicate folder scenario.
         */
        private function getJavaScriptForDuplicateFolder(): string
        {
            return "
                $('.modal-header').removeClass('btn-default').addClass('btn-danger');
                $('.modal-error-same-text').removeClass('hidden');
                $('.modal-error-text').addClass('hidden');
                $('.modal-footer .btn-orange').attr('disabled', 'disabled');
            ";
            }

        /**
         * Creates folders based on the provided parameters and updates the database
         * and UI accordingly. This process involves creating directories, setting
         * metadata, and handling session cleanup and UI updates.
         *
         * @param string $text The name of the folder to be created.
         * @param null|int $id The ID of the parent folder. If null, the folder is created
         *                     at the root level.
         * @param string $path The absolute path where the folder should be created.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        protected function makeFolders(string $text, ?int $id, string $path): void
        {
            clearstatcache();

            $fullPath = $path . "/" . trim(QString::sanitizeForUrl($text));
            $relativePath = $this->objManager->getRelativePath($fullPath);

            Folder::makeDirectory($fullPath, 0777);

            if ($id) {
                $objFolder = Folders::loadById($id);
                if ($objFolder->getLockedFile() !== 1) {
                    $objFolder->setMtime(filemtime($path));
                    $objFolder->setLockedFile(1);
                    $objFolder->save();
                }
            }

            $objAddFolder = new Folders();
            $objAddFolder->setParentId($id);
            $objAddFolder->setPath($relativePath);
            $objAddFolder->setName(trim($text));
            $objAddFolder->setType('dir');
            $objAddFolder->setMtime(filemtime($path));
            $objAddFolder->setLockedFile(0);
            $objAddFolder->save();

            foreach ($this->tempFolders as $tempFolder) {
                $tempPath = $this->objManager->TempPath . '/_files/' . $tempFolder . $relativePath;
                Folder::makeDirectory($tempPath, 0777);
            }

            $dialogBox = file_exists($fullPath) ? $this->dlgModal10 : $this->dlgModal11;
            $dialogBox->showDialogBox();

            unset($_SESSION['fileId']);
            unset($_SESSION['filePath']);
            $this->objManager->refresh();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // REFRESH

        /**
         * Handles the click event for the refresh button, triggering a refresh operation
         * within the manager to update the relevant data or state.
         *
         * @param ActionParams $params Parameters associated with the action event.
         *
         * @return void
         */
        public function btnRefresh_Click(ActionParams $params): void
        {
            $this->objManager->refresh();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // RENAME

        /**
         * Handles the rename button, click event to validate the renaming operation,
         * check for data consistency, and display the appropriate dialog boxes
         * based on the current state and conditions.
         *
         * @param ActionParams $params Parameters passed from the triggering action, typically containing context-sensitive
         *     data.
         *
         * @return void This method does not return a value but performs various UI updates and validation checks.
         * @throws Caller
         */
        public function btnRename_Click(ActionParams $params): void
        {
            clearstatcache();

            if ($this->dataScan() !== $this->scan($this->objManager->RootPath)) {
                $this->dlgModal1->showDialogBox();
                return;
            }

            if (!$this->arrSomeArray) {
                $this->dlgModal13->showDialogBox();
                return;
            }

            $locked = $this->arrSomeArray[0]["data-activities-locked"];

            if ($locked == 1) {
                $this->dlgModal12->showDialogBox();
                return;
            }

            if (count($this->arrSomeArray) !== 1) {
                $this->dlgModal14->showDialogBox();
                return;
            }

            $this->intDataId = $this->arrSomeArray[0]["data-id"];
            $this->strDataName = $this->arrSomeArray[0]["data-name"];
            $this->strDataPath = $this->arrSomeArray[0]["data-path"];
            $this->strDataType = $this->arrSomeArray[0]["data-item-type"];
            $this->intDataLocked = $this->arrSomeArray[0]["data-locked"];

            $strFile = $this->objManager->RootPath . $this->strDataPath;

            if (is_file($strFile)) {
                $strName = pathinfo($strFile, PATHINFO_FILENAME);
                $this->txtRename->Text = $strName;
            } else {
                $this->txtRename->Text = $this->strDataName;
            }

            $this->dlgModal15->showDialogBox();

            if ($this->txtRename->Text == "upload") {
                $this->showUploadError();
            } else {
                $this->showRenameJavaScript();
            }
        }

        /**
         * Executes the JavaScript code needed to update the modal dialog
         * to display an upload error condition, adjusting UI elements accordingly.
         *
         * @return void No return value as the method directly executes the JavaScript code.
         * @throws Caller
         */
        private function showUploadError(): void
        {
            $script = "
                $('.modal-header').removeClass('btn-default').addClass('btn-danger');
                $('.modal-error-directory').removeClass('hidden');
                $('.modal-check-rename-textbox').addClass('hidden');
                $('.modal-error-rename-text').addClass('hidden');
                $('.modal-error-text').addClass('hidden');
                $('.modal-footer .btn-orange').attr('disabled', 'disabled');
            ";
            Application::executeJavaScript($script);
        }

        /**
         * Executes the JavaScript code required to handle the rename functionality
         * within a modal dialog. This includes enabling or disabling buttons,
         * updating header styles, and showing or hiding error messages based on
         * the length of the input text in the rename textbox.
         *
         * @return void This method does not return a value as it executes the JavaScript code directly.
         * @throws Caller
         */
        private function showRenameJavaScript(): void
        {
            $script = "
                $('.modal-check-rename-textbox').on('keyup keydown', function() {
                    var length = $('.modal-check-rename-textbox').val().length;
                    if(length == 0) {
                        $('.modal-header').removeClass('btn-default').addClass('btn-danger');
                        $('.modal-error-rename-text').addClass('hidden');
                        $('.modal-error-text').removeClass('hidden');
                        $('.modal-footer .btn-orange').attr('disabled', 'disabled');
                    } else {
                        $('.modal-header').removeClass('btn-danger').addClass('btn-default');
                        $('.modal-error-rename-text').addClass('hidden');
                        $('.modal-error-text').addClass('hidden');
                        $('.modal-footer .btn-orange').removeAttr('disabled', 'disabled');
                    }
                });
            ";
            Application::executeJavaScript($script);
        }

        /**
         * Handles the renaming operation triggered by a UI click event. Verifies the CSRF token,
         * checks conditions for renaming, and performs the appropriate renaming actions
         * based on the data type (directory or file).
         *
         * @param ActionParams $params Parameters passed from the triggering action, containing
         *                              necessary data for processing the rename operation.
         *
         * @return void No return value. The method performs renaming operations and updates the UI state accordingly.
         * @throws Caller
         * @throws InvalidCast
         * @throws RandomException
         */
        public function renameName_Click(ActionParams $params): void
        {
            if (!Application::verifyCsrfToken()) {
                $this->dlgModal47->showDialogBox();
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                return;
            }

            $path = $this->objManager->RootPath . $this->strDataPath;

            // Check conditions preventing renaming
            if ($this->isRenameNotAllowed($path)) {
                $this->showRenameError();
                return;
            }

            // Perform the renaming based on the data type
            if ($this->strDataType == "dir") {
                $this->renameDirectory();
            } else {
                $this->renameFile();
            }

            // Additional operations after renaming
            $this->postRenameOperations();

            $this->objManager->refresh();
        }

        // Helper functions

        /**
         * Determines whether renaming a file or folder is not allowed based on the provided path.
         *
         * @param string $path The path to the file or folder whose renaming restrictions are being checked.
         *
         * @return bool True if renaming is not allowed, otherwise false.
         */
        private function isRenameNotAllowed(string $path): bool
        {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $files = array_diff(scandir(dirname($path)), array('..', '.'));

            $matchedString = ($this->strDataType == "file") ? $this->txtRename->Text . "." . $ext : $this->txtRename->Text;

            return in_array($matchedString, $files);
        }

        /**
         * Executes the JavaScript code to update the modal dialog for scenarios where
         * a rename operation results in an error.
         *
         * @return void Does not return any value as it directly executes JavaScript for UI adjustments.
         * @throws Caller
         */
        private function showRenameError(): void
        {
            Application::executeJavaScript("
                $('.modal-header').removeClass('btn-default').addClass('btn-danger');
                $('.modal-error-rename-text').removeClass('hidden');
                $('.modal-error-text').addClass('hidden');
                $('.modal-footer .btn-orange').attr('disabled', 'disabled');
            ");
        }

        /**
         * Renames a directory and updates all associated paths and data in the system, including subdirectories
         * and files. Handles directories with or without subfolders/files, ensuring all affected paths are updated
         * in the database and file system.
         *
         * @return void This method does not return a value but updates directory and file paths both in the file system
         *              and the database, ensuring data consistency.
         * @throws Caller
         * @throws InvalidCast
         */
        private function renameDirectory(): void
        {
            // Perform a directory renaming logic

            $path = $this->objManager->RootPath . $this->strDataPath;
            $parts = pathinfo($path);
            $sanitizedName = QString::sanitizeForUrl(trim($this->txtRename->Text));
            $this->strNewPath = $parts['dirname'] . '/' . $sanitizedName;

            $objFolders = Folders::loadAll();
            $objFiles = Files::loadAll();

            // If the folder does not contain subfolders and files, renaming the folder is easy. If this folder contains
            // subfolders and files, all names and paths in descending order must be renamed according to the same logic
            if ($this->intDataLocked == 0) {
                // If there are no subfolders or files in a folder, renaming is easy.
                if (is_dir($path)) {
                    // We will immediately update the database accordingly.
                    $objFolder = Folders::loadById($this->intDataId);
                    $objFolder->Name = trim($this->txtRename->Text);
                    $objFolder->Path = $this->objManager->getRelativePath($this->strNewPath);
                    $objFolder->Mtime = time();
                    $objFolder->save();

                    $this->objManager->rename($path, $this->strNewPath);
                }

                // Here the files must be renamed according to the same logic in temp directories
                foreach ($this->tempFolders as $tempFolder) {
                    if (is_dir($this->objManager->TempPath . '/_files/' . $tempFolder . $this->strDataPath)) {
                        $this->objManager->rename($this->objManager->TempPath . '/_files/' . $tempFolder . $this->strDataPath, $this->objManager->TempPath . '/_files/' . $tempFolder . $this->objManager->getRelativePath($this->strNewPath));
                    }
                }

            } else {
                // If there are subfolders and files in the folder, they must also be renamed.
                $this->tempItems = $this->fullScanIds($this->intDataId);
                $arrUpdatehash = [];

                if ($this->intDataId) {
                    $obj = Folders::loadById($this->intDataId);
                    $obj->Name = trim($this->txtRename->Text);
                    $obj->Mtime = time();
                    $obj->save();
                }

                foreach ($objFolders as $objFolder) {
                    foreach ($this->tempItems as $temp) {
                        if ($temp == $objFolder->getId()) {
                            $newPath = str_replace(basename($this->strDataPath), $sanitizedName, $objFolder->Path);
                            $this->strNewPath = $this->objManager->RootPath . $newPath;

                            $arrUpdatehash[] = $newPath;
                            $this->objManager->UpdatedHash = rawurlencode(dirname($arrUpdatehash[0]));

                            if (is_dir($this->objManager->RootPath . $objFolder->getPath())) {
                                $this->objManager->rename($this->objManager->RootPath . $objFolder->getPath(), $this->strNewPath);
                            }

                            foreach ($this->tempFolders as $tempFolder) {
                                if (is_dir($this->objManager->TempPath . '/_files/' . $tempFolder . $objFolder->getPath())) {
                                    $this->objManager->rename($this->objManager->TempPath . '/_files/' . $tempFolder . $objFolder->getPath(), $this->objManager->TempPath . '/_files/' . $tempFolder . $this->objManager->getRelativePath($this->strNewPath));
                                }
                            }

                            if ($this->intDataLocked !== 0) {
                                $obj = Folders::loadById($objFolder->getId());
                                $obj->Path = $this->objManager->getRelativePath($this->strNewPath);
                                $obj->Mtime = time();
                                $obj->save();
                            }

                        }
                    }
                }

                foreach ($objFiles as $objFile) {
                    foreach ($this->tempItems as $temp) {
                        if ($temp == $objFile->getFolderId()) {
                            $newPath = str_replace(basename($this->strDataPath), $sanitizedName, $objFile->Path);
                            $this->strNewPath = $this->objManager->RootPath . $newPath;

                            // if (is_file($this->objManager->RootPath . $objFile->getPath())) {
                            // $this->objManager->rename($this->objManager->RootPath . $objFile->getPath(), $this->objManager->RootPath . $this->strNewPath);
                            // }

                            $obj = Files::loadById($objFile->getId());
                            $obj->Path = $this->objManager->getRelativePath($this->strNewPath);
                            $obj->Mtime = time();
                            $obj->save();
                        }
                    }
                }
            }
            $this->handleResult();
        }

        /**
         * Renames a file and updates its metadata in the system. The renaming is applied
         * both in the main directory and corresponding temporary directories (if applicable).
         * The updated file information is stored in the database, and necessary adjustments
         * are performed to reflect the changes.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        private function renameFile(): void
        {
            // Perform file renaming logic

            $path = $this->objManager->RootPath . $this->strDataPath;
            $parts = pathinfo($path);

            // The file name is changed in the main directory
            if (is_file($path)) {
                $this->strNewPath = $parts['dirname'] . '/' . trim($this->txtRename->Text) . '.' . strtolower($parts['extension']);
                $this->objManager->rename($this->objManager->RootPath . $this->strDataPath, $this->strNewPath);
            }

            // Here the files must be renamed according to the same logic in temp directories
            if (in_array(strtolower($parts['extension']), $this->arrAllowed)) {
                foreach ($this->tempFolders as $tempFolder) {
                    if (is_file($this->objManager->TempPath . '/_files/' . $tempFolder . $this->strDataPath)) {
                        $this->objManager->rename($this->objManager->TempPath . '/_files/' . $tempFolder . $this->strDataPath, $this->objManager->TempPath . '/_files/' . $tempFolder . $this->objManager->getRelativePath($this->strNewPath));
                    }
                }
            }

            $objFile = Files::loadById($this->intDataId);
            $objFile->Name = basename($this->strNewPath);
            $objFile->Path = $this->objManager->getRelativePath($this->strNewPath);
            $objFile->Size = filesize($this->strNewPath);
            $objFile->Mtime = time();
            $objFile->save();

            $this->handleResult();
        }

        /**
         * Handles the result of renaming a file or directory by displaying
         * the appropriate modal dialog based on the success or failure of the operation.
         * Differentiates responses for directories and files.
         *
         * @return void This method does not return a value.
         */
        private function handleResult(): void
        {
            // Handle success or failure scenarios after renaming

            if (file_exists($this->strNewPath)) {

                $this->dlgModal15->hideDialogBox();

                if ($this->strDataType == "dir") {
                    $this->dlgModal16->showDialogBox();
                } else {
                    $this->dlgModal18->showDialogBox();
                }
            } else {
                if ($this->strDataType == "dir") {
                    $this->dlgModal17->showDialogBox();
                } else {
                    $this->dlgModal19->showDialogBox();
                }
            }
        }

        /**
         * Executes post-rename operations, including updating the UI when
         * specific conditions related to a single item in the array are met.
         *
         * @return void Performs the necessary operations without returning a value.
         * @throws Caller
         */
        private function postRenameOperations(): void
        {
            if (count($this->arrSomeArray) === 1) {
                Application::executeJavaScript("$('.breadcrumbs').empty()");
            }
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // CROP

        /**
         * Handles the crop button click event. This method verifies the CSRF token, performs various validation
         * checks on the selected image file, and prepares necessary data for the cropping operation. If any
         * validation fails, corresponding dialog boxes are displayed with specific error messages.
         *
         * @param ActionParams $params The parameters passed to the action, typically containing user input or context.
         *
         * @return void This method does not return any value but may perform UI updates or trigger dialog boxes.
         * @throws Caller
         * @throws RandomException
         */
        public function btnCrop_Click(ActionParams $params): void
        {
            if (!Application::verifyCsrfToken()) {
                $this->dlgModal47->showDialogBox();
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                return;
            }

            clearstatcache();

            if ($this->dataScan() !== $this->scan($this->objManager->RootPath)) {
                $this->dlgModal1->showDialogBox();
                return;
            }

            if (!$this->arrSomeArray) {
                $this->dlgModal40->showDialogBox();
                return;
            }

            if ($this->arrSomeArray[0]["data-activities-locked"] == 1) {
                $this->dlgModal46->showDialogBox(); // Sorry, be cannot crop a reserved file! ...
                return;
            }

            $this->strDataPath = $this->arrSomeArray[0]["data-path"];
            $fullFilePath = $this->objManager->RootUrl . $this->strDataPath;

            if ($this->arrSomeArray[0]['data-item-type'] == 'file' &&
                !in_array(strtolower($this->arrSomeArray[0]['data-extension']), $this->arrCroppieTypes)) {
                $this->dlgModal41->showDialogBox();
                return;
            }

            if (count($this->arrSomeArray) !== 1 || $this->arrSomeArray[0]['data-item-type'] !== 'file') {
                $this->dlgModal42->showDialogBox();
                return;
            }

            // Check if the file exists and its size is 0 bytes
            if (file_exists($fullFilePath) && filesize($fullFilePath) === 0) {
                $this->dlgModal45->showDialogBox();
                return;
            }

            $scanFolders = $this->scanForSelect();
            $folderData = [];

            foreach ($scanFolders as $folder) {
                if ($folder['activities_locked'] !== 1) {
                    $level = $folder['depth'];
                    if ($this->checkString($folder['path'])) {
                        $level = 0;
                    }
                    $folderData[] = [
                        'id' => $folder['path'],
                        'text' => $folder['name'],
                        'level' => $level,
                        'folderId' => $folder['id']
                    ];
                }
            }

            $this->dlgPopup->showDialogBox();

            $this->dlgPopup->SelectedImage = $fullFilePath;
            $this->dlgPopup->Data = $folderData;
        }

        /**
         * Handles the click event to refresh the object manager.
         * Checks if the finalized image path exists and displays the appropriate dialog
         * box based on the result of the image cropping operation.
         *
         * @param ActionParams $params The parameters associated with the triggered action.
         *
         * @return void This method does not return a value; it performs UI updates and refreshes the object manager.
         */
        public function objManagerRefresh_Click(ActionParams $params): void
        {
            if (file_exists($this->objManager->RootPath . $this->dlgPopup->FinalPath)) {
                $this->dlgModal43->showDialogBox();
            } else {
                $this->dlgModal44->showDialogBox();
            }

            $this->objManager->refresh();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // COPY

        /**
         * Handles the click event for the copy button, including CSRF token validation,
         * data preparation, validation, processing, and updates to the user interface
         * for copy-related operations.
         *
         * @param ActionParams $params The parameters associated with the action event.
         *
         * @return void This method does not return a value; it performs operations such as
         *              validating conditions, preparing data, and updating the UI based on the results.
         * @throws Caller
         * @throws RandomException
         */
        public function btnCopy_Click(ActionParams $params): void
        {
            if (!Application::verifyCsrfToken()) {
                $this->dlgModal47->showDialogBox();
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                return;
            }

            $objFolders = Folders::loadAll();
            $objFiles = Files::loadAll();

            // Check for conditions preventing copying
            if (!$this->validateCopyConditions()) {
                return;
            }

            // Prepare and send data to function fullCopy($src, $dst)
            $this->prepareCopyData();

            // Data validation and processing
            $this->processCopyData($objFolders, $objFiles);

            // UI-related operations
            $this->updateCopyDestinationDialog();

            // Show the copy dialog
            $this->showCopyDialog();
        }

        // Helper functions

        /**
         * Validates conditions required for copying files or folders. This method checks
         * the integrity of the file system structure, ensures that the necessary selections
         * have been made, and verifies specific constraints for the operation.
         *
         * @return bool Returns true if all copy conditions are met; otherwise, false.
         * @throws Caller
         */
        private function validateCopyConditions(): bool
        {
            clearstatcache();

            if ($this->dataScan() !== $this->scan($this->objManager->RootPath)) {
                $this->dlgModal1->showDialogBox();
                return false;
            }

            if (!$this->arrSomeArray) {
                $this->dlgModal20->showDialogBox();
                return false;
            }

            if ($this->arrSomeArray[0]["data-id"] == 1 && $this->arrSomeArray[0]["data-path"] == "") {
                $this->dlgModal21->showDialogBox();
                return false;
            }

            return true;
        }

        /**
         * Prepares data required for the `fullCopy` function by processing an internal array
         * and populating the list of selected items with specified data paths.
         *
         * @return void This method does not return a value.
         */
        private function prepareCopyData(): void
        {
            // Preparing and sending data to the function fullCopy($src, $dst)

            $tempArr = [];

            foreach ($this->arrSomeArray as $arrSome) {
                $tempArr[] = $arrSome;
            }
            foreach ($tempArr as $temp) {
                if ($temp['data-path']) {
                    $this->tempSelectedItems[] = $temp['data-path'];
                }
            }
        }

        /**
         * Processes the copying of data by handling both folder and file operations.
         *
         * @param array $objFolders An object containing information about the folders to be copied.
         * @param array $objFiles An object containing information about the files to be copied.
         *
         * @return void
         * @throws Caller
         */
        private function processCopyData(array $objFolders, array $objFiles): void
        {
            // Processing logic for copying data

            $this->copyDirectory($objFolders);
            $this->copyFile($objFiles);
        }

        /**
         * Copies directory contents by scanning and processing folder IDs.
         *
         * This method processes an array of objects representing folders, identifying
         * and copying their paths based on specific conditions.
         *
         * @param array $objFolders Array of folder objects to be processed. Each object should provide methods to retrieve
         *     its ID and path.
         *
         * @return void No return value, as the method operates directly on the class's internal properties.
         * @throws Caller
         */
        private function copyDirectory(array $objFolders): void
        {
            // Perform directory copying logic

            $dataFolders = [];
            $tempIds = [];

            foreach ($this->arrSomeArray as $arrSome) {
                if ($arrSome["data-item-type"] == "dir") {
                    $dataFolders[] = $arrSome["data-id"];
                }
            }
            foreach ($dataFolders as $dataFolder) {
                $tempIds = array_merge($tempIds, $this->fullScanIds($dataFolder));

            }
            foreach ($objFolders as $objFolder) {
                foreach ($tempIds as $tempId) {
                    if ($objFolder->getId() == $tempId) {
                        $this->tempItems[] = $objFolder->getPath();
                    }
                }
                sort($this->tempItems);
            }
        }

        /**
         * Copies specified file objects by matching their folder or file IDs with predefined arrays and updating an internal temporary items list.
         *
         * @param array $objFiles An array of file objects to be processed. Each file object should provide methods to retrieve folder IDs and file paths.
         *
         * @return void This method does not return a value; it updates the internal state by modifying the temporary items list.
         */
        private function copyFile(array $objFiles): void
        {
            // Perform file copying logic

            $dataFiles = [];

            foreach ($objFiles as $objFile) {
                foreach ([] as $tempId) {
                    if ($objFile->getFolderId() == $tempId) {
                        $this->tempItems[] = $objFile->getPath();
                    }
                }
                sort($this->tempItems);
            }

            foreach ($this->arrSomeArray as $arrSome) {
                if ($arrSome["data-item-type"] == "file") {
                    $dataFiles[] = $arrSome["data-id"];
                }
            }
            foreach ($objFiles as $objFile) {
                foreach ($dataFiles as $dataFile) {
                    if ($objFile->getId() == $dataFile) {
                        $this->tempItems[] = $objFile->getPath();
                    }
                }
                sort($this->tempItems);
            }
        }

        /**
         * Updates the copy destination dialog interface by scanning directories, marking locked directories,
         * and populating the dialog with relevant items.
         *
         * This method identifies locked directories based on the current state and additional checks
         * and then integrates the scanned paths into the copy destination dialog with appropriate markers.
         * Locked directories are highlighted based on specific conditions.
         *
         * @return void This method does not return a value.
         * @throws Caller
         */
        private function updateCopyDestinationDialog(): void
        {
            // Update destination dialog UI

            $objPaths = $this->scanForSelect();

            foreach ($this->tempItems as $tempItem) {
                if (is_dir($this->objManager->RootPath . $tempItem)) {
                    $this->objLockedDirs[] = $tempItem;
                }
            }

            if ($objPaths) foreach ($objPaths as $objPath) {
                if ($objPath['activities_locked'] == 1) {
                    $this->objLockedDirs[] = $objPath["path"];
                }
            }

            foreach ($objPaths as $folder) {
                $level = $folder['depth'];
                if ($this->checkString($folder['path'])) {
                    $level = 0;
                }

                if ($folder['activities_locked'] == 1) {
                    $mark = true;
                } else {
                    $mark = false;
                }

                $this->dlgCopyingDestination->AddItem($this->printDepth($folder['name'], $level), $folder['path'], null, $mark);
            }
        }

        /**
         * Displays the copy dialog and updates its state based on the conditions provided.
         * Adjusts labels, styles, enabled states of components, and executes necessary JavaScript code.
         * Specifically, it handles the enabling/disabling of action buttons and shows the modal dialog for copying
         * operations.
         *
         * @return void This method does not return any value.
         * @throws Caller
         */
        private function showCopyDialog(): void
        {
            // Show the copy dialog

            if (count($this->tempItems) !== 0) {
                $source = join(', ', $this->tempItems);
                $this->lblCoursePath->Text = $source;
                $this->lblCoursePath->setCssStyle('color', '#000000');
                $this->dlgCopyingDestination->Enabled = true;
            } else {
                $this->lblCoursePath->Text = t("It is not possible to copy the main directory!");
                $this->lblCoursePath->setCssStyle('color', '#ff0000');
                $this->dlgCopyingDestination->Enabled = false;
            }

            if (count($this->tempItems) == 0 || $this->dlgCopyingDestination->SelectedValue == null) {
                Application::executeJavaScript("$('.modal-footer .btn-orange').attr('disabled', 'disabled');");
            } else {
                Application::executeJavaScript("$('.modal-footer .btn-orange').removeAttr('disabled', 'disabled');");
            }

            $this->dlgModal22->showDialogBox();  // Copy files or folders
        }

        /**
         * Initiates the process of copying selected items to a specified destination.
         * Handles errors for invalid destination paths, executes the copy operation,
         * and updates the UI or handles the result accordingly.
         *
         * @param ActionParams $params Parameters for the action that trigger the copying process.
         *
         * @return void This method does not return a value but performs operations such as validation,
         *              file copying and result handling.
         * @throws Caller
         */
        public function startCopyingProcess_Click(ActionParams $params): void
        {
            $objPath = $this->dlgCopyingDestination->SelectedValue;

            if (!$objPath) {
                $this->handleCopyError();
                return;
            }

            $this->dlgModal22->hideDialogBox(); // Copy files or folders

            if ($this->dlgCopyingDestination->SelectedValue !== null) {
                foreach ($this->tempSelectedItems as $selectedItem) {
                    $this->fullCopyItem($selectedItem, $objPath);
                }
            }

            $this->handleCopyResult();
        }

        // Helper functions

        /**
         * Handles the error encountered during a copy operation by resetting the destination, displaying an error, and
         * performing cleanup tasks.
         *
         * @return void This method does not return a value as it focuses on error handling and cleanup processes.
         * @throws Caller
         */
        private function handleCopyError(): void
        {
            $this->resetDestinationAndDisplayError();
            $this->cleanupAfterCopy();
        }

        /**
         * Resets the destination selection and displays an error message in the modal.
         * Updates the modal's appearance and functionality by modifying the DOM elements to indicate an error state.
         * Ensures that the destination dropdown is reset with the default option when no value is selected.
         *
         * @return void This method does not return a value but executes JavaScript to update the modal's state and
         *     appearance.
         * @throws Caller
         */
        private function resetDestinationAndDisplayError(): void
        {
            if ($this->dlgCopyingDestination->SelectedValue == null) {
                $this->dlgCopyingDestination->removeAllItems();
                $this->dlgCopyingDestination->AddItem(t('- Select One -'));

                Application::executeJavaScript("
                    $('.modal-header').removeClass('btn-default').addClass('btn-danger');
                    $('.destination-error').removeClass('hidden');
                    $('.source-title').addClass('hidden');
                    $('.source-path').addClass('hidden');
                    $('.modal-footer .btn-orange').attr('disabled', 'disabled');
                ");
            }
        }

        /**
         * Copies the specified item from a source path to a destination path, maintaining the item's structure and contents.
         *
         * @param string $selectedItem The name or relative path of the item to be copied.
         * @param string $objPath An associative array containing the key 'path', which specifies the destination directory.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        private function fullCopyItem(string $selectedItem, string $objPath): void
        {
            $sourcePath = $this->objManager->RootPath . $selectedItem;
            $destinationPath = $this->objManager->RootPath . $objPath . "/" . basename($selectedItem);

            // Perform the copying logic
            $this->fullCopy($sourcePath, $destinationPath);
        }

        /**
         * Handles the result of a copy operation by determining success or failure based on the number of stored checks and temporary items.
         * Displays the appropriate modal dialog box and performs cleanup after the copying process.
         *
         * @return void This method does not return a value. It manages the state and side effects of the copy operation.
         */
        private function handleCopyResult(): void
        {
            if ($this->intStoredChecks >= count($this->tempItems)) {
                $this->dlgModal23->showDialogBox(); // The selected files and folders have been successfully copied!
            } else {
                $this->dlgModal24->showDialogBox(); // Error copying items!
            }

            // Clean up after the copying process
            $this->cleanupAfterCopy();
        }

        /**
         * Cleans up temporary data and refreshes the object manager after a copy operation.
         *
         * @return void This method does not return a value.
         */
        private function cleanupAfterCopy(): void
        {
            unset($this->tempSelectedItems);
            unset($this->tempItems);
            unset($this->objLockedDirs);
            $this->objManager->refresh();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // DELETE

        /**
         * Handles the click event for the delete button by performing validation checks and initiating the delete
         * operation.
         *
         * @param ActionParams $params Parameters passed to the action, which may contain context-specific data for the
         *     click event.
         *
         * @return void No value is returned. The method either performs an action, displays a specific dialog box for
         *     validation errors, or initiates the delete operation for selected folders or files.
         * @throws Caller
         * @throws RandomException
         */
        public function btnDelete_Click(ActionParams $params): void
        {
            if (!Application::verifyCsrfToken()) {
                $this->dlgModal47->showDialogBox();
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                return;
            }

            clearstatcache();

            if ($this->dataScan() !== $this->scan($this->objManager->RootPath)) {
                $this->dlgModal1->showDialogBox(); // A corrupted table "folders" in the database or directory "upload" in the file system! ...
                return;
            }

            if (!$this->arrSomeArray) {
                $this->dlgModal20->showDialogBox(); // Please choose specific folder(s) or file(s)!
                return;
            }

            if ($this->arrSomeArray[0]["data-id"] == 1 && $this->arrSomeArray[0]["data-path"] == "") {
                $this->dlgModal26->showDialogBox(); // It's not possible to delete the root directory!
                return;
            }

            if ($this->arrSomeArray[0]["data-activities-locked"] == 1) {
                $this->dlgModal25->showDialogBox(); // Sorry, but this reserved folder or file cannot be deleted!
                return;
            }

            $this->initializeDeleteOperation();
        }

        // Helper functions

        /**
         * Initializes the delete operation by loading all folders and files, preparing and processing data for deletion,
         * and handling the necessary user interface updates associated with the delete operation.
         *
         * @return void This method does not return any value; it performs the required setup and UI handling for the
         *     delete operation.
         * @throws Caller
         */
        private function initializeDeleteOperation(): void
        {
            $objFolders = Folders::loadAll();
            $objFiles = Files::loadAll();

            // Prepare and send data to function fullRemove($dir)
            $this->prepareDeleteData();

            // Data validation and processing
            $this->processDeleteData($objFolders, $objFiles);

            // UI-related operations
            $this->deleteListDialog();
        }

        /**
         * Prepares data by extracting paths from an internal array and storing them in a temporary property for further processing.
         *
         * @return void This method does not return a value but modifies the object's state by populating the tempSelectedItems property with data-path values.
         */
        private function prepareDeleteData(): void
        {
            // Preparing and sending data to the function fullRemove($dir)

            $tempArr = [];

            foreach ($this->arrSomeArray as $arrSome) {
                $tempArr[] = $arrSome;
            }
            foreach ($tempArr as $temp) {
                if ($temp['data-path']) {
                    $this->tempSelectedItems[] = $temp['data-path'];
                }
            }
        }

        /**
         * Handles the processing of deleting specified folders and files.
         *
         * @param array $objFolders The object or data structure representing the folders to be deleted.
         * @param array $objFiles The object or data structure representing the files to be deleted.
         *
         * @return void
         * @throws Caller
         */
        private function processDeleteData(array $objFolders, array $objFiles): void
        {
            // Processing logic for deleting data

            $this->deleteDirectory($objFolders, $objFiles);
            $this->deleteFile($objFiles);

        }

        /**
         * Processes the deletion of directories and their associated files by scanning folder IDs,
         * checking for locked files, and preparing a list of paths to be removed.
         *
         * @param array $objFolders An array of folder objects to be processed for deletion.
         * @param array $objFiles An array of file objects to be checked and processed for deletion.
         *
         * @return void
         * @throws Caller
         */
        private function deleteDirectory(array $objFolders, array $objFiles): void
        {
            $dataFolders = [];
            $dataFiles = [];
            $tempIds = [];

            foreach ($this->arrSomeArray as $arrSome) {
                if ($arrSome["data-item-type"] == "dir") {
                    $dataFolders[] = $arrSome["data-id"];
                }
            }

            foreach ($dataFolders as $dataFolder) {
                $tempIds = array_merge($tempIds, $this->fullScanIds($dataFolder));
            }

            foreach ($objFiles as $objFile) {
                foreach ($tempIds as $tempId) {
                    if ($objFile->getFolderId() == $tempId) {
                        $dataFiles[] = $objFile->getId();
                    }
                }
            }

            // Here have to check whether the files are locked
            foreach ($objFiles as $objFile) {
                foreach ($dataFiles as $dataFile) {
                    if ($objFile->getId() == $dataFile) {
                        if ($objFile->getLockedFile() === 1) {
                            $this->objLockedFiles++;
                        }
                    }
                }
            }

            foreach ($objFolders as $objFolder) {
                foreach ($tempIds as $tempId) {
                    if ($objFolder->getId() == $tempId) {
                        $this->tempItems[] = $objFolder->getPath();
                    }
                }
                sort($this->tempItems);
            }
            foreach ($objFiles as $objFile) {
                foreach ($dataFiles as $dataFile) {
                    if ($objFile->getId() == $dataFile) {
                        $this->tempItems[] = $objFile->getPath();
                    }
                }
                sort($this->tempItems);
            }
        }

        /**
         * Deletes files by iterating through a passed collection of file objects and matching them with a pre-defined array of file IDs.
         * If a match is found, the file's path is added to a temporary array, and locked files are counted.
         *
         * @param array $objFiles An array of file objects, each providing methods to retrieve their ID, path, and locked status.
         *
         * @return void This method does not return a value.
         */
        private function deleteFile(array $objFiles): void
        {
            $dataFiles = [];

            foreach ($this->arrSomeArray as $arrSome) {
                if ($arrSome["data-item-type"] == "file") {
                    $dataFiles[] = $arrSome["data-id"];
                }
            }

            foreach ($objFiles as $objFile) {
                foreach ($dataFiles as $dataFile) {
                    if ($objFile->getId() == $dataFile) {

                        if ($objFile->getId() == $dataFile) {
                            $this->tempItems[] = $objFile->getPath();
                        }
                        // Here have to check whether the files are locked
                        if ($objFile->getLockedFile() > 0) {
                            $this->objLockedFiles++;
                        }
                    }
                }
            }
        }

        /**
         * Configures and displays a modal dialog for deleting lists, updating the UI elements
         * to reflect the current state of the deletion process, including handling locked files
         * and allowing the user to proceed with or cancel the operation.
         *
         * @return void This method does not return a value. It updates the UI and displays the dialog box.
         * @throws Caller
         */
        private function deleteListDialog(): void
        {
            // Update list dialog UI

            // Show folder and file names before deletion
            if (count($this->tempItems) !== 0) {
                $source = implode(', ', $this->tempItems);
                $this->lblDeletePath->Text = $source;
            }

            // Here have to check if some files have already been locked before.
            //If so, cancel and select unlocked files again...
            if ($this->objLockedFiles !== 0) {
                Application::executeJavaScript("
                    $('.deletion-warning-text').addClass('hidden');
                    $('.deletion-info-text').addClass('hidden');
                    $('.delete-error-text').removeClass('hidden');
                    $('.delete-info-text').removeClass('hidden');
                    $('.modal-footer .btn-orange').attr('disabled', 'disabled');
                ");
            } else {
                Application::executeJavaScript("
                    $('.deletion-warning-text').removeClass('hidden');
                    $('.deletion-info-text').removeClass('hidden');
                    $('.delete-error-text').addClass('hidden');
                    $('.delete-info-text').addClass('hidden');
                    $('.modal-footer .btn-orange').removeAttr('disabled', 'disabled');
                ");
            }

            $this->dlgModal27->showDialogBox(); // Delete files or folders
        }

        /**
         * Initiates the deletion process for selected items and updates the application state accordingly.
         *
         * @param ActionParams $params Parameters associated with the action triggering the deletion process.
         *
         * @return void No value is returned as the method performs the deletion process and updates the dialog box state.
         * @throws UndefinedPrimaryKey
         * @throws Caller
         * @throws InvalidCast
         */
        public function startDeletionProcess_Click(ActionParams $params): void
        {
            $this->dlgModal27->hideDialogBox(); // Delete files or folders

            foreach ($this->tempSelectedItems as $tempSelectedItem) {
                $this->fullRemoveItem($tempSelectedItem);
            }

            $this->handleDeletionResult();
        }

        // Helper functions

        /**
         * Removes an item fully from the system by its path.
         *
         * @param string $tempSelectedItem The selected item path to be removed, relative to the root path.
         *
         * @return void
         * @throws UndefinedPrimaryKey
         * @throws Caller
         * @throws InvalidCast
         */
        private function fullRemoveItem(string $tempSelectedItem): void
        {
            $itemPath = $this->objManager->RootPath . $tempSelectedItem;

            // Perform the removal logic
            $this->fullRemove($itemPath);
        }

        /**
         * Handles the result of the deletion process by displaying an appropriate dialog box
         * based on the outcome and performing necessary cleanup operations.
         *
         * @return void
         */
        private function handleDeletionResult(): void
        {
            if ($this->intStoredChecks >= count($this->tempItems)) {
                $this->dlgModal28->showDialogBox(); // The selected files and folders have been successfully deleted!
            } else {
                $this->dlgModal29->showDialogBox(); // Error deleting items!
            }

            // Clean up after the deletion process
            $this->cleanupAfterDeletion();
        }

        /**
         * Cleans up temporary and locked file data after a deletion operation by unsetting relevant properties.
         *
         * @return void
         */
        private function cleanupAfterDeletion(): void
        {
            unset($this->tempSelectedItems);
            unset($this->objLockedFiles);
            unset($this->tempItems);
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // MOVE

        /**
         * Handles the click event on the "Move" button. This method performs the necessary operations
         * to process file and folder relocation, including validation, data preparation, and UI updates.
         *
         * @param ActionParams $params The parameters associated with the button click event, typically holding context regarding the action performed.
         *
         * @return void This method does not return any value but performs multiple internal operations to handle the move process.
         * @throws Caller
         * @throws RandomException
         */
        public function btnMove_Click(ActionParams $params): void
        {
            if (!Application::verifyCsrfToken()) {
                $this->dlgModal47->showDialogBox();
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                return;
            }

            $objFolders = Folders::loadAll();
            $objFiles = Files::loadAll();

            // Check for conditions preventing relocation
            if (!$this->validateMoveConditions()) {
                return;
            }

            // Prepare and send data to function fullMove($src, $dst)
            $this->prepareMoveData();

            // Data validation and processing
            $this->processMoveData($objFolders, $objFiles);

            // UI-related operations
            $this->updateMoveDestinationDialog();

            // Show the move dialog
            $this->showMoveDialog();
        }

        // Helper functions

        /**
         * Validates the conditions required for moving files or folders.
         *
         * The function checks several preconditions to ensure the move operation is feasible and safe.
         * It evaluates system state, user selection, and restrictions tied to the move process.
         * Appropriate dialog boxes are displayed when validation fails, with specific error messages.
         *
         * @return bool Returns true if all move conditions are valid; otherwise, false.
         * @throws Caller
         */
        private function validateMoveConditions(): bool
        {
            clearstatcache();

            if ($this->dataScan() !== $this->scan($this->objManager->RootPath)) {
                $this->dlgModal1->showDialogBox(); // A corrupted table "folders" in the database or directory "upload" in the file system! ...
                return false;
            }

            if (!$this->arrSomeArray) {
                $this->dlgModal20->showDialogBox(); // Please choose specific folder(s) or file(s)!
                return false;
            }

            if ($this->arrSomeArray[0]["data-id"] == 1 && $this->arrSomeArray[0]["data-path"] == "") {
                $this->dlgModal30->showDialogBox(); // It's not possible to move to the root directory!
                return false;
            }

            if ($this->arrSomeArray[0]["data-activities-locked"] == 1) {
                $this->dlgModal31->showDialogBox(); // Sorry, but this reserved folder or file cannot be moved!
                return false;
            }

            return true;
        }

        /**
         * Prepares data for the move operation by processing an internal array and extracting specific data paths.
         *
         * @return void
         */
        private function prepareMoveData(): void
        {
            // Preparing and sending data to the function fullMove($src, $dst)

            $tempArr = [];

            foreach ($this->arrSomeArray as $arrSome) {
                $tempArr[] = $arrSome;
            }
            foreach ($tempArr as $temp) {
                if ($temp['data-path']) {
                    $this->tempSelectedItems[] = $temp['data-path'];
                }
            }
        }

        /**
         * Processes the data necessary for moving directories and files.
         *
         * @param array $objFolders The folder data to process for moving.
         * @param array $objFiles The files data to process for moving.
         *
         * @return void
         * @throws Caller
         */
        private function processMoveData(array $objFolders, array $objFiles): void
        {
            // Processing logic for moving data

            $this->moveDirectory($objFolders, $objFiles);
            $this->moveFile($objFiles);
        }

        /**
         * Moves a directory along with its contained files and folders to a new location.
         *
         * @param array $objFolders An array of folder objects to be moved.
         * @param array $objFiles An array of file objects to be moved.
         *
         * @return void
         * @throws Caller
         */
        private function moveDirectory(array $objFolders, array $objFiles): void
        {
            // Perform directory moving logic

            $dataFolders = [];
            $dataFiles = [];
            $tempIds = [];

            foreach ($this->arrSomeArray as $arrSome) {
                if ($arrSome["data-item-type"] == "dir") {
                    $dataFolders[] = $arrSome["data-id"];
                }
            }

            foreach ($dataFolders as $dataFolder) {
                $tempIds = array_merge($tempIds, $this->fullScanIds($dataFolder));
            }

            foreach ($objFiles as $objFile) {
                foreach ($tempIds as $tempId) {
                    if ($objFile->getFolderId() == $tempId) {
                        $dataFiles[] = $objFile->getId();
                    }
                }
            }

            // Here have to check whether the files are locked
            foreach ($objFiles as $objFile) {
                foreach ($dataFiles as $dataFile) {
                    if ($objFile->getId() == $dataFile) {
                        if ($objFile->getLockedFile() == 1) {
                            $this->objLockedFiles++;
                        }
                    }
                }
            }

            foreach ($objFolders as $objFolder) {
                foreach ($tempIds as $tempId) {
                    if ($objFolder->getId() == $tempId) {
                        $this->tempItems[] = $objFolder->getPath();
                    }
                }
                sort($this->tempItems);
            }
            foreach ($objFiles as $objFile) {
                foreach ($dataFiles as $dataFile) {
                    if ($objFile->getId() == $dataFile) {
                        $this->tempItems[] = $objFile->getPath();
                    }
                }
                sort($this->tempItems);
            }
        }

        /**
         * Moves specified files by performing necessary logic and updates internal properties.
         *
         * @param array $objFiles An array of file objects to be moved. Each file object must implement methods like getId, getPath, and getLockedFile.
         *
         * @return void
         */
        private function moveFile(array $objFiles): void
        {
            // Perform file moving logic

            $dataFiles = [];

            foreach ($this->arrSomeArray as $arrSome) {
                if ($arrSome["data-item-type"] == "file") {
                    $dataFiles[] = $arrSome["data-id"];
                }
            }

            foreach ($objFiles as $objFile) {
                foreach ($dataFiles as $dataFile) {
                    if ($objFile->getId() == $dataFile) {
                        if ($objFile->getId() == $dataFile) {
                            $this->tempItems[] = $objFile->getPath();
                        }

                        // Here have to check whether the files are locked
                        if ($objFile->getLockedFile() == 1) {
                            $this->objLockedFiles++;
                        }
                    }
                }
            }
        }

        /**
         * Updates the move destination dialog by analyzing directories and paths,
         * managing locks, and marking items for the UI based on specific conditions.
         *
         * @return void
         * @throws Caller
         */
        private function updateMoveDestinationDialog(): void
        {
            // Update destination dialog UI

            $objPaths = $this->scanForSelect();

            foreach ($this->tempItems as $tempItem) {
                if (is_dir($this->objManager->RootPath . $tempItem)) {
                    $this->objLockedDirs[] = $tempItem;
                }
            }

            if ($objPaths) foreach ($objPaths as $objPath) {
                if ($objPath['activities_locked'] == 1) {
                    $this->objLockedDirs[] = $objPath["path"];
                }
            }

            foreach ($objPaths as $folder) {
                $level = $folder['depth'];
                if ($this->checkString($folder['path'])) {
                    $level = 0;
                }

                if (($folder['activities_locked'] == 1) ||
                    ($this->arrSomeArray[0]["data-path"] ==  $folder['path']) ||
                    ($this->arrSomeArray[0]["data-parent-id"] ==  $folder['id'])
                ) {
                    $mark = true;
                } else {
                    $mark = false;
                }

                $this->dlgMovingDestination->AddItem($this->printDepth($folder['name'], $level), $folder['path'], null, $mark);
            }
        }

        /**
         * Displays the move dialog where users can manage and confirm their moving operations.
         * This method populates the dialog with folder and file names, checks if any files
         * are locked, and enforces rules around move permissions and destination selection.
         *
         * @return void
         * @throws Caller
         */
        private function showMoveDialog(): void
        {
            // Show the move dialog

            // Show folder and file names before moving
            if (count($this->tempItems) !== 0) {
                $source = implode(', ', $this->tempItems);
                $this->lblMovingCoursePath->Text = $source;
            }

            // Here have to check if some files have already been locked before.
            //If so, cancel and select unlocked files again...
            if ($this->objLockedFiles !== 0) {
                Application::executeJavaScript("
                    $('.modal-header').removeClass('btn-default').addClass('btn-danger');
                    $('.move-error-text').removeClass('hidden');
                    $('.move-info-text').removeClass('hidden');
                    $('.js-moving-destination').prop('disabled', true); 
                    $('.modal-footer .btn-orange').attr('disabled', 'disabled');
                ");
            } else {
                Application::executeJavaScript("
                    $('.modal-header').removeClass('btn-danger').addClass('btn-default');
                    $('.move-error-text').addClass('hidden');
                    $('.move-info-text').addClass('hidden');
                    $('.js-moving-destination').prop('disabled', false); 
                    $('.modal-footer .btn-orange').removeAttr('disabled', 'disabled');
                ");
            }

            if ($this->dlgMovingDestination->SelectedValue == null) {
                Application::executeJavaScript("$('.modal-footer .btn-orange').attr('disabled', 'disabled');");
            } else {
                Application::executeJavaScript("$('.modal-footer .btn-orange').removeAttr('disabled', 'disabled');");
            }

            $this->dlgModal32->showDialogBox(); // Move files or folders
        }

        /**
         * Handles the click event for starting the moving process of files or folders.
         *
         * @param ActionParams $params Parameters associated with the action event, such as user interaction data.
         *
         * @return void
         * @throws Caller
         */
        public function startMovingProcess_Click(ActionParams $params): void
        {
            $objPath = $this->dlgMovingDestination->SelectedValue;

            if (!$objPath) {
                $this->handleMovingError();
                return;
            }

            $this->dlgMovingDestination->Enabled = false;

            $this->dlgModal32->hideDialogBox(); // Move files or folders

            if ($this->dlgMovingDestination->SelectedValue !== null) {
                foreach ($this->tempSelectedItems as $selectedItem) {
                    $this->fullMoveItem($selectedItem, $objPath);
                }
            }

            $this->handleMovingResult();
        }

        // Helper functions

        /**
         * Moves the selected item from its source path to the specified destination path.
         *
         * @param string $selectedItem The name or path of the item to be moved.
         * @param string $objPath An associative array containing the destination path details.
         *                        Example: ['path' => 'target_directory']
         *
         * @return void
         * @throws UndefinedPrimaryKey
         * @throws Caller
         * @throws InvalidCast
         */
        private function fullMoveItem(string $selectedItem, string $objPath): void
        {
            $sourcePath = $this->objManager->RootPath . $selectedItem;
            $destinationPath = $this->objManager->RootPath . $objPath . "/" . basename($selectedItem);

            // Perform the move logic
            $this->fullMove($sourcePath, $destinationPath);
        }

        /**
         * Handles errors related to the moving destination selection.
         * Ensures that a default value is added to the moving destination dropdown
         * if no value is selected and updates the interface to indicate the error state.
         *
         * @return void
         * @throws Caller
         */
        private function handleMovingError(): void
        {
            if ($this->dlgMovingDestination->SelectedValue == null) {
                $this->dlgMovingDestination->removeAllItems();
                $this->dlgMovingDestination->AddItem(t('- Select One -'));

                Application::executeJavaScript("
                   $('.modal-header').removeClass('btn-default').addClass('btn-danger');
                   $('.destination-moving-error').removeClass('hidden');
                   $('.moving-source-title').addClass('hidden');
                   $('.moving-source-path').addClass('hidden');
                   $('.modal-footer .btn-orange').attr('disabled', 'disabled');
                ");
            }
        }

        /**
         * Handles the result of the moving process.
         *
         * This method determines whether the moving operation was successful based on
         * the comparison of stored checks and the total number of temporary items. It
         * displays the appropriate dialog box indicating success or failure and then
         * performs cleanup operations after the moving process.
         *
         * @return void
         */
        private function handleMovingResult(): void
        {
            if ($this->intStoredChecks >= count($this->tempItems)) {
                $this->dlgModal33->showDialogBox(); // The selected files and folders have been successfully moved!
            } else {
                $this->dlgModal34->showDialogBox(); // Error moving items!
            }

            // Clean up after the moving process
            $this->cleanupAfterMoving();
        }

        /**
         * Cleans up temporary data and resets the state after items have been moved.
         *
         * This method removes temporary selected items, locked files, temporary items,
         * and locked directories. It also refreshes the manager to ensure a consistent state.
         *
         * @return void
         */
        private function cleanupAfterMoving(): void
        {
            unset($this->tempSelectedItems);
            unset($this->objLockedFiles);
            unset($this->tempItems);
            unset($this->objLockedDirs);
            $this->objManager->refresh();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Handles the data clearing operation for the user interface elements, variables, and session storage.
         *
         * @return void
         * @throws Caller
         */
        public function dataClearing_Click(): void
        {
            // Clearing form elements
            $this->txtAddFolder->Text = '';
            $this->dlgCopyingDestination->SelectedValue = '';
            $this->dlgMovingDestination->SelectedValue = '';
            $this->clearDropdownOptions($this->dlgCopyingDestination);
            $this->clearDropdownOptions($this->dlgMovingDestination);

            // Unset variables
            $this->clearVariables();

            // Clearing session storage
            Application::executeJavaScript("sessionStorage.clear();");
        }

        // Helper functions

        /**
         * Clears all existing options from a dropdown and adds a default placeholder option.
         *
         * @param object $dropdown The dropdown component from which all options will be removed and the placeholder option will be added.
         *
         * @return void
         */
        private function clearDropdownOptions(object $dropdown): void
        {
            $dropdown->removeAllItems();
            $dropdown->AddItem(t('- Select One -'), null);
        }

        /**
         * Clears specific variables by unsetting their values.
         *
         * This method is used to reset the state of various class properties
         * related to temporary selections, data identifiers, and locked items.
         *
         * @return void
         */
        private function clearVariables(): void
        {
            unset($this->tempSelectedItems);
            unset($this->objLockedFiles);
            unset($this->tempItems);
            unset($this->intDataId);
            unset($this->strDataName);
            unset($this->strDataPath);
            unset($this->intDataLocked);
            unset($this->objLockedDirs);
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Check the synchronicity of folders and database.
         * If they don't match, FileManager is broken.
         * The reason for this can be either the "folders" table is corrupted or the file system of the "upload" folder is corrupted or an empty folder.
         * In this case, help should be asked from the developer or webmaster.
         *
         * Here is one way to immediately with the code below (with example):
         *
         * $path = $this->objManager->RootPath;
         * print "<pre>";
         * print "<br>DATASCAN:<br>";
         * print_r($this->dataScan());
         * print "<br>SCAN:<br>";
         * print_r($this->scan($path));
         * print "</pre>";
         *
         * @return array The sorted list of folder paths, excluding the first element.
         * @throws Caller
         */
        protected function dataScan(): array
        {
            $folders = Folders::loadAll();

            // Use an array map to extract paths.
            $arr = array_map(function ($folder) {
                return $folder->getPath();
            }, $folders);

            // Remove the first element from the array
            array_shift($arr);
            // Sort the paths.
            sort($arr);

            return $arr;
        }

        /**
         * Recursively scans a directory and retrieves a sorted list of folder paths relative to a predefined base.
         *
         * @param string $path The directory path to scan.
         *
         * @return array An array of relative folder paths sorted alphabetically.
         */
        protected function scan(string $path): array
        {
            $folders = [];

            if (file_exists($path)) {
                foreach (scandir($path) as $f) {
                    if ($f[0] == '.') {
                        continue;
                    }

                    $fullPath = $path . DIRECTORY_SEPARATOR . $f;

                    if (is_dir($fullPath)) {
                        $folders[] = $this->objManager->getRelativePath($fullPath);
                        array_push($folders, ...$this->scan($fullPath));
                    }
                }
            }

            sort($folders);

            return $folders;
        }

        /**
         * Recursively retrieves all descendant folder IDs, including the given parent ID.
         *
         * @param int $parentId The ID of the parent folder to begin scanning for descendants.
         *
         * @return array An array of all descendant folder IDs, including the provided parent ID.
         * @throws Caller
         */
        protected function fullScanIds(int $parentId): array
        {
            $objFolders = Folders::loadAll();
            $descendantIds = [];

            foreach ($objFolders as $objFolder) {
                if ($objFolder->ParentId == $parentId) {
                    array_push($descendantIds, ...$this->fullScanIds($objFolder->Id));
                }
            }

            $descendantIds[] = $parentId;

            return $descendantIds;
        }

        /**
         * Scans and retrieves a sorted list of folders with their associated details.
         * The method loads all folders, gathers data for each folder including id, parent id,
         * name, path, depth, and activities locked status, and then sorts the folders by their paths.
         *
         * @return array Returns a sorted array of folder data. Each folder's data includes:
         *               - id: The unique identifier of the folder.
         *               - parent_id: The identifier of the parent folder.
         *               - name: The name of the folder.
         *               - path: The complete path of the folder.
         *               - depth: The depth of the folder in the hierarchy based on the path.
         *               - activities_locked: The status indicating whether activities are locked for the folder.
         * @throws Caller
         */
        protected function scanForSelect(): array
        {
            $folders = Folders::loadAll();
            $folderData = [];
            $sortedNames = [];

            foreach ($folders as $folder) {
                $folderData[] = [
                    'id' => $folder->getId(),
                    'parent_id' => $folder->getParentId(),
                    'name' => $folder->getName(),
                    'path' => $folder->getPath(),
                    'depth' => substr_count($folder->getPath(), '/'),
                    'activities_locked' => $folder->getActivitiesLocked(),
                ];
            }

            foreach ($folderData as $key => $val) {
                $sortedNames[$key] = strtolower($val['path']);
            }

            array_multisort($sortedNames, SORT_ASC, $folderData);

            return $folderData;
        }

        /**
         * Validates a given string by checking if it contains at most one segment after splitting by slashes.
         *
         * @param string $str The input string to be checked.
         *
         * @return bool Returns true if the string has at most one segment or the second segment is empty, otherwise false.
         */
        protected function checkString(string $str): bool
        {
            // Remove leading and trailing spaces
            $str = trim($str);

            // Split the string based on the slashes
            $parts = explode('/', $str);

            // We check if there are more parts after the first element
            return count($parts) <= 2 && empty($parts[1]);
        }

        /**
         * Generates an indented string representation of a name based on the specified depth.
         * The indentation is created using a predefined spacer, adjusted according to the depth.
         *
         * @param string $name The name to be formatted and indented.
         * @param int $depth The depth level for indentation. A depth of 0 applies no indentation.
         *
         * @return string The formatted and indented string representation of the name.
         */
        protected function printDepth(string $name, int $depth): string
        {
            $spacer = str_repeat('&nbsp;', 5); // Adjust the number as needed for your indentation.

            if ($depth !== 0) {
                $strHtml = str_repeat(html_entity_decode($spacer), $depth) . ' ' . t($name);
            } else {
                $strHtml = t($name);
            }

            return $strHtml;
        }

        /**
         * Moves all contents from the source location to the destination location by copying first and then removing the original files.
         *
         * @param string $src The source directory or file path.
         * @param string $dst The destination directory or file path.
         *
         * @return void
         * @throws UndefinedPrimaryKey
         * @throws Caller
         * @throws InvalidCast
         */
        protected function fullMove(string $src, string $dst): void
        {
            $this->fullCopy($src, $dst);
            $this->fullRemove($src);
        }

        /**
         * Recursively copies a file or directory from source to destination
         * while managing metadata and associated operations.
         *
         * @param string $src The source path to copy from. It can be a file or directory.
         * @param string $dst The destination path to copy to.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        protected function fullCopy(string $src, string $dst): void
        {
            $objId = $this->getIdFromParent($dst);

            if ($objId) {
                $objFolder = Folders::loadById($objId);
                if ($objFolder->getLockedFile() !== 1) {
                    $objFolder->setMtime(filemtime(dirname($dst)));
                    $objFolder->setLockedFile(1);
                    $objFolder->save();
                }
            }

            $dirname = $this->objManager->removeFileName($dst);
            $name = pathinfo($dst, PATHINFO_FILENAME);
            $ext = pathinfo($dst, PATHINFO_EXTENSION);

            if (is_dir($src)) {
                if (file_exists($dirname . '/' . basename($name))) {
                    $inc = 1;
                    while (file_exists($dirname . '/' . $name . '-' . $inc)) $inc++;
                    $dst = $dirname . '/' . $name . '-' . $inc;
                }

                Folder::makeDirectory($dst, 0777);

                $objFolder = new Folders();
                $objFolder->setParentId($objId);
                $objFolder->setPath($this->objManager->getRelativePath(realpath($dst)));
                $objFolder->setName(basename($dst));
                $objFolder->setType("dir");
                $objFolder->setMtime(filemtime($dst));
                $objFolder->save();

                foreach ($this->tempFolders as $tempFolder) {
                    Folder::makeDirectory($this->objManager->TempPath . '/_files/' . $tempFolder . $this->objManager->getRelativePath($dst),0777);
                }

                $files = array_diff(scandir($src), array('..', '.'));
                foreach($files as $file) {
                    $this->fullCopy("$src" . "/" . "$file", "$dst" . "/". "$file");
                }

            } else if (file_exists($src)) {
                if (file_exists($dirname . '/' . basename($name) . '.' . $ext)) {
                    $inc = 1;
                    while (file_exists($dirname . '/' . $name . '-' . $inc . '.' . $ext)) $inc++;
                    $dst = $dirname . '/' . $name . '-' . $inc . '.' . $ext;
                }

                copy($src,$dst);

                if (in_array(strtolower($ext), $this->arrAllowed)) {
                    foreach ($this->tempFolders as $tempFolder) {
                        copy($this->objManager->TempPath . '/_files/' . $tempFolder . $this->objManager->getRelativePath($src),$this->objManager->TempPath . '/_files/' . $tempFolder . $this->objManager->getRelativePath($dst));
                    }
                }

                $objFiles = new Files();
                $objFiles->setFolderId($objId);
                $objFiles->setName(basename($dst));
                $objFiles->setType("file");
                $objFiles->setPath($this->objManager->getRelativePath(realpath($dst)));
                $objFiles->setExtension($this->objManager->getExtension($dst));
                $objFiles->setMimeType($this->objManager->getMimeType($dst));
                $objFiles->setSize(filesize($dst));
                $objFiles->setMtime(filemtime($dst));
                $objFiles->setDimensions($this->objManager->getDimensions($dst));
                $objFiles->save();
            }

            if (file_exists($dst)) {
                $this->intStoredChecks++;
            }

            $this->objManager->refresh();
            clearstatcache();
        }

        /**
         * Retrieves the ID of a folder based on its parent path.
         *
         * @param string $path The file path from which the parent folder's ID will be determined.
         *
         * @return int|null The ID of the folder if a match is found, 1 if the path is empty, or null if no match is found.
         * @throws Caller
         */
        protected function getIdFromParent(string $path): ?int
        {
            $objFolders = Folders::loadAll();
            $objPath = $this->objManager->getRelativePath(realpath(dirname($path)));

            foreach ($objFolders as $objFolder) {
                if ($objPath == $objFolder->getPath()) {
                    return $objFolder->getId();
                }
            }

            // Handle the case where no matching folder is found.
            return ($objPath == "") ? 1 : null;
        }

        /**
         * Recursively removes a directory or file, including associated database entries and temporary files.
         *
         * @param string $dir The path of the directory or file to be removed.
         *
         * @return void
         * @throws UndefinedPrimaryKey
         * @throws Caller
         * @throws InvalidCast
         */
        protected function fullRemove(string $dir): void
        {
            $objFolders = Folders::loadAll();
            $objFiles = Files::loadAll();

            if (is_dir($dir)) {
                $files = array_diff(scandir($dir), array('..', '.'));

                foreach ($files as $file) {
                    $this->fullRemove($dir . "/" . $file);
                }

                foreach ($objFolders as $objFolder) {
                    if ($objFolder->getPath() == $this->objManager->getRelativePath($dir)) {
                        if ($objFolder->getId()) {
                            $obj = Folders::loadById($objFolder->getId());
                            $obj->delete();
                            $this->intStoredChecks++;
                        }
                    }
                }

                if (file_exists($dir)) {
                    rmdir($dir);

                    foreach ($this->tempFolders as $tempFolder) {
                        $tempPath = $this->objManager->TempPath . '/_files/' . $tempFolder . $this->objManager->getRelativePath($dir);
                        if (is_dir($tempPath)) {
                            rmdir($tempPath);
                        }
                    }
                }
            } elseif (file_exists($dir)) {
                foreach ($objFiles as $objFile) {
                    if ($objFile->getPath() == $this->objManager->getRelativePath($dir)) {
                        if ($objFile->getId()) {
                            $obj = Files::loadById($objFile->getId());
                            $obj->delete();
                            $this->intStoredChecks++;
                        }
                    }
                }

                unlink($dir);

                foreach ($this->tempFolders as $tempFolder) {
                    $tempPath = $this->objManager->TempPath . '/_files/' . $tempFolder . $this->objManager->getRelativePath($dir);
                    if (is_file($tempPath)) {
                        unlink($tempPath);
                    }
                }
            }

            $dirname = dirname($dir);
            if (is_dir($dirname)) {
                $folders = glob($dirname . '/*', GLOB_ONLYDIR);
                $files = array_filter(glob($dirname . '/*'), 'is_file');

                foreach ($objFolders as $objFolder) {
                    if ($objFolder->getPath() == $this->objManager->getRelativePath($dirname)) {
                        if (count($folders) == 0 && count($files) == 0) {
                            $obj = Folders::loadById($objFolder->getId());
                            if ($obj->getLockedFile() == 1) {
                                $obj->setMtime(filemtime($dirname));
                                $obj->setLockedFile(0);
                                $obj->save();
                            }
                        }
                    }
                }
            }

            $this->objManager->refresh();
        }

        /**
         * Handles the change event for the destination dialog. Updates the UI components and
         * JavaScript behaviors based on the selected values of the copying and moving destinations,
         * as well as the state of `objLockedFiles`.
         *
         * @param ActionParams $params The parameters passed during the change event triggering, containing any relevant
         *     data.
         *
         * @return void This method does not return any value.
         * @throws Caller
         */
        public function dlgDestination_Change(ActionParams $params): void
        {
            if ($this->dlgCopyingDestination->SelectedValue || $this->dlgMovingDestination->SelectedValue) {
                Application::executeJavaScript("
                    $('.modal-header').removeClass('btn-danger').addClass('btn-default');
                    $('.destination-error').addClass('hidden');
                    $('.destination-moving-error').addClass('hidden');
                    $('.source-title').removeClass('hidden');
                    $('.moving-source-title').removeClass('hidden');
                    $('.source-path').removeClass('hidden');
                    $('.moving-source-path').removeClass('hidden');
                    $('.modal-footer .btn-orange').removeAttr('disabled', 'disabled');
                ");
            } else {
                Application::executeJavaScript("
                    $('.modal-header').removeClass('btn-default').addClass('btn-danger');
                    $('.destination-error').removeClass('hidden');
                    $('.destination-moving-error').removeClass('hidden');
                    $('.source-title').addClass('hidden');
                    $('.moving-source-title').addClass('hidden');
                    $('.source-path').addClass('hidden');
                    $('.moving-source-path').addClass('hidden');
                    $('.modal-footer .btn-orange').attr('disabled', 'disabled');
                ");
            }

            if ($this->objLockedFiles !== 0) {

                Application::executeJavaScript("
                   $('.modal-header').removeClass('btn-default').addClass('btn-danger');
                   //$('.destination-moving-error').removeClass('hidden');
                   $('.moving-source-title').addClass('hidden');
                   $('.moving-source-path').addClass('hidden');
                   $('.modal-footer .btn-orange').attr('disabled', 'disabled');
                ");
            } else {
                $this->dlgMovingDestination->Enabled = false;
            }
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Handles the click event for the image list view button. Updates the UI and object manager
         * state to reflect the image list view selection.
         *
         * @param ActionParams $params The parameters associated with the action event triggered by the button click.
         * @return void This method does not return a value.
         */
        public function btnImageListView_Click(ActionParams $params): void
        {
            $this->btnImageListView->addCssClass("active");
            $this->btnListView->removeCssClassesByPrefix("active");
            $this->btnBoxView->removeCssClassesByPrefix("active");

            $this->objManager->IsImageListView = true;
            $this->objManager->IsListView = false;
            $this->objManager->IsBoxView = false;
            $this->objManager->refresh();
        }

        /**
         * Handles the click event for the List View button. Activates the List View mode by
         * adding the active CSS class to the button and updating the object manager
         * to reflect the current view mode. Also adjusts the active states of other
         * view-related buttons.
         *
         * @param ActionParams $params Parameters related to the action event, such as
         *                             details about the user interaction triggering the event.
         * @return void This method does not return any value.
         */
        public function btnListView_Click(ActionParams $params): void
        {
            $this->btnListView->addCssClass("active");
            $this->btnImageListView->removeCssClassesByPrefix("active");
            $this->btnBoxView->removeCssClassesByPrefix("active");

            $this->objManager->IsListView = true;
            $this->objManager->IsImageListView = false;
            $this->objManager->IsBoxView = false;
            $this->objManager->refresh();
        }

        /**
         * Handles the click event for the btnBoxView button, activating the Box View layout.
         *
         * @param ActionParams $params The parameters associated with the action triggered by the button click.
         * @return void
         */
        public function btnBoxView_Click(ActionParams $params): void
        {
            $this->btnBoxView->addCssClass("active");
            $this->btnImageListView->removeCssClassesByPrefix("active");
            $this->btnListView->removeCssClassesByPrefix("active");

            $this->objManager->IsBoxView = true;
            $this->objManager->IsImageListView = false;
            $this->objManager->IsListView = false;
            $this->objManager->refresh();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Handles the insert button click event, validates CSRF token, checks if the current file is locked,
         * and triggers functionality to insert the selected file into a CKEditor instance.
         *
         * @param ActionParams $params The parameters passed to the button click event handler.
         *
         * @return void This method does not return any value.
         * @throws Caller
         * @throws RandomException
         */
        public function btnInsert_Click(ActionParams $params): void
        {
            if (!Application::verifyCsrfToken()) {
                $this->dlgModal47->showDialogBox();
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                return;
            }

            if ($this->arrSomeArray[0]["data-activities-locked"] == 1) {
                $this->dlgModal35->showDialogBox(); // Sorry, be cannot insert into a reserved file! ...
            } else {
                $fileId = $fullPath = $this->arrSomeArray[0]["data-id"];

                $fullPath = $this->objManager->RootPath . $this->arrSomeArray[0]["data-path"];
                $file = $this->objInfo->RootUrl . $this->arrSomeArray[0]["data-path"];
                $ext = pathinfo($fullPath, PATHINFO_EXTENSION);

                if (in_array(strtolower($ext), $this->arrAllowed)) {
                    $fileUrl = $this->objInfo->TempUrl . "/" . $this->lstSize->SelectedValue . $this->arrSomeArray[0]["data-path"];
                } else {
                    $fileUrl = $file;
                }

                // The code provided below is taken from this link:
                // https://ckeditor.com/docs/ckeditor4/latest/guide/dev_file_browser_api.html

                // Simulate user action of selecting a file to be returned to CKEditor.
                Application::executeJavaScript("
                var funcNum = getUrlParam('CKEditorFuncNum');
                var fileUrl = '$fileUrl';
                window.opener.CKEDITOR.tools.callFunction(funcNum, fileUrl, function() {
                    // Get the reference to a dialog window.
                    var dialog = this.getDialog();
                
                    // Check if this is the Image Properties dialog window.
                    if (dialog.getName() == 'image') {
                    // Get the reference to a text field that stores the 'id' attribute.
                        var element = dialog.getContentElement('advanced', 'linkId');
                        // Assign the new value.
                        if (element) 
                            element.setValue('$fileId');
                    } else {
                        // Check if this is the Link Properties dialog window.
                        if (dialog.getName() == 'link') {
                            // Get the reference to a text field that stores the 'id' attribute.
                            var element = dialog.getContentElement('advanced', 'advId');
                            // Assign the new value.
                            if (element)
                                element.setValue('$fileId');
                            }
                    }
                });
                
                window.close();
            ");
            }
        }

        /**
         * Handles the click event for the cancel button. Verifies the CSRF token and either
         * displays a modal dialog if the token is invalid or closes the window if the token is valid.
         *
         * @param ActionParams $params The parameters passed during the click event triggering, containing
         *     any relevant data.
         *
         * @return void This method does not return any value.
         * @throws Caller
         * @throws RandomException
         */
        public function btnCancel_Click(ActionParams $params): void
        {
            if (!Application::verifyCsrfToken()) {
                $this->dlgModal47->showDialogBox();
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                return;
            }

            Application::executeJavaScript("window.close();");
        }

    }

    DialogForm::run('DialogForm');
