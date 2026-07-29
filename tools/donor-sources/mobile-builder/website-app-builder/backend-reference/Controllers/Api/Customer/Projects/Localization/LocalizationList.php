<?php namespace App\Controllers\Api\Customer\Projects\Localization;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\LocalsModel;
use CodeIgniter\HTTP\ResponseInterface;

class LocalizationList extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Update app permissions
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

        $locals = new LocalsModel();

        $detail = $locals
            ->where("app_id", $app["id"])
            ->first();

        $locals = [];
        $locals[] = ["name" => $detail["string_1"]];
        $locals[] = ["name" => $detail["string_2"]];
        $locals[] = ["name" => $detail["string_3"]];
        $locals[] = ["name" => $detail["string_4"]];
        $locals[] = ["name" => $detail["string_5"]];
        $locals[] = ["name" => $detail["string_6"]];
        $locals[] = ["name" => $detail["string_7"]];
        $locals[] = ["name" => $detail["string_8"]];

        return $this->respond([
            "locals" => $locals,
            "images" => [
                "offline" => !$detail["offline_image"]
                    ? null
                    : base_url('upload/info/'.$app['uid'].'/'.$detail["offline_image"]),
                "error"   => !$detail["error_image"]
                    ? null
                    : base_url('upload/info/'.$app['uid'].'/'.$detail["error_image"])
            ]
        ], 200);
    }

}