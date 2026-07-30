<?php namespace App\Controllers\Api\Customer\Projects\Splashscreen;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\SplashScreensModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class UploadSplashBackground extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Upload splashscreen background
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function index(): ResponseInterface
    {
        if (!$this->validate($this->upload_validation_type())) {
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

        $splash = new SplashScreensModel();

        $screen = $splash
            ->where("app_id", $app["id"])
            ->select("id")
            ->first();

        if ( !is_dir( ROOTPATH.'public_html/upload/splash/'.$app['uid'] ) ) {
            mkdir(ROOTPATH.'public_html/upload/splash/'.$app['uid'], 0777, true);
        }

        $image = $this->request->getFile('screen');
        $name = $image->getRandomName();
        $image->move(ROOTPATH.'public_html/upload/splash/'.$app['uid'], $name);

        $splash->update($screen["id"], [
            "image" => $name
        ]);

        return $this->respond(["uri"  => base_url('upload/splash/'.$app['uid'].'/'.$name)], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for upload splashscreen image background
     * @return array
     */
    private function upload_validation_type(): array
    {
        return [
            'screen' => [
                'label' => lang("Fields.field_29"),
                'rules' => 'uploaded[screen]|max_size[screen,500]|ext_in[screen,png]|max_dims[screen,2436,2436]'
            ]
        ];
    }
}