<?php
namespace nst\member;

use eye4tech\worm\db\saStates;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\controller;
use sacore\application\Event;
use sacore\application\jsonView;
use sacore\application\modelResult;
use sacore\application\modRequest;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\ValidateException;
use sa\files\FileUploadException;
use sa\files\ImageException;
use sa\files\saFile;
use sa\files\saImages;
use sa\member\auth;
use sacore\utilities\debug;
use sacore\utilities\doctrineUtils;
use sacore\utilities\notification;
use sacore\utilities\url;
use sa\member\memberProfileController;


/**
 * @IOC_NAME="memberProfileController"
 */
class NstMemberProfileController extends memberProfileController
{

    /** @var saMemberRepository $saMemberRepo */
    private $saMemberRepo;
    /** @var saMember $saMember */
    private $saMember;

    public function __construct()
    {
        parent::__construct();

        $this->saMember = ioc::staticResolve('saMember');
        $this->saMemberRepo = app::$entityManager->getRepository($this->saMember);
    }


    public function editMember($request): View
    {
        // exit("NSTMEMBERprofile");
        $member = modRequest::request('auth.member', false);

        $view = new View('provider_profile', $this->viewLocation(), false);
        $view->data['postRoute'] = app::get()->getRouter()->generate('member_profile_save');
        // $view->data['avatar'] = $member->getAvatar();

        if($member){
            $mData = $member->toArray();
            $view->data = array_merge($view->data, $mData);
        }

        if ($request->request->all()) {
            $view->data = array_merge($view->data, $request->request->all());
        }

        return $view;
    }


    public function viewUsers(): View
    {
        $view = new View('member_users_list');

        $view->data['member_id'] = auth::getAuthMemberId();
        $view->data['current_user_id'] = auth::getAuthUserId();
        $view->data['current_user_type'] = auth::getAuthUser()->getUserType();
        return $view;
    }

    public static function getUsersList($data): array
    {
        $memberService = new NstMemberService();
        return $memberService->getUsersList($data);
    }

    public static function saveUserData($data): array
    {
        $memberService = new NstMemberService();
        return $memberService->saveUserData($data);
    }

    public static function deleteUser($data): array
    {
        $memberService = new NstMemberService();
        return $memberService->deleteUser($data);
    }
}