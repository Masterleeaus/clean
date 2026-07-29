<?php namespace App\Controllers\Api\Customer\Projects\Localization;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\LocalsModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class UpdateLocalization extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Update localization
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

        $locals = new LocalsModel();

        $detail = $locals
            ->where("app_id", $app["id"])
            ->select("id")
            ->first();

        $key = (int) $this->request->getJsonVar("id");

        $locals->update($detail["id"], [
            'string_'.$key => esc($this->request->getJsonVar("name"))
        ]);
        return $this->respond(["status" => "ok"], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for update string value
     * @return array
     */
    private function update_validation_type(): array
    {
        return [
            "name" => [
                "label" => lang("Fields.field_51"),
                "rules" => "required|min_length[2]|max_length[500]"
            ],
            "id"   => [
                "label" => lang("Fields.field_52"),
                "rules" => "required|numeric|is_natural_no_zero|less_than_equal_to[8]"
            ],
        ];
    }
}