<?php

$categories = array();
$sub_categories = array();

$all_categories = $db->where('type', 'video_category')->get(T_LANGS);
$my_categories = array();
foreach ($all_categories as $key => $category) {
    $cat = ["id" =>  $category->id,
    "lang_key" =>  $category->lang_key,
    "type" =>  $category->type,
    "english" =>  $category->english,];


    $my_categories[] = $cat;
}

$response_data = array(
    'api_status' => '200',
    'api_version' => $api_version,
    'message' => 'fetch_categories',
    'categoryies' => $my_categories,
);
