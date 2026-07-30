<?php namespace App\Controllers\Api\Customer\Projects\Design;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use CodeIgniter\HTTP\ResponseInterface;

class TemplateSettings extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Get app template settings
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
            ->select("color_theme,color_title,template,loader,
            pull_to_refresh,loader_color,display_title,icon_color,active_color")
            ->first();

        if (!$app) {
            return $this->respond(["message" => lang("Message.message_14")], 404);
        }

        return $this->respond([
            "color_theme"     => $app["color_theme"],
            "color_title"     => (int) $app["color_title"],
            "template"        => (int) $app["template"],
            "loader"          => (int) $app["loader"],
            "pull_to_refresh" => (int) $app["pull_to_refresh"],
            "loader_color"    => $app["loader_color"],
            "display_title"   => (int) $app["display_title"],
            "icon_color"      => $app["icon_color"],
            "active_color"    => $app["active_color"]
        ], 200);
    }
}