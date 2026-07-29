<?php namespace App\Controllers\Api\Customer\Projects\Design;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\StylesModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class CreateStyleDiv extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Create div name (style) from list for hide in app
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function index(): ResponseInterface
    {
        $uid = esc($this->request->getGet("uid"));

        if (!$this->validate($this->create_div_validation_type())) {
            return $this->respond(["message" => $this->validator->getErrors()], 400);
        }

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

        $id = $styles->insert([
            "name"   => esc($this->request->getJsonVar("name")),
            "app_id" => $app["id"]
        ]);

        return $this->respond(["id" => (int) $id], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for create div style
     * @return array
     */
    private function create_div_validation_type(): array
    {
        return [
            "name" => [
                "label" => lang("Fields.field_46"),
                "rules" => "required|min_length[2]|max_length[200]"
            ],
        ];
    }
}