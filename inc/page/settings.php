<div class="wrap combopos_wrap">

    <h2 class="title">ComboPOS <small>v1.0</small></h2>
    <ul class="tab-nav justify-content-center">
        <a class="active" href="#"><i class="fa fa-tools"></i> Settings</a>
        <a href="#" class="mr-2 ml-2"><i class="fa fa-calendar-check"></i> Update</a>
        <a href="#"><i class="fa fa-laptop-code"></i> About</a>
    </ul>

    <ul class="tab-content">
        <!-- general settings  -->
        <li style="display: block">
            <form class="uk-form-horizontal uk-margin-large" id="save_cs_settings">

                <h5>General Settings</h5>
                <div class="form-row form-group">
                    <div class="col">Disable Receiving Order</div>
                    <div class="col">
                        <label for="cpos_order_disable">
                            <input type="checkbox" name="cpos_order_disable" id="cpos_order_disable"
                                <?=get_option('cpos_order_disable') == true ? 'checked' : '';?>>
                            Disable
                        </label>
                        <div class="form-note">
                            If you disable it, NO ONE can place order except Admin
                        </div>
                    </div>
                </div>

                <div class="form-group form-group"
                    style="display: <?=get_option('cpos_order_disable') == true ? '' : 'none';?>">
                    <div></div>
                    <div>
                        <label for="cpos_order_disable_reason">Describe Reason</label>
                        <textarea name="cpos_order_disable_reason" id="cpos_order_disable_reason" rows="2"
                            class="form-control"
                            placeholder="Leave a message why are you not receiving orders"><?=get_option('cpos_order_disable_reason');?></textarea>
                    </div>
                </div>
                <div class="form-row form-group">
                    <label class="col" for="cpos_delivery_time">Default Delivery Time (In Minutes)</label>
                    <div class="col"><input type="text" name="cpos_delivery_time" id="cpos_delivery_time"
                            class="form-control" placeholder="45" value="<?=get_option('cpos_delivery_time');?>"></div>
                </div>

                <h5>App Settings</h5>

                <div class="form-row form-group">
                    <label class="col" for="cpos_app_primary_color">App Primary Color</label>
                    <div class="col"><input type="text" id="cpos_app_primary_color" name="cpos_app_primary_color"
                            data-jscolor class="form-control" placeholder="App Primary Color"
                            value="<?=str_replace('', '', get_option('cpos_app_primary_color'));?>">
                    </div>
                </div>

                <div class="form-row form-group">
                    <label class="col" for="cpos_app_secondary_color">App Secondary Color</label>
                    <div class="col"><input type="text" id="cpos_app_secondary_color" name="cpos_app_secondary_color"
                            data-jscolor class="form-control" placeholder="App Secondary Color"
                            value="<?=str_replace('', '', get_option('cpos_app_secondary_color'));?>">
                    </div>
                </div>

                <div class="form-row form-group">
                    <label class="col" for="cpos_app_logo_url">App Logo URL</label>
                    <div class="col"><input type="text" id="cpos_app_logo_url" class="form-control"
                            placeholder="App Logo URL" name="cpos_app_logo_url"
                            value="<?=get_option('cpos_app_logo_url');?>">
                    </div>
                </div>

                <div class="form-row form-group">
                    <label class="col" for="cpos_app_placeholder_url">Media Placeholder Image URL</label>
                    <div class="col"><input type="text" id="cpos_app_placeholder_url" class="form-control"
                            placeholder="Placeholder Image URL" name="cpos_app_placeholder_url"
                            value="<?=get_option('cpos_app_placeholder_url');?>">
                    </div>
                </div>

                <div class="form-row form-group">
                    <div class="col">
                        <a href="#" id="reset_cs_settings">Reset Settings</a>
                    </div>
                    <div class="col" class="col">
                        <button class="btn btn-lg button">Save Changes</button>
                    </div>
                </div>
            </form>
        </li>
        <!-- app settings  -->

        <!-- license validation  -->
        <li>
            <h5>License</h5>
            <form action="" id="check_cs_license">
                <div class="form-group form-row">
                    <label class="col" for="cs_license">POS License</label>
                    <div class="col">
                        <input type="text" class="form-control" placeholder="Enter License" value="" id="cs_license"
                            name="cs_license">
                        <div class="form-group mt-2">
                            <button class="btn btn-lg button">Apply License</button>
                        </div>
                    </div>
                </div>
            </form>

            <h5>Update</h5>
            <table class="table table-borderless table-stripped">
                <tbody>
                    <tr>
                        <td>Current Version</td>
                        <td>1.0</td>
                    </tr>
                    <tr>
                        <td>Latest Version</td>
                        <td>1.0</td>
                    </tr>
                </tbody>
            </table>
        </li>
        <!-- license validation  -->

        <!-- about combosoft  -->
        <li>
            <h5>ComboPOS</h5>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Culpa impedit optio suscipit ut eaque tempora
                facilis at consequuntur repellat itaque! Sint rem, aspernatur ut quibusdam molestias quidem velit
                sapiente quia.</p>
        </li>
        <!-- about combosoft  -->
    </ul>

</div>
