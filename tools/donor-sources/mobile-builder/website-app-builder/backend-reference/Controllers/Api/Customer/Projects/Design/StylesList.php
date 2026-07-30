<?php namespace App\Controllers\Api\Customer\Projects\Design;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\StylesModel;
use CodeIgniter\HTTP\ResponseInterface;

class StylesList extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Get styles class names for hide in app frame
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
            ->select("id")
            ->first();

        if (!$app) {
            return $this->respond(["message" => lang("Message.message_14")], 404);
        }

        $styles = new StylesModel();

        $divs = $styles
            ->where("app_id", $app["id"])
            ->findAll();

        $result = [];
        foreach ($divs as $div) {
            $result[] = [
                "name" => $div["name"],
                "id"   => (int) $div["id"]
            ];
        }

        return $this->respond($result, 200);
    }

}