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
            <form class="uk-form-horizontal uk-margin-large">

                <h3>General Settings</h3>
                <div class="form-group">
                    <div>Disable Receiving Order</div>
                    <div>
                        <label for="cpos_order_disable"><input type="checkbox" name="cpos_order_disable"
                                id="cpos_order_disable">
                            Disable
                        </label>
                        <div class="form-note">
                            If you disable it, NO ONE can place order except Admin
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div></div>
                    <div>
                        <label for="cpos_order_disable_reason">Describe Reason</label>
                        <textarea name="cpos_order_disable_reason" id="cpos_order_disable_reason" rows="5" class="input"
                            placeholder="Leave a message why are you not receiving orders"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label for="cpos_delivery_time">Default Delivery Time (Minutes)</label>
                    <div><input type="text" name="cpos_delivery_time" id="cpos_delivery_time" class="input"
                            placeholder="45"></div>
                </div>

                <h3>App Settings</h3>


                <div class="form-group">
                    <label for="cpos_app_primary_color">App Primary Color</label>
                    <div><input type="text" id="cpos_app_primary_color" class="input" placeholder="App Primary Color">
                    </div>
                </div>

                <!-- <div class="form-group">
                    <label for="cpos_app_primary_color">Help/Support Page Link</label>
                    <div><input type="text" id="cpos_app_primary_color" class="input" placeholder="Support Page Link">
                    </div>
                </div>

                <div class="form-group">
                    <label for="cpos_app_primary_color">Privacy Policy Page Link</label>
                    <div><input type="text" id="cpos_app_primary_color" class="input" placeholder="Privacy Page Link">
                    </div>
                </div>

                <div class="form-group">
                    <label for="cpos_app_primary_color">Terms & Condition Page Link</label>
                    <div><input type="text" id="cpos_app_primary_color" class="input"
                            placeholder="Terms & Condition Page Link">
                    </div>
                </div>

                <div class="form-group">
                    <label for="cpos_app_primary_color">FAQ Page Link</label>
                    <div><input type="text" id="cpos_app_primary_color" class="input" placeholder="FAQ Page Link">
                    </div>
                </div> -->

                <div class="form-group">
                    <label for="cpos_app_primary_color">Media Placeholder Image URL</label>
                    <div><input type="text" id="cpos_app_primary_color" class="input"
                            placeholder="Placeholder Image URL">
                    </div>
                </div>



                <div class="form-group">
                    <div></div>
                    <div>
                        <button class="button">Save Changes</button>
                    </div>
                </div>


            </form>
        </li>


        <!-- app settings  -->
        <!-- <li>App Settings</li> -->
        <li>Offer Push Notification</li>
        <li>Update and Validation License</li>
        <li>About Combosoft</li>
    </ul>

</div>
