<?php namespace App\Controllers\Api\Customer\Projects\Builds;

use App\Controllers\PrivateController;
use App\Libraries\CodeMagic;
use App\Models\BuildsModel;
use CodeIgniter\HTTP\ResponseInterface;

class DownloadArtefact extends PrivateController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Get link download artefact
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $uid = esc($this->request->getGet("uid"));

        $builds = new BuildsModel();

        $build = $builds
            ->where("uid", $uid)
            ->where("status", 1)
            ->where("fail", 0)
            ->first();

        if (!$build) {
            return $this->respond(["message" => lang("Message.message_46")], 404);
        }

        $codemagic = new CodeMagic();

        $link = $codemagic->signing_download_link($build["static"]);

        if (!$link["event"]) {
            return $this->respond(["message" => $link["message"]], 400);
        }

        return $this->respond(["url" => $link["url"]], 200);
    }

}