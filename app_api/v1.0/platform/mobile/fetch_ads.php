<?php

$limit = (!empty($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0 && $_GET['limit'] <= 50) ? PT_Secure($_GET['limit']) : 20;
$offset = (!empty($_GET['offset']) && is_numeric($_GET['offset']) && $_GET['offset'] > 0) ? PT_Secure($_GET['offset']) : 0;
if ($offset > 0) {
    $db->where('id', $offset, '>');
}
if (!empty($_GET['category_id'])) {
    $db->where('category', PT_Secure($_GET['category_id']));
}
if (!empty($_GET['keyword'])) {
    $keyword = PT_Secure($_GET['keyword']);
    $db->where("(`title` LIKE '%$keyword%' OR `description` LIKE '%$keyword%' OR `tags` LIKE '%$keyword%')");
}

$posts   = $db->where('user_id', $pt->blocked_array, 'NOT IN')->where('active', '1')->orderBy('id', 'DESC')->get(T_APPADS, $limit);
foreach ($posts as $key => $post) {
    $post->title = $post->title;
    $post->description = $post->description;
    $post->image = PT_GetMedia($post->image);
    $post->time = date('d-F-Y', $post->updated);
    $post->url = $post->url;
    $post->views = number_format($post->views);
}
$response_data     = array(
    'api_status'   => '200',
    'api_version'  => $api_version,
    'success_type' => 'fetch_ads',
    'data'      => $posts
);