<?php

namespace nst\member;

use http\Client\Curl\User;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\Request;
use sa\member\auth;
use sa\member\saMemberEmail;
use sa\member\saMemberPhone;
use sa\member\saMemberUsers;

class NstMemberService
{
    public static function getUsersList($data) {
        $response = ['success' => false];

        /** @var NstMember $member */
        $member = auth::getAuthMember();


        /** @var NstMemberUsers $user */
        foreach($member->getUsers() as $user) {
            $response['users'][] = [
                'id' => $user->getId(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail() ? $user->getEmail()->getEmail(): '',
                'phone' => count($user->getPhones()) ? $user->getPhones()[0]->getPhone() : '',
                'last_login' => $user->getLastLogin() ? $user->getLastLogin()->format('m/d/Y') : '',
                'is_active' => $user->getIsActive(),
                'user_type' => $user->getUserType(),
                'bonus_allowed' => $user->getBonusAllowed() ? 1 : 0,
                'covid_allowed' => $user->getCovidAllowed() ? 1 : 0
            ];

        }
        usort($response['users'], function($a, $b) {
            if($a['id'] >= $b['id']) {
                return 1;
            }
            return -1;
        });

        $response['success'] = true;

        return $response;
    }

    public static function saveUserData($data) {
        $response = ['success' => false];
        $userId = $data['id'];
        $memberId = $data['member_id'];
        $member = auth::getAuthMember();

        if(!$userId) {
            /** @var NstMemberUsers $user */
            $user = ioc::resolve('saMemberUsers');
            $user->setFirstName($data['first_name']);
            $user->setLastName($data['last_name']);
            $user->setUsername($data['username']);
            /** @var saMemberEmail $userEmail */
            $userEmail = ioc::resolve('saMemberEmail');
            $userEmail->setEmail($data['email']);
            $userEmail->setIsActive(true);
            $userEmail->setIsPrimary(false);
            $userEmail->setType('Personal');
            $userEmail->setMember($member);
            $user->setEmail($userEmail);
            $user->setPassword($data['password']);
            $user->setMember($member);
            $user->setUserType($data['user_type']);
            $user->setIsActive(true);
            $user->setCovidAllowed($data['user_type'] == 'admin' || (bool)$data['covid_allowed']);
            $user->setBonusAllowed($data['user_type'] == 'admin' || (bool)$data['bonus_allowed']);
            app::$entityManager->persist($userEmail);
            app::$entityManager->persist($user);
            app::$entityManager->flush();

            $response['success'] = true;
            $response['id'] = $user->getId();
        } else {

            /** @var NstMemberUsers $user */
            $user = ioc::get('saMemberUsers', ['id' => $userId]);
            if ($user) {
                $user->setUsername($data['username']);
                if ($user->getEmail()) {
                    $user->getEmail()->setEmail($data['email']);
                }
                else {
                    /** @var saMemberEmail $userEmail */
                    $userEmail = ioc::resolve('saMemberEmail');
                    $userEmail->setEmail($data['email']);
                    $userEmail->setIsActive(true);
                    $userEmail->setIsPrimary(true);
                    $userEmail->setType('Personal');
                    $userEmail->setMember($member);
                    app::$entityManager->persist($userEmail);

                    $user->setEmail($userEmail);
                }
                $user->setFirstName($data['first_name']);
                $user->setLastName($data['last_name']);
                $user->setUserType($data['user_type']);
                $user->setIsActive(true);
                $user->setCovidAllowed($data['user_type'] == 'Admin' || (bool)$data['covid_allowed']);
                $user->setBonusAllowed($data['user_type'] == 'Admin' || (bool)$data['bonus_allowed']);
                if($data['password']) {
                    $user->setPassword($data['password']);
                }

                app::$entityManager->flush();

                $response['success'] = true;
                $response['id'] = $user->getId();
            }
        }

        return $response;
    }

    public static function deleteUser($data) {
        $response = ['success' => false];
        $userId = $data['id'];

        $user = ioc::get('saMemberUsers', ['id' => $userId]);

        app::$entityManager->remove($user);
        app::$entityManager->flush();

        $response['success'] = true;

        return $response;
    }

    /**
     * @param Request $request
     */
    public function deleteNstMember($request) {
        $id = $request->getRouteParams()->get('id');

        /** @var NstMember $member */
        $member = ioc::get('NstMember', ['id' => $id]);
        if($member) {
            $member->setIsDeleted(true);

            if($member->getNurse()) {
                $member->getNurse()->setIsDeleted(true);
            }
            if($member->getProvider()) {
                $member->getProvider()->setIsDeleted(true);
            }
            app::$entityManager->flush();
        }
    }

    public function getNstMemberUsersType($username) {
        /** @var NstMemberUsers $member */
        $memberUser = ioc::get('NstMemberUsers', ['username' => $username]);
        if (!$memberUser) {
            return null;
        }

        /** @var NstMember $member */
        $member = $memberUser->getMember();
        return $member->getMemberType();
    }
}
