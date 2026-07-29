<?php namespace App\Controllers\Api\Customer\Projects\Builds;

use App\Controllers\PrivateController;
use App\Libraries\AppSubscription;
use App\Models\AppsModel;
use App\Models\BuildsModel;
use CodeIgniter\HTTP\ResponseInterface;

class BuildsList extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

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

        $appSubscription = new AppSubscription($app["id"]);
        
        $activeSubscribe = $appSubscription->get_active_subscription();

        $builds = new BuildsModel();
        $items = $builds
            ->where("app_id", $app["id"])
            ->orderBy("id", "DESC")
            ->findAll();

        $list = [];
        foreach ($items as $build) {
            $list[] = [
                "uid"      => $build["uid"],
                "platform" => $build["platform"],
                "status"   => (int) $build["status"],
                "version"  => $build["version"],
                "publish"  => (int) $build["publish"],
                "created"  => date('d-m-Y H:i', $build['created_at']),
                "format"   => $build["platform"] == "ios" ? "ipa" : $build["format"],
                "fail"     => (bool) $build["fail"],
                "message"  => $build["message"]
            ];
        }
        return $this->respond([
            "list" => $list, 
            "remaining_count" => $activeSubscribe ? (int) $activeSubscribe["remaining_count"] : 0, 
            "build_count" => $activeSubscribe ? (int) $activeSubscribe["build_count"] : 0,
        ], 200);
    }
}