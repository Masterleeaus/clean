<?php namespace App\Controllers\Api\Customer\Projects\Builds;

use App\Controllers\PrivateController;
use App\Libraries\AppSubscription;
use App\Libraries\Common;
use App\Libraries\GitHub;
use App\Libraries\Uid;
use App\Models\AppsModel;
use App\Models\BuildsModel;
use App\Models\SignsAndroidModel;
use App\Models\SignsIosModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class CreateBuild extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Create new build
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
            ->first();

        if (!$app) {
            return $this->respond(["message" => lang("Message.message_14")], 404);
        }

        $appSubscription = new AppSubscription($app["id"]);

        if (!$appSubscription->active_subscription_exists()) {
            return $this->respond(["message" => lang("Message.message_88")], 400);
        }

        $version = esc($this->request->getJsonVar("version"));

        $common = new Common();

        if (!$common->version_format_validation($version)) {
            return $this->respond(["message" => lang("Message.message_39")], 400);
        }

        $builds = new BuildsModel();

        $active_total = $builds
            ->where("app_id", $app["id"])
            ->where("status", 0)
            ->countAllResults();

        if ($active_total) {
            return $this->respond(["message" => lang("Message.message_31")], 400);
        }

        $platform    = esc($this->request->getJsonVar("platform"));
        $android_key = esc($this->request->getJsonVar("android_key_id"));
        $ios_key     = esc($this->request->getJsonVar("ios_key_id"));
        $sign_uid    = $platform == "android" ? $android_key : $ios_key;

        if (!$sign_uid) {
            return $this->respond(["message" => lang("Message.message_40")], 400);
        }

        $signing_id = $this->sign_validation($sign_uid, $platform, $app["id"]);

        if (!$signing_id) {
            return $this->respond(["message" => lang("Message.message_40")], 400);
        }

        $format  = esc($this->request->getJsonVar("format"));
        $publish = (int) $this->request->getJsonVar("publish");

        $uid = new Uid();

        $uid_build = $uid->create();

        $total = $builds
            ->where("app_id", $app["id"])
            ->countAllResults();

        if (!$total) {
            // create branch
            $git = new GitHub();

            $git_result = $git->create_branch($app["uid"]);

            if (!$git_result["event"]) {
                return $this->respond(["message" => $git_result["message"]], 502);
            }
        }

        $builds->insert([
            "app_id"         => $app["id"],
            "uid"            => $uid_build,
            "platform"       => $platform,
            "status"         => 0,
            "version"        => $version,
            "android_key_id" => $platform == "android" ? $signing_id : 0,
            "ios_key_id"     => $platform == "ios" ? $signing_id : 0,
            "publish"        => $publish,
            "format"         => $format,
            "build_id"       => ""
        ]);

        return $this->respond([
            "uid"      => $uid,
            "platform" => $platform,
            "status"   => 0,
            "version"  => $version,
            "publish"  => $publish,
            "created"  => date('d-m-Y H:i'),
            "format"   => $platform == "ios" ? "ipa" : $format,
            "fail"     => false,
            "message"  => "",
            "static"   => "",
        ], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Check app signing
     * @param string $uid
     * @param string $platform
     * @return int|null
     */
    private function sign_validation(string $uid, string $platform, string $appId): ?int
    {
        if ($platform == "android") {
            $android_signs = new SignsAndroidModel();

            $sign = $android_signs
                ->where(["app_id" => $appId, "uid" => $uid])
                ->select("id")
                ->first();

            if (!$sign) {
                return null;
            }

            return $sign["id"];
        } else {
            $ios_signs = new SignsIosModel();

            $sign = $ios_signs
                ->where(["app_id" => $appId, "uid" => $uid])
                ->select("id")
                ->first();

            if (!$sign) {
                return null;
            }

            return $sign["id"];
        }
    }

    /**
     * Get validation rules for create new build
     * @return array
     */
    private function create_validation_type(): array
    {
        return [
            "version"         => [
                "label" => lang("Fields.field_69"),
                "rules" => "required|min_length[3]|max_length[8]"
            ],
            "platform"        => [
                "label" => lang("Fields.field_70"),
                "rules" => "required|in_list[android,ios]"
            ],
            "format"          => [
                "label" => lang("Fields.field_74"),
                "rules" => "required|in_list[apk,aab]"
            ],
            "android_key_id"  => [
                "label" => lang("Fields.field_71"),
                "rules" => "max_length[100]"
            ],
            "ios_key_id"      => [
                "label" => lang("Fields.field_72"),
                "rules" => "max_length[100]"
            ],
            "publish"         => [
                "label" => lang("Fields.field_75"),
                "rules" => "required|in_list[0,1]"
            ],
        ];
    }
}