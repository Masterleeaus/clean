<?php namespace App\Controllers\Api\Customer\Projects\Icon;

use App\Controllers\PrivateController;
use App\Models\AppsModel;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\ResponseInterface;

class UploadIcon extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Upload launch icon
     * @return ResponseInterface
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

        $image = $this->request->getFile('icon');
        list($width, $height) = getimagesize($image->getTempName());
        if ($width != 1024 || $height != 1024) {
            return $this->respond(["message" => lang("Message.message_19")], 400);
        }

        $this->clean_folder($app['uid']);

        $image->move(ROOTPATH.'public_html/upload/icons/'.$app['uid'], "original.png");

        $android_sizes = $this->android_icons_rules();
        foreach ($android_sizes as $key => $item) {
            $name = $key."_".$item["width"].".png";
            $this->createAssetApp(
                ROOTPATH.'public_html/upload/icons/'.$app['uid']."/original.png",
                $item["width"],
                ROOTPATH.'public_html/upload/icons/'.$app['uid']."/android/".$name,
                ROOTPATH.'public_html/upload/icons/'.$app['uid']."/android"
            );
        }

        $ios_sizes = $this->ios_icons_rules();
        foreach ($ios_sizes as $key => $item) {
            $name = $key.".png";
            $this->createAssetApp(
                ROOTPATH.'public_html/upload/icons/'.$app['uid']."/original.png",
                $item["width"],
                ROOTPATH.'public_html/upload/icons/'.$app['uid']."/ios/".$name,
                ROOTPATH.'public_html/upload/icons/'.$app['uid']."/ios"
            );
        }

        return $this->respond([
            "icons"   => [
                "android" => directory_map(ROOTPATH.'public_html/upload/icons/'.$app['uid']."/android", FALSE, TRUE),
                "ios"     => directory_map(ROOTPATH.'public_html/upload/icons/'.$app['uid']."/ios", FALSE, TRUE),
                "upload"  => true
            ],
            "url"     => base_url("upload/icons/".$app["uid"]),
            "unix"    => strtotime(date('m/d/Y h:i:s a', time())),
        ], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Get validation rules for upload icon image
     * @return array
     */
    private function upload_validation_type(): array
    {
        return [
            'icon' => [
                'label' => lang("Fields.field_22"),
                'rules' => 'uploaded[icon]|max_size[icon,1200]|ext_in[icon,png]|max_dims[icon,1024,1024]'
            ],
        ];
    }

    /**
     * Remove all files in icons folder
     * @param string $uid
     * @return void
     */
    private function clean_folder(string $uid): void
    {
        helper('filesystem');
        delete_files(ROOTPATH.'public_html/upload/icons/'.$uid, true);
    }

    /**
     * Create image assets
     * @param string $original
     * @param int $size
     * @param string $final
     * @param string $new_path
     * @return void
     */
    private function createAssetApp(string $original, int $size, string $final, string $new_path): void
    {
        if (!is_dir($new_path)) {
            mkdir($new_path, 0777, true);
        }
        Services::image()
            ->withFile($original)
            ->fit($size, $size, 'center')
            ->save($final, 100);
    }

    /**
     * Get sizes array for android icon
     * @return array
     */
    private function android_icons_rules(): array
    {
        return [
            "mdpi" => [
                "width"  => 48,
                "height" => 48
            ],
            "hdpi" => [
                "width"  => 72,
                "height" => 72
            ],
            "xhdpi" => [
                "width"  => 96,
                "height" => 96
            ],
            "xxhdpi" => [
                "width"  => 144,
                "height" => 144
            ],
            "xxxhdpi" => [
                "width"  => 192,
                "height" => 192
            ],
        ];
    }

    /**
     * Get sizes array for iOS icon
     * @return array
     */
    private function ios_icons_rules(): array
    {
        return [
            "20" => [
                "width"  => 20,
                "height" => 20
            ],
            "29" => [
                "width"  => 29,
                "height" => 29
            ],
            "40" => [
                "width"  => 40,
                "height" => 40
            ],
            "50" => [
                "width"  => 50,
                "height" => 50
            ],
            "57" => [
                "width"  => 57,
                "height" => 57
            ],
            "58" => [
                "width"  => 58,
                "height" => 58
            ],
            "60" => [
                "width"  => 60,
                "height" => 60
            ],
            "72" => [
                "width"  => 72,
                "height" => 72
            ],
            "76" => [
                "width"  => 76,
                "height" => 76
            ],
            "80" => [
                "width"  => 80,
                "height" => 80
            ],
            "87" => [
                "width"  => 87,
                "height" => 87
            ],
            "100" => [
                "width"  => 100,
                "height" => 100
            ],
            "114" => [
                "width"  => 114,
                "height" => 114
            ],
            "120" => [
                "width"  => 120,
                "height" => 120
            ],
            "144" => [
                "width"  => 144,
                "height" => 144
            ],
            "152" => [
                "width"  => 152,
                "height" => 152
            ],
            "167" => [
                "width"  => 167,
                "height" => 167
            ],
            "180" => [
                "width"  => 180,
                "height" => 180
            ],
            "1024" => [
                "width"  => 1024,
                "height" => 1024
            ],
        ];
    }
}