<?php
// +------------------------------------------------------------------------+
// | @author Deen Doughouz (DoughouzForest)
// | @author_url 1: http://www.playtubescript.com
// | @author_url 2: http://codecanyon.net/user/doughouzforest
// | @author_email: wowondersocial@gmail.com   
// +------------------------------------------------------------------------+
// | PlayTube - The Ultimate Video Sharing Platform
// | Copyright (c) 2017 PlayTube. All rights reserved.
// +------------------------------------------------------------------------+


if (!IS_LOGGED) {

    $response_data    = array(
        'api_status'  => '400',
        'api_version' => $api_version,
        'errors' => array(
            'error_id' => '1',
            'error_text' => 'Not logged in'
        )
    );
}
else if (!empty($_POST['user_id'])) {
    $id    = PT_Secure($_POST['user_id']);
    $db->where('id', $id);
    $user = $db->getOne(T_USERS);
    $user_data      = ToArray($user);
    unset($user_data['password']);

    date_default_timezone_set('Africa/Kigali');
    $date = date('Y-m-d H:i:s');

    if (!empty($user)) {
        $db->where('user_id', $id);
        $db->where('status', "SUCCESS");
        $db->where('end_date', $date, ">=");
        $payment = $db->getOne(T_APPPAY);

        if($payment){

            $diff = abs(strtotime($payment->end_date) - strtotime($date));
            if($diff > 0){
                $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

            }else{
                $days = 0;
            }
            $response_data     = array(
                'api_status'   => '200',
                'api_version'  => $api_version,
                'message' => 'last valide payment',
                'data'      => $payment,
                'days' => $days
            );
        }else{
            $response_data    = array(
                'api_status'  => '400',
                'api_version' => $api_version,
                'errors' => array(
                    'error_id' => '1',
                    'error_text' => 'you have to pay'
                )
            ); 
        }
    }

}else{
    $response_data    = array(
        'api_status'  => '400',
        'api_version' => $api_version,
        'errors' => array(
            'error_id' => '1',
            'error_text' => 'User id is requered'
        )
    );
}