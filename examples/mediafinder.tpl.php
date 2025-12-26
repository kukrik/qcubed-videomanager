<?php require(QCUBED_CONFIG_DIR . '/header.inc.php'); ?>

<?php
// https://ckeditor.com/docs/ckeditor4/latest/guide/dev_file_browse_upload.html
?>
<script>
    ckConfig = {
        skin: 'moono',
        //extraPlugins: ['filebrowser'],
        //filebrowserImageBrowseUrl: 'finder.php',
        //filebrowserBrowseUrl: 'finder.php',
        //filebrowserWindowWidth: '95%',
        //filebrowserWindowHeight: '95%',
        //language: 'en',
        //uiColor: '#9AB8F3'
    };
</script>
<?php $this->RenderBegin(); ?>

<div class="instructions" style="margin-bottom: 20px;">
    <h3>MediaFinder: Implementing the MediaFinder plugin and connecting with FileManager</h3>
    <p>
        How to implement the CKEditor 4 HTML editor, please see examples: <a href="ckeditor.php">ckeditor.php</a>,
        <a href="ckeditor2.php">ckeditor2.php</a>, and <a href="ckeditor3.php">ckeditor3.php</a>.
    </p>
    <p>
        Integrated here is MediaFinder, visible on the right side of the editor. This plugin allows for a much easier
        addition of content beyond the introductory image. The plugin is reusable and can be applied, for instance,
        to articles, news, or anything else.
    </p>
    <p>
        For a better illustration, a simplified table named "example" has been added to the database. This table contains
        a column called "picture_id," which stores the necessary information.
    </p>
    <p>
        The primary plugin, FileHandler, manages files and images site-wide, and MediaFinder utilizes the services
        of FileHandler. Here are some rules and recommendations that should be taken into serious consideration.
        Please refer to the comments and suggestions in mediafinder.php for more details.
    </p>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-9"><?= _r($this->txtEditor); ?></div>
        <div class="col-md-3"><?= _r($this->objMediaFinder); ?></div>
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
            <h5><b>The introduction image is placed, for example, on top of the content on the frontend:</b></h5>
            <?= _r($this->pnlIntroData); ?>
        </div>
    </div>
</div>

<?php $this->RenderEnd(); ?>
<?php require(QCUBED_CONFIG_DIR . '/footer.inc.php'); ?>
