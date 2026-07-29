<?php namespace App\Controllers\Api\Customer\Projects\Design;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\StylesModel;
use CodeIgniter\HTTP\ResponseInterface;

class RemoveStyleDiv extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Remove div name (style) from list for hide in app
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        if (!$this->validate($this->remove_validation_type())) {
            return $this->respond(["message" => $this->validator->getErrors()], 400);
        }

        $styles = new StylesModel();

        $div = $styles
            ->where("id", (int) $this->request->getJsonVar("div_id"))
            ->first();

        if (!$div) {
            return $this->respond(["message" => lang("Message.message_24")], 400);
        }

        $projects = new AppsModel();

        $app = $projects
            ->where("id", $div["app_id"])
            ->where("user", $this->userId)
            ->where("deleted_at", 0)
            ->select("id")
            ->first();

        if (!$app) {
            return $this->respond(["message" => lang("Message.message_14")], 404);
        }

        $styles->delete($div["id"]);

        return $this->respond(["status" => "ok"], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for remove div class
     * @return array
     */
    private function remove_validation_type(): array
    {
        return [
            "div_id"  => [
                "label" => lang("Fields.field_140"),
                "rules" => "required|min_length[1]|max_length[250]|numeric"
            ],
        ];
    }
}