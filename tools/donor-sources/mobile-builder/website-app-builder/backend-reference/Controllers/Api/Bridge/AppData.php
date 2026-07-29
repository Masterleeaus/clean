<?php
namespace App\Controllers\Api\Bridge;

use App\Controllers\BaseController;
use App\Libraries\AppSubscription;
use App\Models\AppsModel;
use App\Models\BarNavigationModel;
use App\Models\DrawersModel;
use App\Models\LocalsModel;
use App\Models\NavigationModel;
use App\Models\StylesModel;
use CodeIgniter\HTTP\ResponseInterface;

class AppData extends BaseController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Get app data
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $uid = esc($this->request->getGet("uid"));

        $projects = new AppsModel();

        $app = $projects
            ->where("uid", $uid)
            ->where("deleted_at", 0)
            ->first();

        if (!$app) {
            return $this->respond(["message" => lang("Message.message_14")], 404);
        }

        $appSubscription = new AppSubscription($app["id"]);
        $subscribe = $appSubscription->get_subscription();

        if (!$subscribe || ($subscribe["type"] == "subscription" && $subscribe["expires_at"] < time())) {
            return $this->respond(["message" => lang("Message.message_88")], 400);
        }

        $drawers = new DrawersModel();

        $drawer = $drawers
            ->where("app_id", $app["id"])
            ->first();

        $locals = new LocalsModel();

        $local = $locals
            ->where("app_id", $app["id"])
            ->first();

        $main_navs = new NavigationModel();

        $main_list = $main_navs
            ->where("app_id", $app["id"])
            ->findAll();

        $main_navigations = [];
        foreach ($main_list as $item) {
            $main_navigations[] = [
                "name"  => $item["name"],
                "type"  => (int) $item["type"],
                "icon"  => str_replace('-', '_', $item["icon"]),
                "value" => $item["link"]
            ];
        }

        $bar_navs = new BarNavigationModel();

        $bar_list = $bar_navs
            ->where("app_id", $app["id"])
            ->findAll();

        $bar_navigations = [];
        foreach ($bar_list as $item) {
            $bar_navigations[] = [
                "name"  => $item["name"],
                "type"  => (int) $item["type"],
                "icon"  => str_replace('-', '_', $item["icon"]),
                "value" => $item["link"]
            ];
        }

        $styles = new StylesModel();

        $divs = $styles
            ->where("app_id", $app["id"])
            ->findAll();

        $style_list = [];
        foreach ($divs as $div) {
            $style_list[] = ".".$div["name"];
        }

        return $this->respond([
            "name"             => $app["name"],
            "link"             => $app["link"],
            "is_display_title" => !$app["display_title"],
            "color"            => $app["color_theme"],
            "active_color"     => $app["active_color"],
            "icon_color"       => $app["icon_color"],
            "is_dark"          => (bool) !$app["color_title"],
            "pull_to_refresh"  => !$app["pull_to_refresh"],
            "user_agent"       => $app["user_agent"],
            "email"            => $app["email"],
            "template"         => (int) $app["template"],
            "indicator"        => (int) $app["loader"],
            "indicator_color"  => $app["loader_color"],
            "access"           => [
                "gps"        => (bool) $app["gps"],
                "camera"     => (bool) $app["camera"],
                "microphone" => (bool) $app["microphone"]
            ],
            "drawer"           => [
                "title"            => $drawer["title"],
                "subtitle"         => $drawer["subtitle"],
                "background_mode"  => (int) $drawer["mode"],
                "is_dark"          => !$drawer["theme"],
                "background_image" => !$drawer["background"]
                    ? base_url("upload/default/drawer.jpg")
                    : base_url('upload/drawer/'.$app['uid'].'/'.$drawer["background"]),
                "logo_image"       => !$drawer["logo"]
                    ? base_url("upload/default/logo.png")
                    : base_url('upload/drawer/'.$app['uid'].'/'.$drawer["logo"]),
                "background_color" => $drawer["color"],
                "is_display_logo"  => (bool) $drawer["logo_enabled"],
            ],
            "hide_styles"       => $style_list,
            "localization"      => [
                "error_image"    => !$local["error_image"]
                    ? base_url("upload/default/alert.png")
                    : base_url('upload/info/'.$app['uid'].'/'.$local["error_image"]),
                "error_browser"  => $local["string_6"],
                "exit_message"   => $local["string_7"],
                "exit_title"     => $local["string_2"],
                "yes"            => $local["string_3"],
                "no"             => $local["string_4"],
                "contact"        => $local["string_8"],
                "back"           => $local["string_1"],
            ],
            "navigation"       => [
                "main" => $main_navigations,
                "bar"  => $bar_navigations
            ]
        ], 200);
    }

}