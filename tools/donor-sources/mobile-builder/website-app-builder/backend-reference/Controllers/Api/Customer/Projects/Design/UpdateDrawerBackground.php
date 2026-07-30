<?php namespace App\Controllers\Api\Customer\Projects\Design;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use App\Models\DrawersModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class UpdateDrawerBackground extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Update drawer background
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

        $image = $this->request->getFile('background');
        $name = $image->getRandomName();
        $image->move(ROOTPATH.'public_html/upload/drawer/'.$app['uid'], $name);

        $drawers->update($header["id"], [
            "background" => $name
        ]);
        return $this->respond(["uri"  => base_url('upload/drawer/'.$app['uid'].'/'.$name)], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for upload background image
     * @return array
     */
    private function update_validation_type(): array
    {
        return [
            'background' => [
                'label' => lang("Fields.field_29"),
                'rules' => 'uploaded[background]|max_size[background,500]|ext_in[background,png,jpg]|max_dims[background,1920,1920]'
            ],
        ];
    }
}