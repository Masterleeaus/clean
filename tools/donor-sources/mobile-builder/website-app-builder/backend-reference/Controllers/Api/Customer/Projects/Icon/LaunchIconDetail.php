<?php namespace App\Controllers\Api\Customer\Projects\Icon;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use CodeIgniter\HTTP\ResponseInterface;

class LaunchIconDetail extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Get app launch icon settings
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

        helper('filesystem');

        $isUploaded = is_dir(ROOTPATH.'public_html/upload/icons/'.$app['uid']);

        if ($isUploaded) {
            $android_icons = directory_map(ROOTPATH.'public_html/upload/icons/'.$app['uid']."/android", false, true);
            $ios_icons = directory_map(ROOTPATH.'public_html/upload/icons/'.$app['uid']."/ios", false, true);
            $url = base_url("upload/icons/".$app["uid"]);
        } else {
            $android_icons = directory_map(ROOTPATH.'public_html/upload/default/icons/android', false, true);
            $ios_icons = directory_map(ROOTPATH.'public_html/upload/default/icons/ios', false, true);
            $url = base_url("upload/default/icons");
        }

        return $this->respond([
            "icons" => [
                "android" => $android_icons,
                "ios"     => $ios_icons,
                "upload"  => $isUploaded
            ],
            "url"   => $url,
            "unix"  => strtotime(date('m/d/Y h:i:s a', time())),
        ], 200);
    }

}