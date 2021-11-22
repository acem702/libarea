<?php

namespace App\Controllers\Post;

use Hleb\Scheme\App\Controllers\MainController;
use Hleb\Constructor\Handlers\Request;
use App\Models\User\UserModel;
use App\Models\{FacetModel, PostModel};
use Content, Base, UploadImage, Validation, Translate;

class EditPostController extends MainController
{
    private $uid;

    public function __construct()
    {
        $this->uid  = Base::getUid();
    }

    // Форма редактирования post
    public function index()
    {
        $post_id    = Request::getInt('id');
        $post       = PostModel::getPostId($post_id);
        if (!accessСheck($post, 'post', $this->uid, 0, 0)) {
            redirect('/');
        }

        Request::getHead()->addStyles('/assets/css/image-uploader.css');
        Request::getResources()->addBottomStyles('/assets/css/select2.css');
        Request::getResources()->addBottomScript('/assets/js/image-uploader.js');
        Request::getResources()->addBottomStyles('/assets/editor/editormd.css');
        Request::getResources()->addBottomScript('/assets/editor/meditor.min.js');
        Request::getResources()->addBottomScript('/assets/js/select2.min.js');

        return view(
            '/post/edit',
            [
                'meta'  => meta($m = [], Translate::get('edit post')),
                'uid'   => $this->uid,
                'data'  => [
                    'sheet'         => 'edit-post',
                    'post'          => $post,
                    'related_posts' => PostModel::postRelated($post['post_related']),
                    'user'          => UserModel::getUser($post['post_user_id'], 'id'),
                    'facet_select'  => PostModel::getPostTopic($post['post_id'], $this->uid['user_id'], 'topic'),
                    'topic_blog'    => PostModel::getPostTopic($post['post_id'], $this->uid['user_id'], 'blog'),
                ]
            ]
        );
    }

    public function edit()
    {
        $post_id                = Request::getPostInt('post_id');
        $post_title             = Request::getPost('post_title');
        $post_content           = $_POST['post_content']; // для Markdown
        $post_type              = Request::getPostInt('post_type');
        $post_translation       = Request::getPostInt('translation');
        $post_draft             = Request::getPostInt('post_draft');
        $post_closed            = Request::getPostInt('closed');
        $post_top               = Request::getPostInt('top');
        $draft                  = Request::getPost('draft');
        $post_user_new          = Request::getPost('user_select');
        $post_merged_id         = Request::getPostInt('post_merged_id');
        $post_tl                = Request::getPostInt('content_tl');
        $blog_id               = Request::getPostInt('blog_id');

        // Связанные посты и темы
        $post_fields    = Request::getPost() ?? [];
        $post_related   = implode(',', $post_fields['post_select'] ?? []);
        $topics         = $post_fields['facet_select'] ?? [];

        // Проверка доступа 
        $post   = PostModel::getPostId($post_id);
        if (!accessСheck($post, 'post', $this->uid, 0, 0)) {
            redirect('/');
        }

        // Если пользователь забанен / заморожен
        $user = UserModel::getUser($this->uid['user_id'], 'id');
        Base::accountBan($user);
        Content::stopContentQuietМode($user);

        $redirect   = getUrlByName('post.edit', ['id' =>$post_id]);

        if (!$topics) {
            addMsg(Translate::get('select topic'), 'error');
            redirect($redirect);
        }

        // Если есть смена post_user_id и это TL5
        $post_user_id = $post['post_user_id'];
        if ($post['post_user_id'] != $post_user_new) {
            $post_user_id = $post['post_user_id'];
            if ($this->uid['user_trust_level'] == 5) {
                $post_user_id = $post_user_new;
            }
        }

        Validation::Limits($post_title, Translate::get('title'), '6', '250', $redirect);
        Validation::Limits($post_content, Translate::get('the post'), '6', '25000', $redirect);

        // Проверим хакинг формы
        if ($post['post_draft'] == 0) {
            $draft = 0;
        }

        $post_date = $post['post_date'];
        if ($draft == 1 && $post_draft == 0) {
            $post_date = date("Y-m-d H:i:s");
        }

        // Обложка поста
        $cover          = $_FILES['images'];
        if ($_FILES['images']['name'][0]) {
            $post_img = UploadImage::cover_post($cover, $post, $redirect, $this->uid['user_id']);
        }

        $post_img = $post_img ?? $post['post_content_img'];

        $data = [
            'post_id'               => $post_id,
            'post_title'            => $post_title,
            'post_type'             => $post_type,
            'post_translation'      => $post_translation,
            'post_date'             => $post_date,
            'post_user_id'          => $post_user_id,
            'post_draft'            => $post_draft,
            'post_content'          => Content::change($post_content),
            'post_content_img'      => $post_img ?? '',
            'post_related'          => $post_related,
            'post_merged_id'        => $post_merged_id,
            'post_tl'               => $post_tl,
            'post_closed'           => $post_closed,
            'post_top'              => $post_top,
        ];

        // Think through the method 
        // $url = Base::estimationUrl($post_content);

        // Перезапишем пост
        PostModel::editPost($data);
        
        if ($blog_id != 0) {
          $topics = array_merge(['0' => $blog_id], $topics);
        }  
  
        $arr = [];
        foreach ($topics as $row) {
            $arr[] = array($row, $post_id);
        }
        FacetModel::addPostFacets($arr, $post_id);
        

      /*  if (!empty($topics)) {
            $arr = [];
            foreach ($topics as $row) {
                $arr[] = array($row, $post_id);
            }
            FacetModel::addPostFacets($arr, $post_id);
        } */

        redirect(getUrlByName('post', ['id' => $post['post_id'], 'slug' => $post['post_slug']]));
    }

    // Удаление обложки
    function imgPostRemove()
    {
        $post_id    = Request::getInt('id');
        $post = PostModel::getPostId($post_id);
        if (!accessСheck($post, 'post', $this->uid, 0, 0)) {
            redirect('/');
        }

        PostModel::setPostImgRemove($post['post_id']);
        UploadImage::cover_post_remove($post['post_content_img'], $this->uid['user_id']);

        addMsg(Translate::get('cover removed'), 'success');
        redirect(getUrlByName('post.edit', ['id' =>$post['post_id']]));
    }

    public function uploadContentImage()
    {
        $user_id    = $this->uid['user_id'];
        $type       = Request::getGet('type');
        $post_id    = Request::getGet('post_id');

        // Фотографии в тело контента
        $img         = $_FILES['editormd-image-file'];
        if ($_FILES['editormd-image-file']['name']) {

            $post_img = UploadImage::post_img($img, $user_id);
            $response = array(
                "url"     => $post_img,
                "message" => Translate::get('successful download'),
                "success" => 1,
            );

            return json_encode($response);
        }

        $response = array(
            "message" => Translate::get('error in loading'),
            "success" => 0,
        );

        return json_encode($response);
    }
}
