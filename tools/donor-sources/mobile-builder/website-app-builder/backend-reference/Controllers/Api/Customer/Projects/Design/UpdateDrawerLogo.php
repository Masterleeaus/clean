<?php namespace App\Controllers\Api\Customer\Projects\Design;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\DrawersModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class UpdateDrawerLogo extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Update drawer settings
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
            ->select("id,uid")
            ->first();

        if (!$app) {
            return $this->respond(["message" => lang("Message.message_14")], 404);
        }

        $drawers = new DrawersModel();

        $header = $drawers
            ->where("app_id", $app["id"])
            ->select("id")
            ->first();

        if ( !is_dir( ROOTPATH.'public_html/upload/drawer/'.$app['uid'] ) ) {
            mkdir(ROOTPATH.'public_html/upload/drawer/'.$app['uid'], 0777, true);
        }

        $image = $this->request->getFile('logo');
        $name = $image->getRandomName();
        $image->move(ROOTPATH.'public_html/upload/drawer/'.$app['uid'], $name);

        $drawers->update($header["id"], [
            "logo" => $name
        ]);

        return $this->respond(["uri"  => base_url('upload/drawer/'.$app['uid'].'/'.$name)], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for upload logo
     * @return array
     */
    private function update_validation_type(): array
    {
        return [
            'logo' => [
                'label' => lang("Fields.field_30"),
                'rules' => 'uploaded[logo]|max_size[logo,500]|ext_in[logo,png,jpg]|max_dims[logo,1200,1200]'
            ],
        ];
    }
}