<?php namespace App\Controllers\Api\Customer\Projects\Localization;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\LocalsModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class RefreshLocalization extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Refresh localization value
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
        if ($key === 1) {
            $value = lang("Fields.field_53");
        } else if ($key === 2) {
            $value = lang("Fields.field_54");
        } else if ($key === 3) {
            $value = lang("Fields.field_55");
        } else if ($key === 4) {
            $value = lang("Fields.field_56");
        } else if ($key === 5) {
            $value = lang("Fields.field_57");
        } else if ($key === 6) {
            $value = lang("Fields.field_58");
        } else if ($key === 7) {
            $value = lang("Fields.field_59");
        } else {
            $value = lang("Fields.field_60");
        }

        $locals->update($detail["id"], [
            'string_'.$key => $value
        ]);

        return $this->respond(["value" => $value], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for refresh string value
     * @return array
     */
    private function update_validation_type(): array
    {
        return [
            "id" => [
                "label" => lang("Fields.field_52"),
                "rules" => "required|numeric|is_natural_no_zero|less_than_equal_to[8]"
            ],
        ];
    }
}