<?php namespace App\Controllers\Api\Customer\Projects\Permissions;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionsDetail extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Get app permissions
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
            ->select("gps,camera,microphone,camera_description,microphone_description,gps_description")
            ->first();

        if (!$app) {
            return $this->respond(["message" => lang("Message.message_14")], 404);
        }

        return $this->respond([
            "gps" => [
                "status"      => (int) $app["gps"],
                "description" => $app["gps_description"]
            ],
            "camera" => [
                "status"      => (int) $app["camera"],
                "description" => $app["camera_description"]
            ],
            "microphone" => [
                "status"      => (int) $app["microphone"],
                "description" => $app["microphone_description"]
            ],
        ], 200);
    }

}