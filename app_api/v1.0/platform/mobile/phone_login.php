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



if (empty($_POST['phone_number']) || empty($_POST['password'])) {
    $response_data       = array(
        'api_status'     => '400',
        'api_version'    => $api_version,
        'errors'         => array(
            'error_id'   => '1',
            'error_text' => 'Please enter your phone number and password'
        )
    );
} else {
    $phone_number        = PT_Secure($_POST['phone_number']);
    $password        = PT_Secure($_POST['password']);
    $password_hashed = sha1($password);

    $db->where("(username = ? or email = ? or phone_number = ?)", array($phone_number, $phone_number, $phone_number));
    $db->where("(password = ? or firebase_id = ?)", array($password_hashed, $password));
    $user = $db->getOne(T_USERS);
    $user_data      = ToArray($user);
    unset($user_data['password']);

    if (!empty($user)) {

        if (!empty($_POST['device_id'])) {
            $db->where('id', $user->id)->update(T_USERS, array('device_id' => PT_Secure($_POST['device_id'])));
        }
        $session_id          = sha1(rand(11111, 99999)) . time() . md5(microtime());
        $platforms           = array('phone', 'web');
        foreach ($platforms as $platform_name) {
            $insert_data     = array(
                'user_id'    => $user->id,
                'session_id' => $session_id,
                'time'       => time(),
                'platform'   => $platform_name
            );

            $insert = $db->insert(T_SESSIONS, $insert_data);
        }

        $response_data       = array(
            'api_status'     => '200',
            'api_version'    => $api_version,
            'success_type'   => 'logged_in',
            'data'           => array(
                'session_id' => $session_id,
                'message'    => 'Successfully logged in, Please wait.',
                'user_id'    => $user->id,
                'cookie'     => $session_id
            ),
            'user_data'      => $user_data
        );
    } else {

        $phone_number               = PT_Secure($_POST['phone_number'], 0);
        $password                   = PT_Secure($_POST['password'], 0);
        $email_code                 = sha1(time() + rand(111, 999));
        $gender                     = 'male';

        if (!empty($_POST['gender'])) {
            if ($_POST['gender'] == 'female') {
                $gender             = 'female';
            }
        }

        $active           = ($pt->config->validation == 'on') ? 0 : 1;
        $password_hashed  = sha1($password);
        $email_code       = sha1(time() + rand(111, 999));
        $insert_data      = array(
            'username'    => $phone_number,
            'password'    => $password_hashed,
            'email'       => $phone_number . "@radiotv1.rw",
            'phone_number' => $phone_number,
            'gender'      => $gender,
            'firebase_id' => $password,
            'active'      => $active,
            'email_code'  => $email_code,
            'last_active' => time(),
            'registered'  => date('Y') . '/' . intval(date('m'))
        );
        if (!empty($_POST['device_id'])) {
            $insert_data['device_id'] = PT_Secure($_POST['device_id']);
        }

        $user_id          = $db->insert(T_USERS, $insert_data);
        if (!empty($user_id)) {
            $db->where("id", $user_id);
            $db->where("password", $password_hashed);
            $user = $db->getOne(T_USERS);
            $user_data      = ToArray($user);
            $user_data["avatar"] = PT_GetMedia($user_data["avatar"]);
            unset($user_data['password']);

            if (!empty($_POST['device_id'])) {
                $db->where('id', $user->id)->update(T_USERS, array('device_id' => PT_Secure($_POST['device_id'])));
            }
            $session_id          = sha1(rand(11111, 99999)) . time() . md5(microtime());
            $platforms           = array('phone', 'web');
            foreach ($platforms as $platform_name) {
                $insert_data     = array(
                    'user_id'    => $user->id,
                    'session_id' => $session_id,
                    'time'       => time(),
                    'platform'   => $platform_name
                );

                $insert = $db->insert(T_SESSIONS, $insert_data);
            }

            $response_data       = array(
                'api_status'     => '200',
                'api_version'    => $api_version,
                'success_type'   => 'logged_in',
                'data'           => array(
                    'session_id' => $session_id,
                    'message'    => 'Successfully logged in, Please wait.',
                    'user_id'    => $user->id,
                    'cookie'     => $session_id
                ),
                'user_data'      => $user_data
            );
        } else {

            $response_data           = array(
                'api_status'         => '400',
                'api_version'        => $api_version,
                'errors'             => array(
                    'error_id'       => '3',
                    'error_text'     => 'Invalid username or password'
                )
            );
        }
    }
}
