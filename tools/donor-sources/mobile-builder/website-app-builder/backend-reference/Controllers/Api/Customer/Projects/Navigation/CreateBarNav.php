<?php namespace App\Controllers\Api\Customer\Projects\Navigation;

use App\Controllers\PrivateController;
use App\Libraries\Common;
use App\Models\AppsModel;
use App\Models\BarNavigationModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class CreateBarNav extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Create new bar navigation item
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function index(): ResponseInterface
    {
        if (!$this->validate($this->create_validation_type())) {
            return $this->respond(["message" => $this->validator->getErrors()], 400);
        }

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

        $action_type = (int) $this->request->getJsonVar("action_type");
        $link = esc($this->request->getJsonVar("link"));

        $common = new Common();

        if ($action_type == 0 || $action_type == 1) {
            if (!$common->uri_validation($link)) {
                return $this->respond(["message" => lang("Message.message_13")], 400);
            }
        }

        if ($action_type == 3) {
            if (!is_numeric($link)) {
                if (!$common->email_validation($link)) {
                    return $this->respond(["message" => lang("Message.message_18")], 400);
                }
            }
        }

        $icon = esc($this->request->getJsonVar("icon"));

        if (!file_exists(ROOTPATH.'public_html/icons/catalog/'.$icon.'.svg')) {
            return $this->respond(["message" => lang("Message.message_32")], 400);
        }

        $navigation = new BarNavigationModel();

        $id = $navigation->insert([
            "name"    => esc($this->request->getJsonVar("name")),
            "app_id"  => $app["id"],
            "type"    => $action_type,
            "link"    => $link,
            "icon"    => esc($this->request->getJsonVar("icon"))
        ]);

        return $this->respond(["id" => $id], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for create new navigation item
     * @return array
     */
    private function create_validation_type(): array
    {
        return [
            "name"         => [
                "label" => lang("Fields.field_19"),
                "rules" => "required|min_length[3]|max_length[50]"
            ],
            "action_type"  => [
                "label" => lang("Fields.field_20"),
                "rules" => "required|in_list[0,1,2,3,4]"
            ],
            "icon"         => [
                "label" => lang("Fields.field_73"),
                "rules" => "required|min_length[3]|max_length[50]"
            ],
        ];
    }
}