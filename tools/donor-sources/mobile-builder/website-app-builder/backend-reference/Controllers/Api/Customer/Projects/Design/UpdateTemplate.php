<?php namespace App\Controllers\Api\Customer\Projects\Design;

use App\Controllers\PrivateController;
use App\Libraries\Common;
use App\Models\AppsModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class UpdateTemplate extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Update app template settings
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function index(): ResponseInterface
    {
        if (!$this->validate($this->update_validation_type())) {
            return $this->respond(["message" => $this->validator->getErrors()], 400);
        }

        $uid = esc($this->request->getGet("uid"));

        $projects = new AppsModel();

        $app = $projects
            ->where("uid", $uid)
            ->where("user", $this->userId)
            ->where("deleted_at", 0)
            ->select("id")
            ->first();

        if (!$app) {
            return $this->respond(["message" => lang("Message.message_14")], 404);
        }

        $common = new Common();

        $color_theme = esc($this->request->getJsonVar("color_theme"));

        if (!$common->hex_validation($color_theme)) {
            return $this->respond(["message" => lang("Message.message_12")], 400);
        }

        $loader_color = esc($this->request->getJsonVar("loader_color"));

        if (!$common->hex_validation($loader_color)) {
            return $this->respond(["message" => lang("Message.message_16")], 400);
        }

        $icon_color = esc($this->request->getJsonVar("icon_color"));

        if (!$common->hex_validation($icon_color)) {
            return $this->respond(["message" => lang("Message.message_67")], 400);
        }

        $active_color = esc($this->request->getJsonVar("active_color"));

        if (!$common->hex_validation($active_color)) {
            return $this->respond(["message" => lang("Message.message_68")], 400);
        }

        $projects->update($app["id"], [
            "color_theme"     => esc($color_theme),
            "color_title"     => (int) $this->request->getJsonVar("color_title"),
            "template"        => (int) $this->request->getJsonVar("template"),
            "loader"          => (int) $this->request->getJsonVar("loader"),
            "pull_to_refresh" => (int) $this->request->getJsonVar("pull_to_refresh"),
            "loader_color"    => esc($loader_color),
            "display_title"   => (int) $this->request->getJsonVar("display_title"),
            "icon_color"      => esc($icon_color),
            "active_color"    => esc($active_color),
        ]);
        return $this->respond(["status" => "ok"], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for update template
     * @return array
     */
    private function update_validation_type(): array
    {
        return [
            "color_theme"     => [
                "label" => lang("Fields.field_8"),
                "rules" => "required|min_length[7]|max_length[7]"
            ],
            "color_title"     => [
                "label" => lang("Fields.field_9"),  "rules" => "required|in_list[0,1]"
            ],
            "template"        => [
                "label" => lang("Fields.field_10"),
                "rules" => "required|in_list[0,1,2,3]"
            ],
            "loader"          => [
                "label" => lang("Fields.field_15"),
                "rules" => "required|in_list[0,1,2]"
            ],
            "pull_to_refresh" => [
                "label" => lang("Fields.field_16"),
                "rules" => "required|in_list[0,1]"
            ],
            "loader_color"    => [
                "label" => lang("Fields.field_18"),
                "rules" => "required|min_length[7]|max_length[7]"
            ],
            "display_title"   => [
                "label" => lang("Fields.field_45"),
                "rules" => "required|in_list[0,1]"
            ],
            "icon_color"      => [
                "label" => lang("Fields.field_125"),
                "rules" => "required|min_length[7]|max_length[7]"
            ],
            "active_color"    => [
                "label" => lang("Fields.field_126"),
                "rules" => "required|min_length[7]|max_length[7]"
            ],
        ];
    }
}