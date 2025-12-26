<?php $strPageTitle = 'Examples of file management' ; ?>

<?php require('header.inc.php'); ?>
<?php $this->RenderBegin(); ?>
    <div class="dialog-content-wrapper">
        <div class="page-content">
            <div class="content-body">
                <div class="files-heading">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="btn-group" role="group">
                                <?= _r($this->btnUploadStart); ?>
                                <?= _r($this->btnAddFolder); ?>
                            </div>
                            <div class="btn-group" role="group">
                                <?= _r($this->btnRefresh); ?>
                            </div>
                            <div class="btn-group" role="group">
                                <?= _r($this->btnRename); ?>
                                <?= _r($this->btnCrop); ?>
                                <?= _r($this->btnCopy); ?>
                                <?= _r($this->btnDelete); ?>
                                <?= _r($this->btnMove); ?>
                            </div>
                            <div class="btn-group" role="group">
                                <?= _r($this->btnImageListView); ?>
                                <?= _r($this->btnListView); ?>
                                <?= _r($this->btnBoxView); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="search">
                                <?= _r($this->txtFilter); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="fileupload-buttonbar hidden">
                    <div class="row">
                        <div class="col-md-12">
                            <?= _r($this->btnAddFiles); ?>
                            <?= _r($this->btnAllStart); ?>
                            <?= _r($this->btnAllCancel); ?>
                            <?= _r($this->btnBack); ?>
                        </div>
                    </div>
                </div>
                <div class="form-body">
                    <div class="row"> <!-- BREADCRUMBS-->
                        <div class="col-md-9">
                            <div class="head">
                                <div class="break-word">
                                    <?= _r($this->lblSearch); ?>
                                    <?= _r($this->objHomeLink); ?>
                                </div>&nbsp;&nbsp;&nbsp;
                                <div class="breadcrumbs"></div>
                            </div>
                        </div>
                        <div class="col-md-3"></div>
                    </div>
                    <div class="row equal">
                        <div class="col-md-9">
                            <div class="upload-wrapper hidden">
                                <div id="alert-wrapper"></div>
                                <div class="alert-multi-wrapper"></div>
                                <?= _r($this->objUpload); ?>
                                <div class="fileupload-donebar hidden">
                                    <?= _r($this->btnDone); ?>
                                </div>
                            </div>
                            <div class="dialog-wrapper"> <!-- This element is required for the scrollpad control -->
                                <div class="control-scrollpad">
                                    <?= _r($this->objManager); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="file-info active-buttons">
                                <?= _r($this->objInfo); ?>
                                <div class="file-info-buttons">
                                    <div class="form-group" style="padding-top: 15px;">
                                        <?= _r($this->btnInsert); ?>
                                        <?= _r($this->btnCancel); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $this->RenderEnd(); ?>

<?php require('footer.inc.php');