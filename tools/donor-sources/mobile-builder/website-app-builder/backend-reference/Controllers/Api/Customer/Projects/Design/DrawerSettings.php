<?php namespace App\Controllers\Api\Customer\Projects\Design;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\DrawersModel;
use CodeIgniter\HTTP\ResponseInterface;

class DrawerSettings extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Get drawer settings
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

        $drawers = new DrawersModel();

        $header = $drawers
            ->where("app_id", $app["id"])
            ->first();

        return $this->respond([
            "mode"         => (int) $header["mode"],
            "color"        => $header["color"],
            "theme"        => (int) $header["theme"],
            "logo_enabled" => (int) $header["logo_enabled"],
            "title"        => $header["title"],
            "subtitle"     => $header["subtitle"],
            "background"   => !$header["background"]
                ? null
                : base_url('upload/drawer/'.$app['uid'].'/'.$header["background"]),
            "logo"         => !$header["logo"]
                ? null
                : base_url('upload/drawer/'.$app['uid'].'/'.$header["logo"])
        ], 200);
    }

}