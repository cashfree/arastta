<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" onclick="save('save')" form="form-cashfree" data-toggle="tooltip" title="" class="btn btn-success" data-original-title="Save"><i class="fa fa-check"></i></button>
                <button type="submit" form="form-cashfree" data-toggle="tooltip" title="" class="btn btn-default" data-original-title="Save & Close"><i class="fa fa-save text-success"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-times-circle text-danger"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if (isset($error['error_warning'])) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error['error_warning']; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-cashfree" class="form-horizontal">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
                        <li><a href="#tab-status" data-toggle="tab"><?php echo $tab_order_status; ?></a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab-general">
                            <div class="form-group required">
                                <label class="col-sm-2 control-label" for="entry-app-id"><span data-toggle="tooltip" title="<?php echo $help_app_id; ?>"><?php echo $entry_app_id; ?></span></label>
                                <div class="col-sm-10">
                                    <input type="text" name="cashfree_app_id" value="<?php echo $cashfree_app_id; ?>" placeholder="<?php echo $entry_app_id; ?>" id="entry-email" class="form-control"/>
                                    <?php if ($error_app_id) { ?>
                                    <div class="text-danger"><?php echo $error_app_id; ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group required">
                                <label class="col-sm-2 control-label" for="entry-secret-key"><span data-toggle="tooltip" title="<?php echo $help_secret_key; ?>"><?php echo $entry_secret_key; ?></span></label>
                                <div class="col-sm-10">
                                    <input type="text" name="cashfree_secret_key" value="<?php echo $cashfree_secret_key; ?>" placeholder="<?php echo $entry_secret_key; ?>" id="entry-secret-key" class="form-control"/>
                                    <?php if ($error_secret_key) { ?>
                                    <div class="text-danger"><?php echo $error_secret_key; ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-live-demo"><span data-toggle="tooltip" title="<?php echo $help_test; ?>"><?php echo $entry_test; ?></span></label>
                                <div class="col-sm-10">
                                    <select name="cashfree_test" id="input-live-demo" class="form-control">
                                        <?php if ($cashfree_test) { ?>
                                        <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                                        <option value="0"><?php echo $text_no; ?></option>
                                        <?php } else { ?>
                                        <option value="1"><?php echo $text_yes; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_no; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-debug"><span data-toggle="tooltip" title="<?php echo $help_debug; ?>"><?php echo $entry_debug; ?></span></label>
                                <div class="col-sm-10">
                                    <select name="cashfree_debug" id="input-debug" class="form-control">
                                        <?php if ($cashfree_debug) { ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                        <?php } else { ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
                                <div class="col-sm-10">
                                    <input type="text" name="cashfree_sort_order" value="<?php echo $cashfree_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                                <div class="col-sm-10">
                                    <select name="cashfree_status" id="input-status" class="form-control">
                                        <?php if ($cashfree_status) { ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                        <?php } else { ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-status">
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo $entry_expired_status; ?></label>
                                <div class="col-sm-10">
                                    <select name="cashfree_expired_status_id" class="form-control">
                                        <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $cashfree_expired_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo $entry_failed_status; ?></label>
                                <div class="col-sm-10">
                                    <select name="cashfree_failed_status_id" class="form-control">
                                        <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $cashfree_failed_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo $entry_pending_status; ?></label>
                                <div class="col-sm-10">
                                    <select name="cashfree_pending_status_id" class="form-control">
                                        <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $cashfree_pending_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo $entry_processed_status; ?></label>
                                <div class="col-sm-10">
                                    <select name="cashfree_processed_status_id" class="form-control">
                                        <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $cashfree_processed_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript"><!--
function save(type){
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'button';
    input.value = type;
    form = $("form[id^='form-']").append(input);
    form.submit();
}
//--></script>
<?php echo $footer; ?>
