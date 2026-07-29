<?php namespace App\Controllers\Api\Customer\Projects\Icon;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use CodeIgniter\HTTP\ResponseInterface;

class DownloadIcon extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Download launch icon
     */
    public function index()
    {
        if (!$this->validate($this->download_validation_type())) {
            return "1";
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

        helper('filesystem');

        $isUploaded = is_dir(ROOTPATH.'public_html/upload/icons/'.$app['uid']);

        $icon_name = esc($this->request->getJsonVar("name"));
        $icon_type = esc($this->request->getJsonVar("type"));

        if ($isUploaded) {
            if (!file_exists(ROOTPATH.'public_html/upload/icons/'.$app['uid']."/".$icon_type."/".$icon_name)) {
                return $this->respond(["message" => lang("Message.message_75")], 404);
            }
            return readfile(ROOTPATH.'public_html/upload/icons/'.$app['uid']."/".$icon_type."/".$icon_name);
        } else {
            if (!file_exists(ROOTPATH.'public_html/upload/default/icons/'.$icon_type.'/'.$icon_name)) {
                return $this->respond(["message" => lang("Message.message_75")], 404);
            }
            return readfile(ROOTPATH.'public_html/upload/default/icons/'.$icon_type.'/'.$icon_name);
        }
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for download icon
     * @return array
     */
    private function download_validation_type(): array
    {
        return [
            "type" => [
                "label" => lang("Fields.field_145"),
                "rules" => "required|in_list[android,ios]"
            ],
            "name" => [
                "label" => lang("Fields.field_146"),
                "rules" => "required|max_length[200]"
            ],
        ];
    }
}