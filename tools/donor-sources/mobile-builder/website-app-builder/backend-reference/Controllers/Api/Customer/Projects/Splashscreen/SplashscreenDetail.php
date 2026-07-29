<?php namespace App\Controllers\Api\Customer\Projects\Splashscreen;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\SplashScreensModel;
use CodeIgniter\HTTP\ResponseInterface;

class SplashscreenDetail extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Get app splashscreen settings
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $uid = esc($this->request->getGet("uid"));

        $projects = new AppsModel();

        $app = $projects
            ->where("uid", $uid)
            ->where("user", $this->userId)
            ->where("deleted_at", 0)
            ->select("id,uid")
            ->first();

        if (!$app) {
            return $this->respond(["message" => lang("Message.message_14")], 404);
        }

        $splash = new SplashScreensModel();

        $detail = $splash
            ->where("app_id", $app["id"])
            ->first();

        return $this->respond([
            "background_mode" => (int) $detail["background"],
            "color"           => $detail["color"],
            "image"           => $detail["image"],
            "tagline"         => $detail["tagline"],
            "delay"           => (int) $detail["delay"],
            "theme"           => (int) $detail["theme"],
            "use_logo"        => (int) $detail["use_logo"],
            "background"      => !$detail["image"]
                ? null
                : base_url('upload/splash/'.$app['uid'].'/'.$detail["image"]),
            "logo"            => !$detail["logo"]
                ? null
                : base_url('upload/logos/'.$app['uid'].'/'.$detail["logo"])
        ], 200);
    }

}