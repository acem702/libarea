<?php

namespace App\Controllers\Post;

use Hleb\Scheme\App\Controllers\MainController;
use Hleb\Constructor\Handlers\Request;
use App\Models\{PostModel, AnswerModel, CommentModel, SubscriptionModel, FeedModel, FacetModel};
use Content, Config, Tpl, Translate, UserData;

class PostController extends MainController
{
    private $user;

    protected $limit = 25;

    public function __construct()
    {
        $this->user  = UserData::get();
    }

    // Полный пост
    public function index($type)
    {
        $slug  = Request::get('slug');
        $id    = Request::getInt('id');

        $content = self::presence($type, $id, $slug, $this->user);
 
        // Просмотры поста
        if (!isset($_SESSION['pagenumbers'])) {
            $_SESSION['pagenumbers'] = [];
        }

        if (!isset($_SESSION['pagenumbers'][$content['post_id']])) {
            PostModel::updateCount($content['post_id'], 'hits');
            $_SESSION['pagenumbers'][$content['post_id']] = $content['post_id'];
        }

        $content['modified'] = $content['post_date'] != $content['post_modified'] ? true : false;

        $facets = PostModel::getPostTopic($content['post_id'], $this->user['id'], 'topic');
        $blog   = PostModel::getPostTopic($content['post_id'], $this->user['id'], 'blog');
 
        // Покажем черновик только автору
        if ($content['post_draft'] == 1 && $content['post_user_id'] != $this->user['id']) {
            redirect('/');
        }

        // If the post type is a page, then depending on the conditions we make a redirect
        // Если тип поста страница, то в зависимости от условий делаем редирект
        if ($content['post_type'] == 'page' && $id > 0) {
            if ($blog) {
                redirect(getUrlByName('blog.article', ['facet_slug' => $blog[0]['facet_slug'], 'slug' => $content['post_slug']]));
            }
            redirect(getUrlByName('facet.article', ['facet_slug' => 'info', 'slug' => $content['post_slug']]));
        }

        $content['post_content']   = Content::text($content['post_content'], 'text');
        $content['post_date_lang'] = lang_date($content['post_date']);

        // Q&A (post_feature == 1) или Дискуссия
        $content['amount_content'] = $content['post_answers_count'];
        if ($content['post_feature'] == 0) {
            $comment_n = $content['post_comments_count'] + $content['post_answers_count'];
            $content['amount_content'] = $comment_n;
        }

        $post_answers = AnswerModel::getAnswersPost($content['post_id'], $this->user['id'], $content['post_feature']);

        $answers = [];
        foreach ($post_answers as $ind => $row) {

            if (strtotime($row['answer_modified']) < strtotime($row['date'])) {
                $row['edit'] = 1;
            }
            // TODO: N+1 см. AnswerModel()
            $row['comments'] = CommentModel::getComments($row['answer_id'], $this->user['id']);
            $answers[$ind]   = $row;
        }

        $content_img  = Config::get('meta.img_path');
        if ($content['post_content_img']) {
            $content_img  = AG_PATH_POSTS_COVER . $content['post_content_img'];
        } elseif ($content['post_thumb_img']) {
            $content_img  = AG_PATH_POSTS_THUMB . $content['post_thumb_img'];
        }

        $desc  = explode("\n", $content['post_content']);
        $desc  = strip_tags($desc[0]);
        if ($desc == '') {
            $desc = strip_tags($content['post_title']);
        }

        if ($content['post_is_deleted'] == 1) {
            Request::getHead()->addMeta('robots', 'noindex');
        }

        Request::getResources()->addBottomStyles('/assets/js/prism/prism.css');
        Request::getResources()->addBottomScript('/assets/js/prism/prism.js');
        Request::getResources()->addBottomScript('/assets/js/zoom/medium-zoom.min.js');

        if ($this->user['id'] > 0 && $content['post_closed'] == 0) {
            Request::getResources()->addBottomStyles('/assets/js/editor/easymde.min.css');
            Request::getResources()->addBottomScript('/assets/js/editor/easymde.min.js');
        }

        if ($content['post_related']) {
            $related_posts = PostModel::postRelated($content['post_related']);
        }

        $m = [
            'og'         => true,
            'twitter'    => true,
            'imgurl'     => $content_img,
            'url'        => getUrlByName('post', ['id' => $content['post_id'], 'slug' => $content['post_slug']]),
        ];

        $topic = $facets[0]['facet_title'] ?? 'agouti';
        if ($blog) {
            $topic = $blog[0]['facet_title'];
        }

        $meta = meta($m, strip_tags($content['post_title']) . ' — ' . $topic, $desc . ' — ' . $topic, $date_article = $content['post_date']);

        $view = $type == 'post' ? '/post/view' : '/page/view';
         
        if ($type == 'post') {    
            return Tpl::agRender(
                '/post/view',
                [
                    'meta'  => $meta,
                    'data'  => [
                        'post'          => $content,
                        'answers'       => $answers,
                        'recommend'     => PostModel::postsSimilar($content['post_id'], $this->user, 5),
                        'related_posts' => $related_posts ?? '',
                        'post_signed'   => SubscriptionModel::getFocus($content['post_id'], $this->user['id'], 'post'),
                        'facets'        => $facets,
                        'blog'          => $blog ?? null,
                        'last_user'     => PostModel::getPostLastUser($content['post_id']),
                        'sheet'         => 'article',
                        'type'          => 'post',
                    ]
                ]
            );
        }  
        
        $slug_facet = Request::get('facet_slug');
        $type_facet = $type == 'info.page' ? 'section' : 'blog';

        $facet  = FacetModel::getFacet($slug_facet, 'slug', $type_facet);
        pageError404($facet);
 
         $m = [
            'og'        => false,
            'twitter'   => false,
            'imgurl'    => false,
            'url'       => getUrlByName('page', ['facet' => $content['post_slug'], 'slug' => $facet['facet_slug']]),
        ];
 
        $title = $content['post_title'] . ' - ' . Translate::get('page');
        return Tpl::agRender(
            '/page/view',
            [
                'meta'  => meta($m, $title, $desc . ' (' . $facet['facet_title'] . ' - ' . Translate::get('page') . ')'),
                'data'  => [
                    'sheet' => 'page',
                    'type'  => $type,
                    'page'  => $content,
                    'facet' => $facet,
                    'pages' => PostModel::recent($facet['facet_id'], $content['post_id'])
                ]
            ]
        );
    }

    public static function presence($type, $id, $slug, $user)
    {
        // Проверим id и получим данные контента
        if ($type == 'post') {
            $content = PostModel::getPost($id, 'id', $user);
            
            // Если slug поста отличается от данных в базе
            if ($slug != $content['post_slug']) {
                redirect(getUrlByName('post', ['id' => $content['post_id'], 'slug' => $content['post_slug']]));
            }
            
            // Редирект для слияния поста
            if ($content['post_merged_id'] > 0 && !UserData::checkAdmin()) {
                redirect('/post/' . $content['post_merged_id']);
            }
            
        } else {
            $content  = PostModel::getPost($slug, 'slug', $user);
        }
        
        // Если контента нет
        pageError404($content);
        
        return $content;
    }

    // Размещение своего поста у себя в профиле
    public function postProfile()
    {
        $post_id    = Request::getPostInt('post_id');
        $post       = PostModel::getPost($post_id, 'id', $this->user);

        // Проверка доступа
        if (!accessСheck($post, 'post', $this->user, 0, 0)) {
            redirect('/');
        }

        // Запретим добавлять черновик в профиль
        if ($post['post_draft'] == 1) {
            return false;
        }

        return PostModel::setPostProfile($post_id, $this->user['id']);
    }

    // Просмотр поста с титульной страницы
    public function shownPost()
    {
        $post_id = Request::getPostInt('post_id');
        $post    = PostModel::getPost($post_id, 'id', $this->user);

        $post['post_content'] = Content::text($post['post_content'], 'text');

        Tpl::agIncludeTemplate('/content/post/postcode', ['post' => $post, 'user'   => $this->user]);
    }

    // Посты по домену
    public function domain($sheet, $type)
    {
        $domain     = Request::get('domain');
        $page       = Request::getInt('page');
        $page       = $page == 0 ? 1 : $page;

        $site       = PostModel::getDomain($domain, $this->user['id']);
        pageError404($site);

        $site['item_content'] = Content::text($site['item_content_url'], 'line');

        $posts      = FeedModel::feed($page, $this->limit, $this->user, $sheet, $site['item_url_domain']);
        $pagesCount = FeedModel::feedCount($this->user, $sheet, $site['item_url_domain']);

        $result = [];
        foreach ($posts as $ind => $row) {
            $text = explode("\n", $row['post_content']);
            $row['post_content_preview']    = Content::text($text[0], 'line');
            $row['post_date']               = lang_date($row['post_date']);
            $result[$ind]                   = $row;
        }

        $m = [
            'og'         => false,
            'twitter'    => false,
            'imgurl'     => false,
            'url'        => getUrlByName('domain', ['domain' => $domain]),
        ];

        return Tpl::agRender(
            '/post/link',
            [
                'meta'  => meta($m, Translate::get('domain') . ': ' . $domain, Translate::get('domain-desc') . ': ' . $domain),
                'data'  => [
                    'sheet'         => 'domain',
                    'pagesCount'    => ceil($pagesCount / $this->limit),
                    'pNum'          => $page,
                    'posts'         => $result,
                    'domains'       => PostModel::getDomainTop($domain),
                    'site'          => $site,
                    'type'          => $type,
                ]
            ]
        );
    }
    
    // Последние 5 страниц по id контенту
    public function last($content_id)
    {
        return PostModel::recent($content_id, null);
    }
}
