<?php

if ($_POST['type'] == 'fetch') {
    $limit = (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 50) ? PT_Secure($_POST['limit']) : 20;
    $offset = (!empty($_POST['offset']) && is_numeric($_POST['offset']) && $_POST['offset'] > 0) ? PT_Secure($_POST['offset']) : 0;
    if (!empty($offset)) {
        $db->where('id', PT_Secure($offset), '<');
    }
    $user_ads        = $db->where('user_id', $pt->user->id)->orderBy('id', 'DESC')->get(T_USR_ADS, $limit);
    foreach ($user_ads as $key => $ad) {
        $user_ads[$key]->media = PT_GetMedia($ad->media);
        $user_ads[$key]->url = urldecode($ad->url);
    }
    $response_data     = array(
        'api_status'   => '200',
        'api_version'  => $api_version,
        'success_type' => 'fetch_ad',
        'data'    => $user_ads
    );
} elseif ($_POST['type'] == 'fetch_by_id') {
    if (!empty($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) {
        $user_ad        = $db->where('id', PT_Secure($_POST['id']))->where('user_id', $pt->user->id)->getOne(T_USR_ADS);
        if (!empty($user_ad)) {
            $user_ad->media = PT_GetMedia($user_ad->media);
            $user_ad->url = urldecode($user_ad->url);
            $response_data     = array(
                'api_status'   => '200',
                'api_version'  => $api_version,
                'success_type' => 'fetch_by_id',
                'data'    => $user_ad
            );
        } else {
            $response_data       = array(
                'api_status'     => '400',
                'api_version'    => $api_version,
                'errors'         => array(
                    'error_id'   => '6',
                    'error_text' => 'ad not found'
                )
            );
        }
    } else {
        $response_data       = array(
            'api_status'     => '400',
            'api_version'    => $api_version,
            'errors'         => array(
                'error_id'   => '5',
                'error_text' => 'id can not be empty'
            )
        );
    }
}else{
    $response_data       = array(
        'api_status'     => '400',
        'api_version'    => $api_version,
        'errors'         => array(
            'error_id'   => '5',
            'error_text' => 'id can not be empty'
        )
    );
}