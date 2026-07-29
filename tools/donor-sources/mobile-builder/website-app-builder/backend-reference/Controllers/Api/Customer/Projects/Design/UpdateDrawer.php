<?php namespace App\Controllers\Api\Customer\Projects\Design;

use App\Controllers\PrivateController;
use App\Libraries\Common;
use App\Models\AppsModel;
use App\Models\DrawersModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class UpdateDrawer extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Update drawer settings
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

        $drawers = new DrawersModel();

        $header = $drawers
            ->where("app_id", $app["id"])
            ->select("id")
            ->first();

        $color = esc($this->request->getJsonVar("color"));

        $common = new Common();

        if (!$common->hex_validation($color)) {
            return $this->respond(["message" => lang("Message.message_12")], 400);
        }

        $drawers->update($header["id"], [
            "mode"         => (int) $this->request->getJsonVar("mode"),
            "color"        => esc($color),
            "theme"        => (int) $this->request->getJsonVar("theme"),
            "logo_enabled" => (int) $this->request->getJsonVar("logo_enabled"),
            "title"        => esc($this->request->getJsonVar("title")),
            "subtitle"     => esc($this->request->getJsonVar("subtitle")),
        ]);

        return $this->respond(["status" => "ok"], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for update drawer settings
     * @return array
     */
    private function update_validation_type(): array
    {
        return [
            "mode"         => [
                "label" => lang("Fields.field_47"),
                "rules" => "required|in_list[0,1,2]"
            ],
            "color"        => [
                "label" => lang("Fields.field_24"),
                "rules" => "required|min_length[7]|max_length[7]"
            ],
            "theme"        => [
                "label" => lang("Fields.field_27"),
                "rules" => "required|in_list[0,1]"
            ],
            "logo_enabled" => [
                "label" => lang("Fields.field_28"),
                "rules" => "required|in_list[0,1]"
            ],
            "title"        => [
                "label" => lang("Fields.field_48"),
                "rules" => "max_length[30]"
            ],
            "subtitle"     => [
                "label" => lang("Fields.field_49"),
                "rules" => "max_length[30]"
            ],
        ];
    }
}