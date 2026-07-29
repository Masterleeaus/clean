<?php
namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\CodeMagic;
use App\Libraries\GitHub;
use App\Libraries\OneSignal;
use App\Libraries\UpdatesManager;
use App\Models\AppsModel;
use App\Models\BuildsModel;
use App\Models\LocalsModel;
use App\Models\SignsAndroidModel;
use App\Models\SignsIosModel;
use App\Models\SplashScreensModel;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class CreatePackage extends BaseController
{
    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    /**
     * Create app package for build
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function index(): ResponseInterface
    {
        // Check for updates first
        $updatesManager = new UpdatesManager();
        $checkInstallation = $updatesManager->check_installation();
        
        if (!$checkInstallation['event']) {
            return $this->respond([
                'status' => 'fail',
                'message' => $checkInstallation["message"]
            ], 200);
        }

        $builds = new BuildsModel();

        $build_list = $builds
            ->where("status", 0)
            ->where("build_id", "")
            ->findAll();

        if (!$build_list) {
            return $this->respond(["status" => "not found"], 200);
        }

        foreach ($build_list as $build) {
            $apps = new AppsModel();

            $app = $apps
                ->where("id", $build["app_id"])
                ->select("id,uid,app_id,name")
                ->first();

            // Check and update the target branch
            $checkBranch = $updatesManager->check_prepare_branch($app["uid"]);
            if (!$checkBranch['event']) {
                $builds->update($build["id"], [
                    "status"  => 1,
                    "fail"    => 1,
                    "message" => $checkBranch["message"]
                ]);
                return $this->respond(["status" => "fail", "message" => $checkBranch["message"]], 400);
            }

            // create config.dart file
            $config_file = $this->upload_config($build["app_id"], $build["version"], $build["platform"]);
            if (!$config_file["event"]) {
                $builds->update($build["id"], [
                    "status"  => 1,
                    "fail"    => 1,
                    "message" => $config_file["message"]
                ]);
                return $this->respond(["status" => "fail", "message" => $config_file], 400);
            }

            // create pubspec.yaml file
            $pubspec_file = $this->upload_pubspec($build["version"], $app["uid"]);
            if (!$pubspec_file["event"]) {
                $builds->update($build["id"], [
                    "status"  => 1,
                    "fail"    => 1,
                    "message" => $pubspec_file["message"]
                ]);
                return $this->respond(["status" => "fail"], 400);
            }

            if ($build["platform"] == "android") {
                // create build gradle file
                $gradle_file = $this->upload_gradle($app["app_id"], $build["android_key_id"], $app["uid"]);
                if (!$gradle_file["event"]) {
                    $builds->update($build["id"], [
                        "status"  => 1,
                        "fail"    => 1,
                        "message" => $gradle_file["message"]
                    ]);
                    return $this->respond(["status" => "fail"], 400);
                }

                // create android manifest
                $manifest_file = $this->upload_manifest($app["id"]);
                if (!$manifest_file["event"]) {
                    $builds->update($build["id"], [
                        "status"  => 1,
                        "fail"    => 1,
                        "message" => $manifest_file["message"]
                    ]);
                    return $this->respond(["status" => "fail"], 400);
                }

                // create codemagic yaml
                $cm_yaml = $this->upload_codemagic_android(
                    $build["uid"],
                    $app["app_id"],
                    $app["uid"],
                    $build["version"],
                    $build["format"]
                );
                if (!$cm_yaml["event"]) {
                    $builds->update($build["id"], [
                        "status"  => 1,
                        "fail"    => 1,
                        "message" => $cm_yaml["message"]
                    ]);
                    return $this->respond(["status" => "fail"], 400);
                }

                // upload icons
                $icons_res = $this->upload_android_icons($app["uid"]);
                if (!$icons_res["event"]) {
                    $builds->update($build["id"], [
                        "status"  => 1,
                        "fail"    => 1,
                        "message" => $cm_yaml["message"]
                    ]);
                    return $this->respond(["status" => "fail"], 200);
                }

            } else {
                // create plist and podfile
                $plist = $this->upload_plist_pod($app["id"]);
                if (!$plist["event"]) {
                    $builds->update($build["id"], [
                        "status"  => 1,
                        "fail"    => 1,
                        "message" => $plist["message"]
                    ]);
                    return $this->respond(["status" => "fail"], 200);
                }

                // create codemagic yaml
                $cm_file = $this->upload_codemagic_ios(
                    $app["app_id"],
                    $app["uid"],
                    $build["ios_key_id"],
                    $build["uid"],
                    (int) $build["publish"],
                    $build["version"]
                );
                if (!$cm_file["event"]) {
                    $builds->update($build["id"], [
                        "status"  => 1,
                        "fail"    => 1,
                        "message" => $cm_file["message"]
                    ]);
                    return $this->respond(["status" => "fail"], 200);
                }

                // create pbxproj
                $pbx_file = $this->upload_pbxprog_ios($app["app_id"], $app["uid"]);
                if (!$pbx_file["event"]) {
                    $builds->update($build["id"], [
                        "status"  => 1,
                        "fail"    => 1,
                        "message" => $pbx_file["message"]
                    ]);
                    return $this->respond(["status" => "fail"], 200);
                }

                // upload icons
                $ios_icons = $this->upload_ios_icons($app["uid"]);
                if (!$ios_icons["event"]) {
                    $builds->update($build["id"], [
                        "status"  => 1,
                        "fail"    => 1,
                        "message" => $ios_icons["message"]
                    ]);
                    return $this->respond(["status" => "fail"], 200);
                }
            }

            $codemagic = new CodeMagic();

            $cm_res = $codemagic->create_build($app["uid"], $build["platform"]);
            if (!$cm_res["event"]) {
                return $this->respond(["status" => "fail"], 200);
            }

            $apps->update($app["id"], [
                "status" => 0
            ]);

            $builds->update($build["id"], [
                "build_id" => $cm_res["id"]
            ]);
        }

        return $this->respond(["status" => "ok"], 200);
    }

    /**************************************************************************************
     * PRIVATE FUNCTIONS
     **************************************************************************************/

    /**
     * Upload codemagic yaml for ios
     * @param string $packageId
     * @param string $appUid
     * @param int $sign_id
     * @param string $buildUid
     * @param int $isPublish
     * @param string $version
     * @return array
     */
    private function upload_codemagic_ios(string $packageId, string $appUid, int $sign_id, string $buildUid, int $isPublish, string $version): array
    {
        $yamlFile = file_get_contents(!$isPublish
            ? WRITEPATH."bundle/workflow/ios.yaml"
            : WRITEPATH."bundle/workflow/ios_publish.yaml"
        );

        $ios_signs = new SignsIosModel();

        $sign = $ios_signs
            ->where("id", $sign_id)
            ->first();

        $yamlFileVariables = [
            '{WORKFLOW_NAME}',
            '{APP_ID}',
            '{APP_STORE_CONNECT_ISSUER_ID}',
            '{APP_STORE_CONNECT_KEY_IDENTIFIER}',
            '{APP_STORE_CONNECT_PRIVATE_KEY}',
            '{NOTICE_URL}',
            '{CERTIFICATE_PRIVATE_KEY}',
            '{APP_VERSION}'
        ];

        $yamlCodeVariable = [
            !$isPublish ? "Flangapp PRO iOS without publication" : "Flangapp PRO iOS with publication",
            $packageId,
            $sign["issuer_id"],
            $sign["key_id"],
            $this->build_private_key(file_get_contents(WRITEPATH.'storage/ios/'.$sign["file"])),
            base_url("public/observe/notice?uid=".$buildUid),
            $this->build_private_key(file_get_contents(WRITEPATH.'storage/pub/'.$sign["pub_file"])),
            $version
        ];

        $content = str_replace($yamlFileVariables, $yamlCodeVariable, $yamlFile);

        $git = new GitHub();

        return $git->create_commit($appUid, "codemagic.yaml", $content);
    }

    /**
     * Upload pbxprog file for ios
     * @param string $packageId
     * @param string $appUid
     * @return array
     */
    private function upload_pbxprog_ios(string $packageId, string $appUid): array
    {
        $pbxprojFile = file_get_contents(WRITEPATH.'bundle/ios/project.pbxproj');

        $pbxprojFileVariables = [
            '{APP_ID}'
        ];

        $pbxprojCodeVariable = [
            $packageId
        ];

        $content = str_replace($pbxprojFileVariables, $pbxprojCodeVariable, $pbxprojFile);

        $git = new GitHub();

        return $git->create_commit($appUid, "ios/Runner.xcodeproj/project.pbxproj", $content);
    }

    /**
     * Upload info plist and podfile
     * @param int $appId
     * @return array
     */
    private function upload_plist_pod(int $appId): array
    {
        $projects = new AppsModel();

        $app = $projects
            ->where("id", $appId)
            ->where("deleted_at", 0)
            ->first();

        $orientation = "";
        if ($app["orientation"] == 0) {
            $orientation .= "<string>UIInterfaceOrientationPortrait</string>\n";
            $orientation .= "<string>UIInterfaceOrientationPortraitUpsideDown</string>\n";
            $orientation .= "<string>UIInterfaceOrientationLandscapeLeft</string>\n";
            $orientation .= "<string>UIInterfaceOrientationLandscapeRight</string>\n";
        }
        if ($app["orientation"] == 1) {
            $orientation .= "<string>UIInterfaceOrientationPortrait</string>\n";
        }
        if ($app["orientation"] == 2) {
            $orientation .= "<string>UIInterfaceOrientationLandscapeLeft</string>\n";
            $orientation .= "<string>UIInterfaceOrientationLandscapeRight</string>\n";
        }

        $gps_permissions = "";
        $gps_pod = "";
        if ($app["gps"]) {
            $gps_permissions .= "<key>NSLocationAlwaysAndWhenInUseUsageDescription</key>\n";
            $gps_permissions .= "<string>".$app["gps_description"]."</string>\n";
            $gps_permissions .= "<key>NSLocationWhenInUseUsageDescription</key>\n";
            $gps_permissions .= "<string>".$app["gps_description"]."</string>\n";
            $gps_pod = "'PERMISSION_LOCATION=1',";
        }

        $camera_permissions = "";
        $camera_pod = "";
        if ($app["camera"]) {
            $camera_permissions .= "<key>NSCameraUsageDescription</key>\n";
            $camera_permissions .= "<string>".$app["camera_description"]."</string>\n";
            $camera_pod = "'PERMISSION_CAMERA=1',";
        }

        $microphone_permissions = "";
        $microphone_pod = "";
        if ($app["microphone"]) {
            $microphone_permissions .= "<key>NSMicrophoneUsageDescription</key>\n";
            $microphone_permissions .= "<string>".$app["microphone_description"]."</string>\n";
            $microphone_pod = "'PERMISSION_MICROPHONE=1',";
        }

        $plistFile = file_get_contents(WRITEPATH.'bundle/ios/Info.plist');

        $plistFileVariables = [
            '{APP_NAME}',
            '{APP_ORIENTATION}',
            '{APP_LANGUAGE}',
            '{LOCATION}',
            '{CAMERA}',
            '{MICROPHONE}',
        ];

        $plistCodeVariable = [
            $app["name"],
            $orientation,
            strtolower($app["language"]),
            $gps_permissions,
            $camera_permissions,
            $microphone_permissions
        ];

        $content = str_replace($plistFileVariables, $plistCodeVariable, $plistFile);

        $git = new GitHub();

        $res_plist = $git->create_commit($app["uid"], "ios/Runner/Info.plist", $content);

        if (!$res_plist["event"]) {
            return $res_plist;
        }

        $podFile = file_get_contents(WRITEPATH.'bundle/ios/Podfile');

        $podFileVariables = [
            '{LOCATION}',
            '{CAMERA}',
            '{MICROPHONE}',
        ];

        $podCodeVariable = [
            $gps_pod,
            $camera_pod,
            $microphone_pod
        ];

        $pod_content = str_replace($podFileVariables, $podCodeVariable, $podFile);

        return $git->create_commit($app["uid"], "ios/Podfile", $pod_content);
    }

    /**
     * Upload android icons
     * @param string $appUid
     * @return array
     */
    private function upload_android_icons(string $appUid): array
    {
        helper('filesystem');

        $isUploaded = is_dir(ROOTPATH.'public_html/upload/icons/'.$appUid);
        if (!$isUploaded) {
            return ["event" => true];
        }

        $icons = [];
        $icons[] = [
            "name" => "mipmap-hdpi",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/android/hdpi_72.png'
        ];
        $icons[] = [
            "name" => "mipmap-mdpi",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/android/mdpi_48.png'
        ];
        $icons[] = [
            "name" => "mipmap-xhdpi",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/android/xhdpi_96.png'
        ];
        $icons[] = [
            "name" => "mipmap-xxhdpi",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/android/xxhdpi_144.png'
        ];
        $icons[] = [
            "name" => "mipmap-xxxhdpi",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/android/xxxhdpi_192.png'
        ];

        $git = new GitHub();

        foreach ($icons as $icon) {
            $content = file_get_contents($icon["path"]);
            $res = $git->create_commit($appUid, 'android/app/src/main/res/'.$icon["name"].'/ic_launcher.png', $content);
            if (!$res) {
                return $res;
            }
        }

        return ["event" => true];
    }

    /**
     * Upload ios icons
     * @param string $appUid
     * @return array
     */
    private function upload_ios_icons(string $appUid): array
    {
        helper('filesystem');

        $isUploaded = is_dir(ROOTPATH.'public_html/upload/icons/'.$appUid);
        if (!$isUploaded) {
            return ["event" => true];
        }

        $icons = [];
        $icons[] = [
            "name" => "20.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/20.png'
        ];
        $icons[] = [
            "name" => "29.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/29.png'
        ];
        $icons[] = [
            "name" => "40.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/40.png'
        ];
        $icons[] = [
            "name" => "50.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/50.png'
        ];
        $icons[] = [
            "name" => "57.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/57.png'
        ];
        $icons[] = [
            "name" => "58.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/58.png'
        ];
        $icons[] = [
            "name" => "60.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/60.png'
        ];
        $icons[] = [
            "name" => "72.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/72.png'
        ];
        $icons[] = [
            "name" => "76.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/76.png'
        ];
        $icons[] = [
            "name" => "80.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/80.png'
        ];
        $icons[] = [
            "name" => "87.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/87.png'
        ];
        $icons[] = [
            "name" => "100.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/100.png'
        ];
        $icons[] = [
            "name" => "114.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/114.png'
        ];
        $icons[] = [
            "name" => "120.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/120.png'
        ];
        $icons[] = [
            "name" => "144.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/144.png'
        ];
        $icons[] = [
            "name" => "152.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/152.png'
        ];
        $icons[] = [
            "name" => "167.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/167.png'
        ];
        $icons[] = [
            "name" => "180.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/180.png'
        ];
        $icons[] = [
            "name" => "1024.png",
            "path" => ROOTPATH.'public_html/upload/icons/'.$appUid.'/ios/1024.png'
        ];

        $git = new GitHub();

        foreach ($icons as $icon) {
            $content = file_get_contents($icon["path"]);
            $res = $git->create_commit($appUid, "ios/Runner/Assets.xcassets/AppIcon.appiconset/".$icon["name"], $content);
            if (!$res) {
                return $res;
            }
        }

        return ["event" => true];
    }

    /**
     * Upload codemagic android yaml
     * @param string $buildUid
     * @param string $packageId
     * @param string $appUid
     * @param string $version
     * @param string $type
     * @return array
     */
    private function upload_codemagic_android(string $buildUid, string $packageId, string $appUid, string $version, string $type): array
    {
        $yamlFile = file_get_contents($type === "apk"
            ? WRITEPATH."bundle/workflow/android_apk.yaml"
            : WRITEPATH."bundle/workflow/android_aab.yaml"
        );

        $yamlFileVariables = [
            '{WORKFLOW_NAME}',
            '{APP_ID}',
            '{NOTICE_URL}',
            '{VERSION}'
        ];

        $yamlCodeVariable = [
            "Flangapp PRO Android ".$type,
            $packageId,
            base_url("public/observe/notice?uid=".$buildUid),
            $version
        ];

        // replace
        $content = str_replace($yamlFileVariables, $yamlCodeVariable, $yamlFile);

        $git = new GitHub();

        return $git->create_commit($appUid, "codemagic.yaml", $content);
    }

    /**
     * Upload android manifest
     * @param int $appId
     * @return array
     */
    private function upload_manifest(int $appId): array
    {
        $manifestFile = file_get_contents(WRITEPATH.'bundle/android/AndroidManifest.xml');

        $projects = new AppsModel();

        $app = $projects
            ->where("id", $appId)
            ->select("name,orientation,gps,camera,microphone,uid")
            ->first();

        $gps_permissions = "";
        if ($app["gps"]) {
            $gps_permissions .= '<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />'."\n";
            $gps_permissions .= ' <uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />'."\n";
        }

        $camera_permissions = "";
        if ($app["camera"]) {
            $camera_permissions .= '<uses-permission android:name="android.permission.CAMERA" />'."\n";;
            $camera_permissions .= '<uses-permission android:name="android.permission.VIDEO_CAPTURE" />'."\n";;
            $camera_permissions .= '<uses-feature android:name="android.hardware.camera" />'."\n";;
            $camera_permissions .= '<uses-feature android:name="android.hardware.camera.autofocus" />'."\n";;
        }

        $microphone_permissions = "";
        if ($app["microphone"]) {
            $microphone_permissions .= '<uses-permission android:name="android.permission.RECORD_AUDIO" />'."\n";;
            $microphone_permissions .= '<uses-permission android:name="android.permission.MODIFY_AUDIO_SETTINGS" />'."\n";;
            $microphone_permissions .= '<uses-permission android:name="android.permission.AUDIO_CAPTURE" />'."\n";;
        }

        $orientation = "unspecified";
        if ($app["orientation"] == 1) {
            $orientation = "portrait";
        }
        if ($app["orientation"] == 2) {
            $orientation = "landscape";
        }

        $configFileVariables = [
            '{APP_NAME}',
            '{APP_ORIENTATION}',
            '{GEO_PERMISSIONS}',
            '{MICROPHONE_PERMISSIONS}',
            '{CAMERA_PERMISSIONS}',
        ];
        $configCodeVariable = [
            $app["name"],
            $orientation,
            $gps_permissions,
            $microphone_permissions,
            $camera_permissions
        ];

        $content = str_replace($configFileVariables, $configCodeVariable, $manifestFile);

        $git = new GitHub();

        return $git->create_commit($app["uid"], "android/app/src/main/AndroidManifest.xml", $content);
    }

    /**
     * Upload build.gradle file and keystore
     * @param string $appPackageId
     * @param int $signId
     * @param string $appUid
     * @return array
     */
    private function upload_gradle(string $appPackageId, int $signId, string $appUid): array
    {
        $encrypter = Services::encrypter();

        $android_signs = new SignsAndroidModel();

        $sign = $android_signs
            ->where("id", $signId)
            ->first();

        if (!$sign) {
            return ["event" => false, "message" => lang("Message.message_40")];
        }

        $gradleFile = file_get_contents(WRITEPATH.'bundle/android/build.gradle.kts');

        $configFileVariables = [
            '{APP_ID}',
            '{KEYSTORE_PASSWORD}',
            '{KEY_PASSWORD}',
            '{KEY_ALIAS}',
        ];
        $configCodeVariable = [
            $appPackageId,
            $encrypter->decrypt($sign["keystore_password"]),
            $encrypter->decrypt($sign["key_password"]),
            $sign["alias"],
        ];

        $content = str_replace($configFileVariables, $configCodeVariable, $gradleFile);

        $git = new GitHub();

        $res = $git->create_commit($appUid, "android/app/build.gradle.kts", $content);

        if ($res["event"]) {
            $sign_content = file_get_contents(WRITEPATH.'storage/android/'.$sign["file"]);
            return $git->create_commit($appUid, "android/app/sign/key.jks", $sign_content);
        }

        return $res;
    }

    /**
     * Upload flutter pubspec
     * @param string $version
     * @param string $appUid
     * @return array
     */
    private function upload_pubspec(string $version, string $appUid): array
    {
        // pubspec example file
        $pubFile = file_get_contents(WRITEPATH.'bundle/pubspec.yaml');
        $configFileVariables = [
            '{APP_VERSION}',
            '{GENERATE_DATE}'
        ];
        $configCodeVariable = [
            $version,
            date('d-m-Y H:i'),
        ];
        // replace
        $content = str_replace($configFileVariables, $configCodeVariable, $pubFile);

        $git = new GitHub();

        return $git->create_commit($appUid, "pubspec.yaml", $content);
    }

    /**
     * Upload app config dart and static
     * @param int $appId
     * @param string $version
     * @param string $platform
     * @return array
     * @throws ReflectionException
     */
    private function upload_config(int $appId, string $version, string $platform): array
    {
        $projects = new AppsModel();

        $app = $projects
            ->where("id", $appId)
            ->where("deleted_at", 0)
            ->first();

        if (!$app) {
            return ["event" => false, "message" => lang("Message.message_14")];
        }

        $onesignal_id = "";

        if ($platform == "android") {

            if ($app["one_signal_id"]) {
                $onesignal_id = $app["one_signal_id"];
            } else {
                $onesignal = new OneSignal();
                $resOs = $onesignal->create_onesignal_app($app["uid"]);
                if (!$resOs["event"]) {
                    return $resOs;
                }
                $onesignal_id = $resOs["app_id"];
                $projects->update($app["id"], [
                    "one_signal_id" => $resOs["app_id"]
                ]);
            }
        }

        $git = new GitHub();

        $splash_screens = new SplashScreensModel();

        $splash = $splash_screens
            ->where("app_id", $app["id"])
            ->first();

        if ($splash["image"]) {
            $splash_content = file_get_contents(ROOTPATH.'public_html/upload/splash/'.$app['uid'].'/'.$splash["image"]);
            $res = $git->create_commit($app["uid"], "assets/splash_screen.png", $splash_content);
            if (!$res["event"]) {
                return $res;
            }
        }

        if ($splash["logo"]) {
            $splash_content = file_get_contents(ROOTPATH.'public_html/upload/logos/'.$app['uid'].'/'.$splash["logo"]);
            $res = $git->create_commit($app["uid"], "assets/splash_logo.png", $splash_content);
            if (!$res["event"]) {
                return $res;
            }
        }

        $locals = new LocalsModel();

        $local = $locals
            ->where("app_id", $app["id"])
            ->first();

        if ($local["offline_image"]) {
            $off_content = file_get_contents(ROOTPATH.'public_html/upload/info/'.$app['uid'].'/'.$local["offline_image"]);
            $res = $git->create_commit($app["uid"], "assets/dino.png", $off_content);
            if (!$res["event"]) {
                return $res;
            }
        }

        $configFile = file_get_contents(WRITEPATH.'bundle/config.dart');

        $configFileVariables = [
            "{GENERATE_DATE}",
            "{APP_UID}",
            "{API_SERVER}",
            "{VERSION}",
            "{ONESIGNAL_PUSH_ID}",
            "{SPLASH_BACKGROUND_COLOR}",
            "{SPLASH_TEXT_COLOR}",
            "{SPLASH_IS_BACKGROUND_IMAGE}",
            "{SPLASH_BACKGROUND_IMAGE}",
            "{SPLASH_TAGLINE}",
            "{SPLASH_DELAY}",
            "{SPLASH_LOGO_IMAGE}",
            "{SPLASH_IS_DISPLAY_LOGO}",
            "{OFFLINE_ERROR_MESSAGE}",
            "{OFFLINE_IMAGE}",
            "{SUBSCRIBE_ERROR_TITLE}",
            "{SUBSCRIBE_ERROR_MESSAGE}"
        ];
        $configCodeVariable = [
            date('d-m-Y H:i'),
            $app["uid"],
            site_url(),
            $version,
            $onesignal_id,
            $splash["color"],
            !$splash["theme"] ? "#ffffff" : "#000000",
            !$splash["background"] ? "false" : "true",
            "splash_screen.png",
            $splash["tagline"],
            $splash["delay"],
            "splash_logo.png",
            !$splash["use_logo"] ? "false" : "true",
            $local["string_5"],
            "dino.png",
            lang("Message.message_76"),
            lang("Message.message_77"),
        ];
        $content = str_replace($configFileVariables, $configCodeVariable, $configFile);

        return $git->create_commit($app["uid"], "lib/config/config.dart", $content);
    }

    /**
     * Get formatted ios private key string
     * @param string $key
     * @return string
     */
    public function build_private_key(string $key): string
    {
        $string = "";
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $key) as $line){
            $string .= "         $line\n";
        }
        return $string;
    }
}
