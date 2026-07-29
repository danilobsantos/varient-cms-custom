<div class="modal fade" tabindex="-1" id="modalBulkDocumentation">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?= trans('bulk_post_upload'); ?></h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>

            <div class="modal-body">
                <table style="width:100%" class="table table-bordered">
                    <tr>
                        <th><?= trans('field'); ?></th>
                        <th><?= trans('description'); ?></th>
                    </tr>
                    <tr>
                        <td style="width: 180px;">lang_id</td>
                        <td><?= trans("data_type"); ?>: Integer <br><strong><?= trans("required"); ?></strong> <br><span style="color: #777;"><?= trans("example"); ?>: 1</span></td>
                    </tr>
                    <tr>
                        <td style="width: 180px;">title</td>
                        <td><?= trans("data_type"); ?>: String <br><strong><?= trans("required"); ?></strong><br><span style="color: #777;"><?= trans("example"); ?>: Test title</span></td>
                    </tr>
                    <tr>
                        <td style="width: 180px;">slug</td>
                        <td><?= trans("data_type"); ?>: String <br><strong><?= trans("optional"); ?></strong> <small>(<?= trans("slug_exp"); ?>)</small> <br> <span style="color: #777;"><?= trans("example"); ?>: test-title</span></td>
                    </tr>
                    <tr>
                        <td style="width: 180px;">summary</td>
                        <td><?= trans("data_type"); ?>: String <br><strong><?= trans("optional"); ?></strong><br> <span style="color: #777;"><?= trans("example"); ?>: This is summary</span></td>
                    </tr>
                    <tr>
                        <td style="width: 180px;">content</td>
                        <td><?= trans("data_type"); ?>: String <br><strong><?= trans("optional"); ?></strong><br> <span style="color: #777;"><?= trans("example"); ?>: This is content</span></td>
                    </tr>
                    <tr>
                        <td style="width: 180px;">category_id</td>
                        <td><?= trans("data_type"); ?>: Integer <br><strong><?= trans("required"); ?></strong><br> <span style="color: #777;"><?= trans("example"); ?>: 2</span></td>
                    </tr>
                    <tr>
                        <td style="width: 180px;">post_format</td>
                        <td><?= trans("data_type"); ?>: String <br><strong><?= trans("required"); ?></strong><br><span style="color: #333;"><b>article</b> <?= trans("or"); ?> <b>video</b></span></td>
                    </tr>
                    <tr>
                        <td style="width: 180px;">video_embed_code</td>
                        <td><?= trans("data_type"); ?>: String<br><strong><?= trans("optional"); ?></strong> <br> <span style="color: #777;"><?= trans("example"); ?>: https://www.youtube.com/embed/V9ypxcc0TpI</span></td>
                    </tr>
                    <tr>
                        <td style="width: 180px;">image_url</td>
                        <td><?= trans("data_type"); ?>: String <br><strong><?= trans("optional"); ?></strong><br> <span style="color: #777;"><?= trans("example"); ?>: https://upload.wikimedia.org/wikipedia/commons/7/70/Labrador-sea-paamiut.jpg</span></td>
                    </tr>
                    <tr>
                        <td style="width: 180px;">image_description</td>
                        <td><?= trans("data_type"); ?>: String <br><strong><?= trans("optional"); ?></strong><br> <span style="color: #777;"><?= trans("example"); ?>: Labrador sea</span></td>
                    </tr>
                    <tr>
                        <td style="width: 180px;">tags</td>
                        <td><?= trans("data_type"); ?>: String <br><strong><?= trans("optional"); ?></strong><br> <span style="color: #777;"><?= trans("example"); ?>: test, post</span></td>
                    </tr>
                    <tr>
                        <td style="width: 180px;">meta_title</td>
                        <td><?= trans("data_type"); ?>: String <br><strong><?= trans("optional"); ?></strong> <br> <span style="color: #777;"><?= trans("example"); ?>: Test title</span></td>
                    </tr>
                    <tr>
                        <td style="width: 180px;">meta_description</td>
                        <td><?= trans("data_type"); ?>: String <br><strong><?= trans("optional"); ?></strong> <br> <span style="color: #777;"><?= trans("example"); ?>: Test meta description</span></td>
                    </tr>
                    <tr>
                        <td style="width: 180px;">meta_keywords</td>
                        <td><?= trans("data_type"); ?>: String <br><strong><?= trans("optional"); ?></strong> <br> <span style="color: #777;"><?= trans("example"); ?>: test, post</span></td>
                    </tr>
                </table>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("close"); ?></button>
            </div>
        </div>
    </div>
</div>