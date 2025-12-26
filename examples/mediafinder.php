<?php
require('qcubed.inc.php');

use QCubed as Q;
use QCubed\Project\Control\FormBase as Form;
use QCubed\Exception\Caller;
use QCubed\Exception\InvalidCast;
use QCubed\Project\Control\Button;
use QCubed\Control\Panel;
use QCubed\Event\Click;
use QCubed\Action\Ajax;
use QCubed\Action\ActionParams;

/**
 * Class SampleForm4
 *
 * This example demonstrates how to call and use MediaFinder
 */

class SampleForm4 extends Form
{
    protected Q\Plugin\CKEditor $txtEditor;
    protected Q\Plugin\MediaFinder $objMediaFinder;
    protected Button $btnSubmit;
    protected Panel $pnlResult;
    protected Panel $pnlData;
    protected Panel $pnlIntroData;

    /**
     * Initializes and configures UI components such as text editor, media finder, buttons, and panels.
     * Includes the setup for handling user interactions with the components.
     *
     * @return void
     * @throws Caller
     * @throws InvalidCast
     */
    protected function formCreate(): void
    {
        // This is one possible example, suppose you have created a database table "example"
        // with one column "picture_ids" next to other columns.

        $objExample = Example::load(1);

        $this->txtEditor = new Q\Plugin\CKEditor($this);
        $this->txtEditor->Text = $objExample->getContent() ? $objExample->getContent() : null;
        $this->txtEditor->Configuration = 'ckConfig';
        $this->txtEditor->Rows = 15;

        $this->objMediaFinder = new Q\Plugin\MediaFinder($this);
        $this->objMediaFinder->TempUrl = APP_UPLOADS_TEMP_URL . "/_files/thumbnail";
        $this->objMediaFinder->PopupUrl = dirname(QCUBED_FILEMANAGER_ASSETS_URL) . "/examples/finder.php";
        $this->objMediaFinder->EmptyImageAlt = t("Choose a picture");
        $this->objMediaFinder->SelectedImageAlt = t("Selected picture");

        $this->objMediaFinder->SelectedImageId = $objExample->getPictureId() ? $objExample->getPictureId() : null;

        if ($this->objMediaFinder->SelectedImageId !== null) {
            $objFiles = Files::loadById($this->objMediaFinder->SelectedImageId);
            $this->objMediaFinder->SelectedImagePath = $this->objMediaFinder->TempUrl . $objFiles->getPath();
            $this->objMediaFinder->SelectedImageName = $objFiles->getName();
        }

        $this->objMediaFinder->addAction(new Q\Plugin\Event\ImageSave(), new Q\Action\Ajax('imageSave_Push'));
        $this->objMediaFinder->addAction(new Q\Plugin\Event\ImageDelete(), new Q\Action\Ajax('imageDelete_Push'));

        $this->btnSubmit = new Button($this);
        $this->btnSubmit->Text = "Submit";
        $this->btnSubmit->PrimaryButton = true;
        $this->btnSubmit->AddAction(new Click(), new Ajax('submit_Click'));

        $this->pnlResult = new Panel($this);
        $this->pnlResult->HtmlEntities = true;

        $this->pnlData = new Panel($this);
        $this->pnlIntroData = new Panel($this);
        $this->pnlIntroData->HtmlEntities = true;
    }

    /**
     * Handles the saving of a selected image by updating the relevant database entry
     * with the associated image ID. Updates the UI components to reflect the selected image's information.
     *
     * @param ActionParams $params Parameters from the action event, used for processing the image save request.
     *
     * @return void
     * @throws Caller
     * @throws InvalidCast
     */
    protected function imageSave_Push(ActionParams $params): void
    {
        // The "finder.php" file always registers the id of the selected image
        // in the "files" table. So there is no need to do anything here.

        // Here register the id of the selected image, for example, in the "example" column.

        $saveId = $this->objMediaFinder->Item;

        $objExample = Example::load(1);
        $objExample->setPictureId($saveId);
        $objExample->save();

        $info = $objExample->getPictureId();

        if ($info) {
            $this->pnlData->Text = $info;
        }

        $image = Files::loadById($info);

        if ($image) {
            $this->pnlIntroData->Text = '<img src="' . APP_UPLOADS_TEMP_URL . "/_files/medium" . $image->getPath() . '" class="image img-responsive">';
        }
    }

    /**
     * Handles the deletion of an image and updates the relevant database records.
     * Ensures the associated file is marked as free and updates the corresponding
     * entry in the "example" table to indicate no image is associated. Additionally,
     * updates UI components to reflect the changes.
     *
     * @param ActionParams $params Provides parameters related to the user-triggered action.
     *
     * @return void
     * @throws Caller
     * @throws InvalidCast
     */
    protected function imageDelete_Push(ActionParams $params): void
    {
        // Here it is necessary to inform the "files" table that this selected image is now free.
        // If this is not done, the FileHandler will not report the correct information about
        // whether the files are free or not. This is so that files occupied by others cannot be
        // accidentally deleted in the FileHandler.

        $objExample = Example::load(1);

        $objFiles = Files::loadById($objExample->getPictureId());

        if ($objFiles->getLockedFile() !== 0) {
            $objFiles->setLockedFile($objFiles->getLockedFile() - 1);
            $objFiles->save();
        }

        // Here, in the "example" table, report that this image no longer exists

        $objExample->setPictureId(null);
        $objExample->save();

        $this->pnlData->Text = "NULL";
        $this->pnlIntroData->Text = "NULL";
    }

    /**
     * Handles the click event for the submit action. This method updates the content
     * of an example object, saves the changes, and updates various panels with information
     * or placeholders based on available data, including an associated image.
     *
     * @param ActionParams $params Contains the parameters passed during the click event.
     *
     * @return void
     * @throws Caller
     * @throws InvalidCast
     */
    protected function submit_Click(ActionParams $params): void
    {
        $objExample = Example::loadById(1);
        $objExample->setContent($this->txtEditor->Text);
        $objExample->save();

        $this->pnlResult->Text = $objExample->getContent();

        if ($objExample->getPictureId()) {
            $this->pnlData->Text = $objExample->getPictureId();
        } else {
            $this->pnlData->Text = "NULL";
        }

        $image = Files::loadById($objExample->getPictureId());

        if ($image) {
            $this->pnlIntroData->Text = '<img src="' . APP_UPLOADS_TEMP_URL . "/_files/medium" . $image->getPath() . '" class="image img-responsive">';
        } else {
            $this->pnlIntroData->Text = "NULL";
        }
    }

    // Special attention must be given here when you wish to delete the selected example. It is necessary
    // to inform FileHandler to first decrease the count of locked files ("locked_file").
    // Finally, delete this example.

    // The approximate example below:

    /*protected function delete_Click(ActionParams $params)
    {
        $objExample = Example::loadById(1);

        $lockedFile = Files::loadById($objExample->getPictureId());
        $lockedFile->setLockedFile($lockedFile->getLockedFile() - 1);
        $lockedFile->save();

        $objExample->delete();
    }*/

}
SampleForm4::run('SampleForm4');
