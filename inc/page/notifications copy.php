<div class="wrap combopos_wrap">

    <h2 class="title">Broadcast Notification</h2>
    <ul class="tab-nav justify-content-center">
        <a class="active mr-2" href="#"><i class="fa fa-tools"></i> Broadcast</a>
        <a href="#"><i class="fa fa-bullhorn"></i> Notifications</a>
    </ul>

    <ul class="tab-content">


        <!-- broadcast and offers  -->
        <li style="display: block">
            <h5>Broadcast Push Notification</h5>
            <form action="#" class="mt-3" id="push_cs_notification">
                <div class="form-group form-row">

                    <div class="col-4">
                        <div class="form-group">
                            <label for="" class="small text-muted">Broadcast Type</label>
                            <select name="" id="" class="custom-select">
                                <option>It's an Offer</option>
                                <option>Its a Message</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form-group">
                            <label for="" class="small text-muted">Target Customers</label>
                            <select name="" id="" class="custom-select">
                                <option>All</option>
                                <option>Grouped</option>
                                <option>Specific</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form-group">
                            <label for="" class="small text-muted">User</label>
                            <?php 
                            $all_customers = get_users( [ 'role__in' => [ 'customer' ] ] );
                            if($all_customers): ?>
                            <select name="notification_userlist" id="" class="custom-select" cs_multiple
                                multiple="multiple">
                                <?php foreach ( $all_customers as $customer ): ?>
                                <option value="<?=$customer->ID; ?>">
                                    <?=$customer->display_name . ' [' . $customer->ID . ']'; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php else: ?>
                            no customer found
                            <?php  endif;  ?>
                        </div>
                    </div>

                </div>

                <div class="form-group">
                    <label for="" class="small text-muted">Write what to push</label>
                    <textarea name="" id="" cols="30" rows="5" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <button class="btn button">Broadcast</button>
                </div>
            </form>
        </li>
        <!-- broadcast and offers  -->
        <!-- general settings  -->
        <li>

        </li>


        <!-- app settings  -->
    </ul>

</div>
</div>
