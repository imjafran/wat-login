<div class="wrap combopos_wrap">

    <h2 class="title">ComboPOS <small>v1.0</small></h2>
    <ul class="tab-nav">
        <a class="active" href="#"><i class="fa fa-tools"></i> Settings</a>
        <!-- <a href="#"><i class="fa fa-mobile-alt"></i> App</a> -->
        <a href="#"><i class="fa fa-bullhorn"></i> Broadcast</a>
        <a href="#"><i class="fa fa-calendar-check"></i> Update</a>
        <a href="#"><i class="fa fa-laptop-code"></i> About</a>
    </ul>

    <ul class="tab-content">
        <!-- general settings  -->
        <li style="display: block">
            <form class="uk-form-horizontal uk-margin-large" id="save_cs_settings">

                <h3>General Settings</h3>
                <div class="form-group">
                    <div>Disable Receiving Order</div>
                    <div>
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

                <div class="form-group" style="display: <?=get_option('cpos_order_disable') == true ? '' : 'none';?>">
                    <div></div>
                    <div>
                        <label for="cpos_order_disable_reason">Describe Reason</label>
                        <textarea name="cpos_order_disable_reason" id="cpos_order_disable_reason" rows="5" class="input"
                            placeholder="Leave a message why are you not receiving orders"><?=get_option('cpos_order_disable_reason');?></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label for="cpos_delivery_time">Default Delivery Time (In Minutes)</label>
                    <div><input type="text" name="cpos_delivery_time" id="cpos_delivery_time" class="input"
                            placeholder="45" value="<?=get_option('cpos_delivery_time');?>"></div>
                </div>

                <h3>App Settings</h3>

                <div class="form-group">
                    <label for="cpos_app_primary_color">App Primary Color</label>
                    <div><input type="text" id="cpos_app_primary_color" name="cpos_app_primary_color" data-jscolor
                            class="input" placeholder="App Primary Color"
                            value="<?=str_replace('', '', get_option('cpos_app_primary_color'));?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="cpos_app_logo_url">App Logo URL</label>
                    <div><input type="text" id="cpos_app_logo_url" class="input" placeholder="App Logo URL"
                            name="cpos_app_logo_url" value="<?=get_option('cpos_app_logo_url');?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="cpos_app_placeholder_url">Media Placeholder Image URL</label>
                    <div><input type="text" id="cpos_app_placeholder_url" class="input"
                            placeholder="Placeholder Image URL" name="cpos_app_placeholder_url"
                            value="<?=get_option('cpos_app_placeholder_url');?>">
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <a href="#" id="reset_cs_settings">Reset Settings</a>
                    </div>
                    <div>
                        <button class="button">Save Changes</button>
                    </div>
                </div>
            </form>
        </li>


        <!-- app settings  -->

        <!-- broadcast and offers  -->
        <li>Offer Push Notification</li>
        <!-- broadcast and offers  -->

        <!-- license validation  -->
        <li>Update and Validation License</li>
        <!-- license validation  -->

        <!-- about combosoft  -->
        <li>About Combosoft</li>
        <!-- about combosoft  -->
    </ul>

</div>
