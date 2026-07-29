<?php namespace App\Controllers\Api\Customer\Projects\Settings;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class UpdateAppSettings extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Update app settings
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

        $app_id = esc($this->request->getJsonVar("app_id"));

        if (count(explode('.', $app_id)) != 3) {
            return $this->respond(["message" => lang("Message.message_15")], 400);
        }

        if (preg_match('/[0-9\s-]/', $app_id)) {
            return $this->respond(["message" => lang("Message.message_15")], 400);
        }

        $projects->update($app["id"], [
            "name"        => esc($this->request->getJsonVar("name")),
            "link"        => esc($this->request->getJsonVar("link")),
            "orientation" => (int) $this->request->getJsonVar("orientation"),
            "app_id"      => $app_id,
            "user_agent"  => esc($this->request->getJsonVar("user_agent")),
            "language"    => strtoupper(esc($this->request->getJsonVar("language"))),
            "email"       => esc($this->request->getJsonVar("email")),
        ]);

        return $this->respond(["status" => "ok"], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for update app
     * @return array
     */
    private function update_validation_type(): array
    {
        return [
            "link"        => [
                "label" => lang("Fields.field_7"),
                "rules" => "required|min_length[3]|max_length[250]|valid_url_strict"
            ],
            "name"        => [
                "label" => lang("Fields.field_6"),
                "rules" => "required|min_length[3]|max_length[50]"
            ],
            "app_id"      => [
                "label" => lang("Fields.field_11"),
                "rules" => "required|min_length[3]|max_length[50]"
            ],
            "user_agent"  => [
                "label" => lang("Fields.field_12"),
                "rules" => "max_length[200]"
            ],
            "orientation" => [
                "label" => lang("Fields.field_13"),
                "rules" => "required|in_list[0,1,2]"
            ],
            "language"    => [
                "label" => lang("Fields.field_40"),
                "rules" => "required|min_length[2]|max_length[2]"
            ],
            "email"       => [
                "label" => lang("Fields.field_43"),
                "rules" => "required|min_length[3]|max_length[70]|valid_email"
            ],
        ];
    }
}