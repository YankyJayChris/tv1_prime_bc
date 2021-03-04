<?php

add_action('rest_api_init', 'wp_api_add_posts_endpoints');
function wp_api_add_posts_endpoints()
{
    register_rest_route('momo/v1', 'momo_callback', array(
        'methods' => 'POST',
        'callback' => 'momo_callback',
    ));
}

function momo_callback(WP_REST_Request $request)
{
    $myUUID = $request['financialTransactionId'];
    $status = $request['status'];
    $resdesc = "Payment failed";
    if ($status == "SUCCESSFUL") {
        $resdesc = "Successful payment";
    }
    woomomo_update_transaction($myUUID, $status, $resdesc);
}