<?php

$week_names = array(
    __( 'Sun', 'tp-hotel-booking' ),
    __( 'Mon', 'tp-hotel-booking' ),
    __( 'Tue', 'tp-hotel-booking' ),
    __( 'Wed', 'tp-hotel-booking' ),
    __( 'Thu', 'tp-hotel-booking' ),
    __( 'Fri', 'tp-hotel-booking' ),
    __( 'Sat', 'tp-hotel-booking' )
);

$room_id = intval( hb_get_request( 'hb-room' ) );

$capacitiyID = get_post_meta( $room_id, '_hb_room_capacity', true );

$pricing_plans = get_posts(
    array(
        'post_type'         => 'hb_pricing_plan',
        'post_status'       => 'publish',
        'posts_per_page'    => 9999,
        'meta_query' => array(
            array(
                'key'     => '_hb_pricing_plan_room',
                'value'   => $room_id
            )
        )
    )
);

if( $pricing_plans ) {
    $regular_plan = array_pop($pricing_plans);
}else{
    $regular_plan = null;
}
$count_plants = count( $pricing_plans );
?>

<div class="wrap" id="tp_hotel_booking_pricing">
    <h2><?php _e( 'Pricing Plans', 'tp-hotel-booking' ); ?></h2>
    <form method="post" name="pricing-table-form">
        <p><strong><?php _e( 'Select name of room', 'tp-hotel-booking' ); ?></strong>&nbsp;&nbsp;<?php echo hb_dropdown_rooms( array('selected' => $room_id) ); //$room_type_select; ?></p>
        <?php if( $room_id ){?>
        <div class="hb-pricing-table regular-price clearfix">
            <h3 class="hb-pricing-table-title">
                <span><?php _e( 'Regular price', 'tp-hotel-booking' ); ?></span>
                <input type="text" class="datepicker" name="date-start[<?php echo $regular_plan ? $regular_plan->ID : '__INDEX__'; ?>]" size="10" readonly="readonly" />
                <input type="text" class="datepicker" name="date-end[<?php echo $regular_plan ? $regular_plan->ID : '__INDEX__'; ?>]" size="10" readonly="readonly" />
            </h3>
            <div class="hb-pricing-controls">
                <a href="" class="dashicons dashicons-edit" data-action="edit" title="<?php _e( 'Edit', 'tp-hotel-booking' ); ?>"></a>
                <a href="" class="dashicons dashicons-admin-page" data-action="clone" title="<?php _e( 'Clone', 'tp-hotel-booking' ); ?>"></a>
                <a href="" class="dashicons dashicons-trash" data-action="remove" title="<?php _e( 'Remove', 'tp-hotel-booking' ); ?>"></a>
            </div>
            <?php
                if( $regular_plan ) {
                    $regular_prices = get_post_meta($regular_plan->ID, '_hb_pricing_plan_prices', true);
                }else{
                    $regular_prices = array();
                }
            ?>
            <div class="hb-pricing-list">
                <table>
                    <thead>
                        <tr>
                            <?php for( $i = 0; $i < 7; $i++ ){?>
                            <th><?php echo $week_names[ $i ]; ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if( $capacitiyID ):?>
                        <tr>
                            <!-- <th><?php //echo $capacity->name; ?></th> -->
                            <?php for( $i = 0; $i < 7; $i++ ){?>
                                <td>
                                    <?php
                                    $price = ! empty( $regular_prices[ $capacitiyID ][ $i ] ) ? $regular_prices[ $capacitiyID ][ $i ] : '';
                                    ?>
                                    <input class="hb-pricing-price" type="text" name="price[<?php echo $regular_plan ? $regular_plan->ID : '__INDEX__'; ?>][<?php echo $capacitiyID; ?>][<?php echo $i; ?>]" value="<?php echo $price; ?>" size="10" readonly="readonly" />
                                </td>
                            <?php }?>
                        </tr>
                        <?php else:?>
                        <tr><td colspan="7"><?php _e( 'No capacities found', 'tp-hotel-booking' ); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
            <div class="clearfix"></div>
        <h3 class="clearfix">
            <?php _e( 'Other plan', 'tp-hotel-booking' ); ?>
            <span class="count"><?php printf( _n( '(%d plan)', '(%d plans)', $count_plants, 'tp-hotel-booking' ), $count_plants ); ?></span>
        </h3>
        <p>
            <button class="button hb-add-new-plan" type="button"><?php _e( 'Add Plan', 'tp-hotel-booking' ); ?></button>
            <button class="button button-primary"><?php _e( 'Update', 'tp-hotel-booking'); ?></button>
        </p>
        <div id="hb-pricing-plan-list">
        <?php if( $pricing_plans ): foreach( $pricing_plans as $plan ){?>
            <?php
                $plan_prices = get_post_meta($plan->ID, '_hb_pricing_plan_prices', true);
                $start_date = get_post_meta($plan->ID, '_hb_pricing_plan_start', true);
                $end_date = get_post_meta($plan->ID, '_hb_pricing_plan_end', true);
            ?>
            <div class="hb-pricing-table">
                <h3 class="hb-pricing-table-title">
                    <span><?php _e( 'Date Range', 'tp-hotel-booking' ); ?></span>
                    <input type="text" class="datepicker" name="date-start[<?php echo $plan->ID; ?>]" size="10" value="<?php echo $start_date; ?>" readonly="readonly" />
                    <input type="text" class="datepicker" name="date-end[<?php echo $plan->ID; ?>]" size="10" value="<?php echo $end_date; ?>" readonly="readonly" />
                </h3>
                <div class="hb-pricing-controls">
                    <a href="" class="dashicons dashicons-edit" data-action="edit" title="<?php _e( 'Edit', 'tp-hotel-booking' ); ?>"></a>
                    <a href="" class="dashicons dashicons-admin-page" data-action="clone" title="<?php _e( 'Clone', 'tp-hotel-booking' ); ?>"></a>
                    <a href="" class="dashicons dashicons-trash" data-action="remove" title="<?php _e( 'Remove', 'tp-hotel-booking' ); ?>"></a>
                </div>

                <div class="hb-pricing-list">
                    <table>
                        <thead>
                        <tr>
                            <!-- <th><?php //_e( 'Capacity', 'tp-hotel-booking' ); ?></th> -->
                            <?php for( $i = 0; $i < 7; $i++ ){?>
                                <th><?php echo $week_names[ $i ]; ?></th>
                            <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if( $capacitiyID ):?>
                        <?php $capacity = get_term( $capacitiyID, 'hb_room_capacity' ) ?>
                            <tr>
                                <!-- <th><?php //echo $capacity->name; ?></th> -->
                                <?php for( $i = 0; $i < 7; $i++ ){?>
                                    <td>
                                        <?php $price = ! empty( $plan_prices[ $capacitiyID ] ) ? ( array_key_exists( $i, $plan_prices[ $capacitiyID ] ) ? $plan_prices[ $capacitiyID ][ $i ] : '' ) : ''; ?>
                                        <input class="hb-pricing-price" type="text" name="price[<?php echo $plan->ID; ?>][<?php echo $capacitiyID; ?>][<?php echo $i; ?>]" value="<?php echo $price; ?>" size="10" readonly="readonly" />
                                    </td>
                                <?php }?>
                            </tr>
                        <?php else:?>
                            <tr><td colspan="7"><?php _e( 'No capacities found', 'tp-hotel-booking' ); ?></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } else:?>
            <p id="hb-no-plan-message"> <?php _e( 'No addition plans', 'tp-hotel-booking' ); ?></p>
        <?php endif; ?>
        </div>
        <?php wp_nonce_field( 'hb-update-pricing-plan', 'hb-update-pricing-plan-field' ); ?>
        <!-- <p>
            <button type="button" class="button hb-add-new-plan"><?php //_e( 'Add Plan', 'tp-hotel-booking' ); ?></button>
            <button class="button button-primary"><?php //_e( 'Update', 'tp-hotel-booking' ); ?></button>
        </p> -->
        <?php }?>
    </form>
</div>
<script type="text/html" id="tmpl-hb-pricing-table">
    <div class="hb-pricing-table">
        <h3 class="hb-pricing-table-title">
            <span><?php _e( 'Date Range', 'tp-hotel-booking' ); ?></span>
            <input type="text" class="datepicker" name="date-start[__INDEX__]" size="10" readonly="readonly" />
            <input type="text" class="datepicker" name="date-end[__INDEX__]" size="10" readonly="readonly" />
        </h3>
        <div class="hb-pricing-controls">
            <a href="" class="dashicons dashicons-edit" data-action="edit" title="<?php _e( 'Clone', 'tp-hotel-booking' ); ?>"></a>

            <# if( typeof data.clone !== 'undefined' && data.clone === true ) { #>
                <a href="" class="dashicons dashicons-admin-page" data-action="clone" title="<?php _e( 'Clone', 'tp-hotel-booking' ); ?>"></a>
            <# } #>

            <a href="" class="dashicons dashicons-trash" data-action="remove" title="<?php _e( 'Remove', 'tp-hotel-booking' ); ?>"></a>
        </div>

        <div class="hb-pricing-list">
            <table>
                <thead>
                <tr>
                    <?php for( $i = 0; $i < 7; $i++ ){?>
                        <th><?php echo $week_names[ $i ]; ?></th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php if( $capacitiyID ):?>
                <?php $capacity = get_term( $capacitiyID, 'hb_room_capacity' ) ?>
                    <tr>
                        <?php for( $i = 0; $i < 7; $i++ ){?>
                            <td>
                                <input class="hb-pricing-price" type="text" name="price[__INDEX__][<?php echo $capacitiyID; ?>][<?php echo $i; ?>]" value="" size="10" readonly="readonly" />
                            </td>
                        <?php }?>
                    </tr>
                <?php else:?>
                    <tr><td colspan="7"><?php _e( 'No capacities found', 'tp-hotel-booking' ); ?></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</script>