<?php

if (IS_LOGGED == false) {
    $data = array(
        'status' => 400,
        'error' => 'Not logged in'
    );
    echo json_encode($data);
    exit();
}
if (PT_IsAdmin() == false) {
    $data = array(
        'status' => 400,
        'error' => 'Not admin'
    );
    echo json_encode($data);
    exit();
}

$first = $_POST['type'];

if ($first == 'new-ad') {
    $error = false;
    if (empty($_POST['title']) || empty($_POST['description']) ||  empty($_FILES["image"])) {
        $error = 400;
    } else {

        if (strlen($_POST['title']) < 5) {
            $error = 401;
        } else if (strlen($_POST['description']) < 15) {
            $error = 402;
        } else if (!empty($_FILES["image"]["error"])) {
            $error = 404;
        } else if (!file_exists($_FILES["image"]["tmp_name"])) {
            $error = 405;
        } else if (file_exists($_FILES["image"]["tmp_name"])) {
            $image = getimagesize($_FILES["image"]["tmp_name"]);
            if (!in_array($image[2], array(
                IMAGETYPE_GIF,
                IMAGETYPE_JPEG,
                IMAGETYPE_PNG,
                IMAGETYPE_BMP
            ))) {
                $error = 405;
            }
        } else if (empty($_POST['category']) || !in_array($_POST['category'], array_keys(get_object_vars($pt->categories)))) {
            $error = 406;
        }
    }

    if (empty($error)) {

        $file_info   = array(
            'file' => $_FILES['image']['tmp_name'],
            'size' => $_FILES['image']['size'],
            'name' => $_FILES['image']['name'],
            'type' => $_FILES['image']['type'],
            'crop' => array(
                'width' => 720,
                'height' => 300
            )
        );

        $file_upload     = PT_ShareFile($file_info);
        $insert          = false;
        $active          = (isset($_POST['draft'])) ? '0' : '1';

        if (!empty($file_upload['filename'])) {
            $post_image  = PT_Secure($file_upload['filename']);
            $insert_data = array(
                'title' => PT_Secure(PT_ShortText($_POST['title'], 150)),
                'description' => PT_Secure(PT_ShortText($_POST['description'], 200)),
                'category' => PT_Secure($_POST['category']),
                'image' => $post_image,
                'time' => time(),
                'user_id' => $pt->user->id,
                'active' => $active,
                'views' => 0,
                'shared' => 0,
            );

            $insert     = $db->insert(T_APPADS, $insert_data);
        }

        $data['status'] = ($insert) ? 200 : 500;
        
    } else {
        $data['status'] = $error;
    }
}else if ($first == 'update-ad') {
    $error = false;
    if (empty($_POST['title']) || empty($_POST['description'])) {
        $error = 400;
    } else {

        if (strlen($_POST['title']) < 5) {
            $error = 401;
        } else if (strlen($_POST['description']) < 15) {
            $error = 402;
        } else if (!empty($_FILES["image"])) {

            if (!empty($_FILES["image"]["error"])) {
                $error = 404;
            } else if (!file_exists($_FILES["image"]["tmp_name"])) {
                $error = 405;
            } else if (file_exists($_FILES["image"]["tmp_name"])) {
                $image = getimagesize($_FILES["image"]["tmp_name"]);
                if (!in_array($image[2], array(
                    IMAGETYPE_GIF,
                    IMAGETYPE_JPEG,
                    IMAGETYPE_PNG,
                    IMAGETYPE_BMP
                ))) {
                    $error = 405;
                }
            }
        } else if (empty($_POST['category']) || !in_array($_POST['category'], array_keys(get_object_vars($pt->categories)))) {
            $error = 406;
        } else if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
            $error = 500;
        }
    }

    if (empty($error)) {
        $insert      = false;
        $active      = (isset($_POST['draft'])) ? '0' : '1';
        $active      = (!empty($_POST['status']) && $_POST['status'] == '1') ? '1' : '0';
        $id          = PT_Secure($_POST['id']);

        $update_data = array(
            'title' => PT_Secure(PT_ShortText($_POST['title'], 150)),
            'description' => PT_Secure(PT_ShortText($_POST['description'], 200)),
            'category' => PT_Secure($_POST['category']),
            'active' => $active,
            'shared' => 0,
            "updated" => time(),
        );

        if (!empty($_FILES["image"])) {
            $file_info   = array(
                'file' => $_FILES['image']['tmp_name'],
                'size' => $_FILES['image']['size'],
                'name' => $_FILES['image']['name'],
                'type' => $_FILES['image']['type'],
                'crop' => array(
                    'width' => 720,
                    'height' => 300
                )
            );
            $file_upload     = PT_ShareFile($file_info);
            if (!empty($file_upload['filename'])) {
                $update_data['image'] = PT_Secure($file_upload['filename']);
            } else {
                $error = true;
            }
        }

        $insert         = $db->where('id', $id)->update(T_APPADS, $update_data);
        $ad = $db->where('id', PT_Secure($_POST['id']))->get(T_APPADS);

        $data['status'] = ($insert && empty($error)) ? 200 : 500;
    } else {
        $data['status'] = $error;
    }
} else if ($first == 'delete-ad') {
    if (!empty($_POST['id'])) {
        $ad = $db->where('id', PT_Secure($_POST['id']))->getOne(T_APPADS);
        if (!empty($ad)) {
            $s3      = ($pt->config->s3_upload == 'on' || $pt->config->ftp_upload = 'on' || $pt->config->spaces == 'on') ? true : false;
            if (file_exists($ad->image)) {
                unlink($ad->image);
            } else if ($s3 === true) {
                PT_DeleteFromToS3($ad->image);
            }

            $delete  = $db->where('id', PT_Secure($_POST['id']))->delete(T_APPADS);


            if ($delete) {
                $data = array('status' => 200);
            }
        }
    }
} else{
    $data = array(
        'status' => 400,
        'error' => 'Please try again'
    );
    echo json_encode($data);
    exit();
}