<?php namespace App\Controllers\Api\Customer\Projects\Preview;

use App\Controllers\PrivateController;
use App\Libraries\Flangapp;
use App\Models\AppsModel;
use App\Models\BarNavigationModel;
use App\Models\DrawersModel;
use App\Models\NavigationModel;
use App\Models\SplashScreensModel;
use App\Models\StylesModel;
use CodeIgniter\HTTP\ResponseInterface;

class Snack extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Get launch preview data
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $uid = esc($this->request->getGet("uid"));
        $mode = esc($this->request->getGet("mode"));

        $projects = new AppsModel();

        $app = $projects
            ->where("uid", $uid)
            ->where("user", $this->userId)
            ->where("deleted_at", 0)
            ->first();

        if (!$app) {
            return $this->respond(["message" => lang("Message.message_14")], 404);
        }

        $config = $this->get_app_config($app, $mode);

        $flangapp = new Flangapp();

        $files = $flangapp->get_snacks();
        if (!$files["event"]) {
            return $this->respond(["message" => $files["message"]], 400);
        }

        $data = $files["data"];

        return $this->respond([
            "config"       => $config,
            "sdkVersion"   => $data->sdkVersion,
            "dependencies" => $data->dependencies,
            "files"        => $data->files
        ], 200);
    }

    /**
     * Get config for update preview
     * @return ResponseInterface
     */
    public function config(): ResponseInterface
    {
        $uid = esc($this->request->getGet("uid"));
        $mode = esc($this->request->getGet("mode"));

        $projects = new AppsModel();

        $app = $projects
            ->where("uid", $uid)
            ->where("user", $this->userId)
            ->where("deleted_at", 0)
            ->first();

        if (!$app) {
            return $this->respond(["message" => lang("Message.message_14")], 404);
        }

        $config = $this->get_app_config($app, $mode);

        return $this->respond(["config" => $config], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get app config
     * @param array $app
     * @param string $mode
     * @return string
     */
    public function get_app_config(array $app, string $mode) :string
    {
        $styles = new StylesModel();

        $divs = $styles
            ->where("app_id", $app["id"])
            ->findAll();
        $styles = [];
        foreach ($divs as $div) {
            $styles[] = $div["name"];
        }

        $drawers = new DrawersModel();

        $drawer = $drawers
            ->where("app_id", $app["id"])
            ->first();

        $splash_screens = new SplashScreensModel();

        $splash = $splash_screens
            ->where("app_id", $app["id"])
            ->first();

        $app_navigation = new NavigationModel();

        $main_navs = $app_navigation
            ->where("app_id", $app["id"])
            ->findAll();
        $main_navigation = [];
        foreach ($main_navs as $nav) {
            if ($app["template"] == 1) {
                if ($nav["type"]) {
                    continue;
                }
            }
            $main_navigation[] = [
                "icon"   => $nav["icon"],
                "name"   => $nav["name"],
                "action" => [
                    "type"  => $this->get_action_type($nav["type"]),
                    "value" => $nav["link"]
                ]
            ];
        }

        $bar_navigations = new BarNavigationModel();

        $bar_navs = $bar_navigations
            ->where("app_id", $app["id"])
            ->findAll();
        $bar_navigation = [];
        foreach ($bar_navs as $nav) {
            $bar_navigation[] = [
                "icon"   => $nav["icon"],
                "name"   => $nav["name"],
                "action" => [
                    "type"  => $this->get_action_type($nav["type"]),
                    "value" => $nav["link"]
                ]
            ];
        }

        $configFileVariables = [
            '{MODE}',
            '{APP_NAME}',
            '{APP_LINK}',
            '{DISPLAY_TITLE}',
            '{COLOR}',
            '{IS_DARK}',
            '{APP_TEMPLATE}',
            '{INDICATOR}',
            '{INDICATOR_COLOR}',
            '{CSS}',
            '{USER_AGENT}',
            '{DRAWER_TITLE}',
            '{DRAWER_SUBTITLE}',
            '{DRAWER_MODE}',
            '{DRAWER_BACKGROUND_COLOR}',
            '{DRAWER_IS_DARK}',
            '{DRAWER_BACKGROUND_IMAGE}',
            '{DRAWER_LOGO_IMAGE}',
            '{DRAWER_IS_DISPLAY_LOGO}',
            '{SPLASH_BACKGROUND_COLOR}',
            '{SPLASH_COLOR}',
            '{SPLASH_BACKGROUND_IMAGE}',
            '{SPLASH_TAGLINE}',
            '{SPLASH_LOGO}',
            '{SPLASH_IS_DISPLAY_LOGO}',
            '{SPLASH_IS_IMAGE_BACKGROUND}',
            '{MAIN_NAVIGATION}',
            '{BAR_NAVIGATION}',
            '{MODAL_NAVIGATION}',
            '{ACTIVE_COLOR}',
            '{ICON_COLOR}'
        ];

        $configCodeVariable = [
            $mode === "app" ? "app" : "splash",
            $app["name"],
            $app["link"],
            (int) !$app["display_title"],
            $app["color_theme"],
            (int) !$app["color_title"],
            (int) $app["template"],
            (int) $app["loader"],
            $app["loader_color"],
            json_encode($styles),
            $app["user_agent"],
            $drawer["title"],
            $drawer["subtitle"],
            (int) $drawer["mode"],
            $drawer["color"],
            !$drawer["theme"] ? 1 : 0,
            !$drawer["background"]
                ? base_url('snack/static/drawer_background.png')
                : base_url('upload/drawer/'.$app['uid'].'/'.$drawer["background"]),
            !$drawer["logo"]
                ? base_url('snack/static/logo.png')
                : base_url('upload/drawer/'.$app['uid'].'/'.$drawer["logo"]),
            $drawer["logo_enabled"] ? 1 : 0,
            $splash["color"],
            !$splash["theme"] ? "#ffffff" : "#000000",
            !$splash["image"]
                ? base_url('snack/static/splash_background.png')
                : base_url('upload/splash/'.$app['uid'].'/'.$splash["image"]),
            $splash["tagline"],
            !$splash["logo"]
                ? base_url('snack/static/logo.png')
                : base_url('upload/logos/'.$app['uid'].'/'.$splash["logo"]),
            $splash["use_logo"] ? 1 : 0,
            (int) $splash["background"],
            json_encode($main_navigation),
            json_encode($bar_navigation),
            json_encode([]),
            $app["active_color"],
            $app["icon_color"]
        ];

        return str_replace(
            $configFileVariables,
            $configCodeVariable,
            file_get_contents(WRITEPATH.'snack/config.js')
        );
    }

    /**
     * Get nav action type
     * @param int $type
     * @return string
     */
    public function get_action_type(int $type) :string
    {
        switch($type)
        {
            case 1 :
                return "external";
            case 2 :
                return "share";
            case 3 :
                return "email";
            case 4 :
                return "phone";
            default :
                return "internal";
        }
    }
}