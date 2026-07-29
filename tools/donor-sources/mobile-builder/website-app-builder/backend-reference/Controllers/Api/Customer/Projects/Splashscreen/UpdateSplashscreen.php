<?php namespace App\Controllers\Api\Customer\Projects\Splashscreen;

use App\Controllers\PrivateController;
use App\Libraries\Common;
use App\Models\AppsModel;
use App\Models\SplashScreensModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class UpdateSplashscreen extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Update splashscreen settings
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

        $splash = new SplashScreensModel();

        $screen = $splash
            ->where("app_id", $app["id"])
            ->select("id")
            ->first();

        $color = esc($this->request->getJsonVar("color"));

        $common = new Common();

        if (!$common->hex_validation($color)) {
            return $this->respond(["message" => lang("Message.message_12")], 400);
        }

        $splash->update($screen["id"], [
            "background" => (int) $this->request->getJsonVar("background_mode"),
            "color"      => esc($color),
            "tagline"    => esc($this->request->getJsonVar("tagline")),
            "delay"      => (int) $this->request->getJsonVar("delay"),
            "theme"      => (int) $this->request->getJsonVar("theme"),
            "use_logo"   => (int) $this->request->getJsonVar("use_logo"),
        ]);

        return $this->respond(["status" => "ok"], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for update splashscreen settings
     * @return array
     */
    private function update_validation_type(): array
    {
        return [
            "background_mode" => [
                "label" => lang("Fields.field_23"),
                "rules" => "required|in_list[0,1]"
            ],
            "color"           => [
                "label" => lang("Fields.field_24"),
                "rules" => "required|min_length[7]|max_length[7]"
            ],
            "tagline"         => [
                "label" => lang("Fields.field_25"),
                "rules" => "max_length[40]"
            ],
            "delay"           => [
                "label" => lang("Fields.field_26"),
                "rules" => "required|numeric|is_natural"
            ],
            "theme"           => [
                "label" => lang("Fields.field_27"),
                "rules" => "required|in_list[0,1]"
            ],
            "use_logo"        => [
                "label" => lang("Fields.field_28"),
                "rules" => "required|in_list[0,1]"
            ],
        ];
    }
}