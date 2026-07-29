<?php namespace App\Controllers\Api\Customer\Projects\Permissions;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class UpdatePermissions extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Update app permissions
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

        $gps_status = (int) $this->request->getJsonVar("gps");
        $gps_description = esc($this->request->getJsonVar("gps_description"));

        if ($gps_status) {
            if (mb_strlen($gps_description, 'UTF-8') < 10) {
                return $this->respond(["message" => lang("Message.message_72")], 400);
            }
        }

        $camera_status = (int) $this->request->getJsonVar("camera");
        $camera_description = esc($this->request->getJsonVar("camera_description"));

        if ($camera_status) {
            if (mb_strlen($camera_description, 'UTF-8') < 10) {
                return $this->respond(["message" => lang("Message.message_73")], 400);
            }
        }

        $microphone_status = (int) $this->request->getJsonVar("microphone");
        $microphone_description = esc($this->request->getJsonVar("microphone_description"));

        if ($microphone_status) {
            if (mb_strlen($microphone_description, 'UTF-8') < 10) {
                return $this->respond(["message" => lang("Message.message_74")], 400);
            }
        }

        $projects->update($app["id"], [
            "gps"                    => $gps_status,
            "gps_description"        => $gps_description,
            "camera"                 => $camera_status,
            "camera_description"     => $camera_description,
            "microphone"             => $microphone_status,
            "microphone_description" => $microphone_description
        ]);

        return $this->respond(["status" => "ok"], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for update permissions
     * @return array
     */
    private function update_validation_type(): array
    {
        return [
            "gps"                     => [
                "label" => lang("Fields.field_39"),
                "rules" => "required|in_list[0,1]"
            ],
            "gps_description"         => [
                "label" => lang("Fields.field_142"),
                "rules" => "max_length[100]"
            ],
            "camera"                 => [
                "label" => lang("Fields.field_41"),
                "rules" => "required|in_list[0,1]"
            ],
            "camera_description"     => [
                "label" => lang("Fields.field_143"),
                "rules" => "max_length[100]"
            ],
            "microphone"             => [
                "label" => lang("Fields.field_42"),
                "rules" => "required|in_list[0,1]"
            ],
            "microphone_description" => [
                "label" => lang("Fields.field_144"),
                "rules" => "max_length[100]"
            ],
        ];
    }
}