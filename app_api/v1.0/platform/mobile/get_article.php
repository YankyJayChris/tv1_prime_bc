<?php

if (empty($_GET['article_id'])) {
    $response_data       = array(
        'api_status'     => '400',
        'api_version'    => $api_version,
        'errors'         => array(
            'error_id'   => '1',
            'error_text' => 'Bad Request, Invalid or missing parameter'
        )
    );
} elseif (empty($_GET['article_id'])) {
    $response_data    = array(
        'api_status'  => '400',
        'api_version' => $api_version,
        'errors' => array(
            'error_id' => '4',
            'error_text' => 'id can not be empty'
        )
    );
}
else{
    $article_id = PT_Secure($_GET['article_id']);
    $post = $db->where('id', $article_id)->where('active', '1')->getOne(T_POSTS);

    if (empty($post)) {
        $response_data       = array(
            'api_status'     => '404',
            'api_version'    => $api_version,
            'errors'         => array(
                'error_id'   => '2',
                'error_text' => 'Article does not exist'
            )
        );
    } else {
        $post->orginal_text =  $post->text;
        $post->text =  strip_tags(htmlspecialchars_decode($post->text));
        $post->views = number_format($post->views);
        $post->image = PT_GetMedia($post->image);
        $post->url = PT_Link('articles/read/' . PT_URLSlug($post->title, $post->id));
        $post->time_format = date('d-F-Y', $post->time);
        $post->text_time = PT_Time_Elapsed_String($post->time);
        $post->user_data = PT_UserData($post->user_id);
        unset($post->user_data->password);
        $post->comments_count     = $db->where('post_id', $post->id)->getValue(T_COMMENTS, 'COUNT(*)');
        $post->likes     = $db->where('post_id', $post->id)->where('type', 1)->getValue(T_DIS_LIKES, "count(*)");
        $post->dislikes  = $db->where('post_id', $post->id)->where('type', 2)->getValue(T_DIS_LIKES, "count(*)");
        $u_like     = $db->where('post_id', $post->id)->where('user_id', $user->id)->where('type', 1)->getValue(T_DIS_LIKES, "count(*)");
        $post->liked      = ($u_like > 0) ? 1 : 0;

        $u_dislike  = $db->where('post_id', $post->id)->where('user_id', $user->id)->where('type', 2)->getValue(T_DIS_LIKES, "count(*)");
        $post->disliked   = ($u_dislike > 0) ? 1 : 0;

        $update_data = array(
            'views' => $post->views + 1,
        );
        
        $views= $db->where('id', $article_id)->update(T_POSTS, $update_data);

        $response_data     = array(
            'api_status'   => '200',
            'api_version'  => $api_version,
            'success_type' => 'get_article',
            'data'      => $post
        );
    }
}