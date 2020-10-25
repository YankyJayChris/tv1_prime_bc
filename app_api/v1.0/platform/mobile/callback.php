<?php

$postdata = file_get_contents("php://input");

if($postdata){
    $apikey = $postdata;

    $insert_data      = array(
        'mtn_data'    => $apikey,

    );
    $user_id          = $db->insert(T_APIKEY, $insert_data);
    if (!empty($insert)) {
        $response_data     = array(
            'api_status'   => '200',
            'api_version'  => $api_version,
            'success_type' => 'callback',
            'message'      => 'Successfully joined, paid..'
        );
    }
}else{

    $insert_data      = array(
        'mm_apikey'    => "thiserror_notworking",
        'user_id'    => "fake_user",

    );
    $user_id          = $db->insert(T_APIKEY, $insert_data);
    $response_data = array(
        'api_status' => '400',
        'api_version' => $api_version,
        'errors' => array(
            'error_id' => '2',
            'error_text' => 'Bad Request, Invalid or missing parameter (apiKey)'
        ),
        'post_data' => $postdata
    );
}

?>