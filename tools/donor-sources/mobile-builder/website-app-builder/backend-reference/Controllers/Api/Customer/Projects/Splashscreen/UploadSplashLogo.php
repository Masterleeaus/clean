<?php namespace App\Controllers\Api\Customer\Projects\Splashscreen;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\SplashScreensModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class UploadSplashLogo extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Upload splashscreen logo
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

        $image = $this->request->getFile('logo');
        $name = $image->getRandomName();
        $image->move(ROOTPATH.'public_html/upload/logos/'.$app['uid'], $name);

        $splash->update($screen["id"], [
            "logo" => $name
        ]);

        return $this->respond(["uri"  => base_url('upload/logos/'.$app['uid'].'/'.$name)], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for upload logo image
     * @return array
     */
    private function upload_validation_type(): array
    {
        return [
            'logo' => [
                'label' => lang("Fields.field_30"),
                'rules' => 'uploaded[logo]|max_size[logo,500]|ext_in[logo,png]|max_dims[logo,1200,1200]'
            ],
        ];
    }
}