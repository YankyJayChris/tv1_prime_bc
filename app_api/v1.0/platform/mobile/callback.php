<?php

if($_POST["apiKey"]){
    $apikey = $_POST["apiKey"];
    $userid = ($_POST["userid"]) ?"":"";

    $insert_data      = array(
        'mm_apikey'    => $apikey,
        'user_id'    => $userid,

    );
    $user_id          = $db->insert(T_APIKEY, $insert_data);
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
        )
    );
}

?>