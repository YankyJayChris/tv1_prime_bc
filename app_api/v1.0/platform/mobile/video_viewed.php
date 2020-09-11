<?php

if (empty($_GET['phone_id']) && empty($_GET['video_id'])) {

    $response_data       = array(
        'api_status'     => '400',
        'api_version'    => $api_version,
        'errors'         => array(
            'error_id'   => '2',
            'error_text' => 'Bad Request, Invalid or missing parameter'
        )
    );
} else {

    $finger = PT_Secure($_GET['phone_id']);
    $video_id = PT_Secure($_GET['video_id']);
    $video    = $db->where('id', $video_id)->getOne(T_VIDEOS, array('video_id', 'user_id', 'views'));

    if (empty($video)) {
        $response_data       = array(
            'api_status'     => '404',
            'api_version'    => $api_version,
            'errors'         => array(
                'error_id'   => '2',
                'error_text' => 'Video does not exist'
            )
        );
    } else {
        $my_updated;
        $is_viewed = $db->where('fingerprint', $finger)->where('video_id', $video_id)->where('time', time() - 31556926, '>=')->getValue(T_VIEWS, "count(*)");
        if ($is_viewed == 0) {
            $data_info = array(
                'video_id' => $video_id,
                'fingerprint' => $finger,
                'time'    => time()
            );
            if (IS_LOGGED == true) {
                $data_info['user_id'] = $user->id;
            }
            $db->insert(T_VIEWS, $data_info);
            // $update = $video->views += 1;
            $my_updated = $db->where('id', $video_id)->update(T_VIDEOS, array('views' => $video->views += 1));
            $response_data     = array(
                'api_status'   => '200',
                'api_version'  => $api_version,
                'success_type' => 'video_viewed',
                'message'      => 'video viewed'
            );
        } else {

            $response_data     = array(
                'api_status'   => '200',
                'api_version'  => $api_version,
                'success_type' => 'video_viewed',
                'message'      => "you have watched this video"
            );
        }
    }
}
