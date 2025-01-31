<?php

namespace App\Controllers\Item;

use Hleb\Constructor\Handlers\Request;
use App\Controllers\Controller;
use App\Models\Item\{WebModel, UserAreaModel};
use App\Models\{SubscriptionModel, ActionModel, FacetModel, NotificationModel};
use UserData, Meta, Access;

use App\Validate\RulesItem;

class AddItemController extends Controller
{
    // Add Domain Form
    // Форма добавление домена
    public function index()
    {
        if (Access::trustLevels(config('trust-levels.tl_add_item')) == false) {
            redirect('/web');
        }

        $count_site = UserData::checkAdmin() ? 0 : UserAreaModel::getUserSitesCount($this->user['id']);

        return $this->render(
            '/item/add',
            [
                'meta'  => Meta::get(__('web.add_website')),
                'user'  => $this->user,
                'data'  => [
                    'sheet'      => 'add',
                    'type'       => 'web',
                    'user_count_site'   => $count_site,
                ]
            ],
            'item',
        );
    }

    // Checks and directly adding 
    public function create()
    {
        $data = Request::getPost();

        $basic_host = RulesItem::rulesAdd($data);

        // Instant accommodation for staff only
        // Мгновенное размещение только для персонала
        $published = Request::getPost('published') == 'on' ? 1 : 0;
        $published = UserData::checkAdmin() ? $published : 0;

        $item_last = WebModel::add(
            [
                'item_url'              => $data['url'],
                'item_domain'           => $basic_host,
                'item_title'            => $data['title'],
                'item_content'          => $data['content'] ?? __('web.desc_formed'),
                'item_published'        => $published,
                'item_user_id'          => $this->user['id'],
                'item_close_replies'    => Request::getPost('close_replies') == 'on' ? 1 : null,
            ]
        );

        // Facets (categories are called here) for the site 
        // Фасеты (тут называются категории) для сайта
        $post_fields    = Request::getPost() ?? [];
        $facet_post     = $post_fields['facet_select'] ?? [];
        $topics         = json_decode($facet_post, true);

        if (!empty($topics)) {
            $arr = [];
            foreach ($topics as $row) {
                $arr[] = $row;
            }
            FacetModel::addItemFacets($arr, $item_last['item_id']);
        }

        ActionModel::addLogs(
            [
                'id_content'    => $item_last['item_id'],
                'action_type'   => 'item',
                'action_name'   => 'added',
                'url_content'   => url('web.audits'),
            ]
        );

        $this->notif($this->user['trust_level']);

        SubscriptionModel::focus($item_last['item_id'], 'item');

        is_return(__('web.site_added'), 'success', url('web'));
    }

    // Notification to staff
    // Оповещение персоналу
    public function notif($trust_level)
    {
        if ($trust_level != UserData::REGISTERED_ADMIN) {
            NotificationModel::send(UserData::REGISTERED_ADMIN_ID,  NotificationModel::TYPE_ADD_WEBSITE, url('web.audits'));
        }

        return true;
    }
}
