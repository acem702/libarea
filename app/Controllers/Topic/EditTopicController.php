<?php

namespace App\Controllers\Topic;

use Hleb\Scheme\App\Controllers\MainController;
use Hleb\Constructor\Handlers\Request;
use App\Models\User\UserModel;
use App\Models\{TopicModel, PostModel};
use Base, Validation, UploadImage;

class EditTopicController extends MainController
{
    private $uid;

    public function __construct()
    {
        $this->uid  = Base::getUid();
    }

    // Форма редактирования topic
    public function index()
    {
        $tl     = Validation::validTl($this->uid['user_trust_level'], 5, 0, 1);
        if ($tl === false) {
            redirect('/');
        }

        $topic_id   = Request::getInt('id');
        $topic      = TopicModel::getTopic($topic_id, 'id');
        Base::PageError404($topic);

        Request::getResources()->addBottomStyles('/assets/css/select2.css');
        Request::getHead()->addStyles('/assets/css/image-uploader.css');
        Request::getResources()->addBottomScript('/assets/js/image-uploader.js');
        Request::getResources()->addBottomScript('/assets/js/select2.min.js');

        $topic_related      = TopicModel::topicRelated($topic['topic_related']);
        $post_related       = PostModel::postRelated($topic['topic_post_related']);

        $topic_parent_id    = '';
        if ($topic['topic_parent_id']  != 0) {
            $topic_parent_id    = TopicModel::topicMain($topic['topic_parent_id']);
        }

        $meta = meta($m = [], lang('edit topic') . ' | ' . $topic['topic_title']);
        $data = [
            'topic'             => $topic,
            'topic_related'     => $topic_related,
            'topic_parent_id'   => $topic_parent_id,
            'post_related'      => $post_related,
            'user'              => UserModel::getUser($topic['topic_user_id'], 'id'),
            'sheet'             => 'topics',
        ];

        return view('/topic/edit', ['meta' => $meta, 'uid' => $this->uid, 'data' => $data]);
    }

    public function edit()
    {
        $tl     = Validation::validTl($this->uid['user_trust_level'], 5, 0, 1);
        if ($tl === false) {
            redirect('/');
        }

        $topic_id                   = Request::getPostInt('topic_id');
        $topic_title                = Request::getPost('topic_title');
        $topic_description          = Request::getPost('topic_description');
        $topic_short_description    = Request::getPost('topic_short_description');
        $topic_info                 = Request::getPost('topic_info');
        $topic_slug                 = Request::getPost('topic_slug');
        $topic_seo_title            = Request::getPost('topic_seo_title');
        $topic_merged_id            = Request::getPostInt('topic_merged_id');
        $topic_is_parent            = Request::getPostInt('topic_is_parent');
        $topic_count                = Request::getPostInt('topic_count');
        $topic_user_new             = Request::getPost('user_select');
        $topic_tl                   = Request::getPostInt('content_tl');

        $topic = TopicModel::getTopic($topic_id, 'id');
        Base::PageError404($topic);

        // Если убираем тему из корневой, то должны очистеть те темы, которые были подтемами
        if ($topic['topic_is_parent'] == 1 && $topic_is_parent == 0) {
            TopicModel::clearBinding($topic['topic_id']);
        }

        $redirect = getUrlByName('admin.topic.edit', ['id' => $topic['topic_id']]);

        Validation::charset_slug($topic_slug, 'Slug (url)', $redirect);
        Validation::Limits($topic_title, lang('title'), '3', '64', $redirect);
        Validation::Limits($topic_slug, lang('slug'), '3', '43', $redirect);
        Validation::Limits($topic_seo_title, lang('name SEO'), '4', '225', $redirect);
        Validation::Limits($topic_description, lang('meta description'), '44', '225', $redirect);
        Validation::Limits($topic_short_description, lang('short description'), '11', '160', $redirect);
        Validation::Limits($topic_info, lang('Info'), '14', '5000', $redirect);

        // Запишем img
        $img = $_FILES['images'];
        $check_img  = $_FILES['images']['name'][0];
        if ($check_img) {
            UploadImage::img($img, $topic['topic_id'], 'topic');
        }

        // Если есть смена topic_user_id и это TL5
        $topic_user_id = $topic['topic_user_id'];
        if ($topic['topic_user_id'] != $topic_user_new) {
            $topic_user_id = $topic['topic_user_id'];
            if ($this->uid['user_trust_level'] == 5) {
                $topic_user_id = $topic_user_new;
            }
        }

        $post_fields    = Request::getPost() ?? [];
        $data = [
            'topic_id'                  => $topic_id,
            'topic_title'               => $topic_title,
            'topic_description'         => $topic_description,
            'topic_short_description'   => $topic_short_description,
            'topic_info'                => $topic_info,
            'topic_slug'                => $topic_slug,
            'topic_seo_title'           => $topic_seo_title,
            'topic_parent_id'           => implode(',', $post_fields['topic_parent_id'] ?? ['0']),
            'topic_user_id'             => $topic_user_id,
            'topic_tl'                  => $topic_tl,
            'topic_is_parent'           => $topic_is_parent,
            'topic_post_related'        => implode(',', $post_fields['post_related'] ?? ['0']),
            'topic_related'             => implode(',', $post_fields['topic_related'] ?? ['0']),
            'topic_count'               => $topic_count,
        ];

        TopicModel::edit($data);

        addMsg(lang('changes saved'), 'success');

        redirect(getUrlByName('topic', ['slug' => $topic_slug]));
    }
}
