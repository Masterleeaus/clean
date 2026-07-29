<?php namespace App\Controllers\Api\Customer\Projects\Navigation;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\NavigationModel;
use CodeIgniter\HTTP\ResponseInterface;

class RemoveMainNav extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Remove main navigation item
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $id = (int) $this->request->getGet("id");

        $navigation = new NavigationModel();

        $item = $navigation
            ->where("id", $id)
            ->select("id,app_id,icon")
            ->first();

        if (!$item) {
            return $this->respond(["message" => lang("Message.message_17")], 400);
        }

        $projects = new AppsModel();

        $app = $projects
            ->where("id", $item["app_id"])
            ->where("user", $this->userId)
            ->where("deleted_at", 0)
            ->select("id,uid")
            ->first();

        if (!$app) {
            return $this->respond(["message" => lang("Message.message_14")], 404);
        }

        $navigation->delete($item["id"]);

        return $this->respond(["status" => "ok"], 200);
    }

}