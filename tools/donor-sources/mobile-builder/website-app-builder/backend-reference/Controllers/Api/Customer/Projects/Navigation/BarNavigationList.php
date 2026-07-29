<?php namespace App\Controllers\Api\Customer\Projects\Navigation;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\BarNavigationModel;
use CodeIgniter\HTTP\ResponseInterface;

class BarNavigationList extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Get bar navigation
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

        $navigation = new BarNavigationModel();

        $list = $navigation
            ->where("app_id", $app["id"])
            ->findAll();

        $items = [];

        foreach ($list as $item) {
            $items[] = [
                "id"   => (int) $item["id"],
                "type" => (int) $item["type"],
                "icon" => $item["icon"],
                "link" => $item["link"],
                "name" => $item["name"]
            ];
        }

        return $this->respond($items, 200);
    }

}