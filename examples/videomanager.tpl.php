<?php require(QCUBED_CONFIG_DIR . '/header.inc.php'); ?>

<?php
// https://ckeditor.com/docs/ckeditor4/latest/guide/dev_file_browse_upload.html
?>
<style>
    code {
        display: inline-block;
        width: 11%;
    }
</style>
<script>
    ckConfig = {
        skin: 'moono',
    };
</script>
<?php $this->RenderBegin(); ?>

<div class="instructions" style="margin-bottom: 40px;">
    <h3>VideoEmbed for QCubed-4: Implementing the VideoEmbed Plugin</h3>

    <p>
        For information on how to implement the CKEditor 4 HTML editor, please see the following examples:
        <a href="../../../qcubed-4/plugin-ckeditor/examples/ckeditor.php">ckeditor.php</a>,
        <a href="../../../qcubed-4/plugin-ckeditor/examples/ckeditor2.php">ckeditor2.php</a>, and
        <a href="../../qcubed-filemanager/examples/ckeditor3.php">ckeditor3.php</a>.
        For more convenient usage, you can also refer to
        <a href="../../qcubed-filemanager/examples/mediafinder.php">mediafinder.php</a>.
    </p>

    <p>
        The VideoEmbed plugin is integrated here and is visible on the right side of the editor.
        This plugin makes it much easier to add rich content beyond an introductory video.
        It includes an embed sanitization function, <code>cleanEmbedCode()</code>.
        The plugin is reusable and can be applied to articles, news items, or any other content type.
    </p>

    <p>
        The current example is intentionally simplified and does not include all possible
        validations or control logic. Implementing such checks is the responsibility of the developer,
        depending on the specific requirements of the project.
    </p>

    <p>
        To use the VideoEmbed plugin, you need to add at least two columns to your database table:
        <code>media_id</code> and <code>video_embed</code>.
    </p>

    <p>
        When building application logic, you can combine the editor’s right-side controls with
        additional selection options such as Image, Video, and others. The possible use cases
        are flexible and extensible.
    </p>
</div>


<div class="container" style="margin-top: 20px;">
    <div class="row">
        <div class="col-md-9"><?= _r($this->txtEditor); ?></div>
        <div class="col-md-3"><?= _r($this->objVideoEmbed); ?></div>
    </div>
    <div class="row">
        <div class="col-md-12" style="margin-top: 15px;"><?= _r($this->btnSubmit); ?></div>
    </div>
    <div class="row" style="margin-top: 20px;">
        <div class="col-md-4">
            <h5><b>The HTML you typed:</b></h5>
            <?= _r($this->pnlResult); ?>
        </div>
        <div class="col-md-4">
            <h5><b>DATA to store in a separate column of the database table:</b></h5>
            <?= _r($this->pnlData); ?>
        </div>
        <div class="col-md-4">
            <h5><b>The introduction videoembed is placed, for example, on top of the content on the frontend:</b></h5>
            <?= _r($this->pnlIntroData); ?>
        </div>
    </div>
</div>

<?php $this->RenderEnd(); ?>
<?php require(QCUBED_CONFIG_DIR . '/footer.inc.php'); ?>
