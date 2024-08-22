<?php


namespace sa\files\Test;


use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\Request;
use sacore\application\responses\File;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sa\files\filesController;
use sa\menus\saMenuController;
use sa\messages\messagesConfig;
use sa\messages\messagesController;
use sa\messages\PushNotification;
use sa\messages\saEmail;
use sa\messages\saMessagesController;
use sa\messages\saPushNotificationController;
use sa\messages\saSMS;
use sa\messages\saVoice;
use sa\Test\RouteTest;
use sacore\utilities\notification;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\RouteCollection;

class MessagesRouteTest extends RouteTest
{

    const NUMBER_OF_ROUTES = 25;

    public function testRouteInit()
    {
        $rCollection = new RouteCollection();
        messagesConfig::getRouteCollection($rCollection, 'messages');
        $this->assertEquals(self::NUMBER_OF_ROUTES, $rCollection->count());
    }

    public function testEmailCronExists(){
        $this->singleRouteDefinition("email_cron");
    }

    //Weak test, no return test but there is no return to test
    //I guess it will stay risky cause I can't think of any good way to test what is going on in that function
    public function testEmailCronFunctionality(){
        $request = new Request(['batch_id']);
        $controller = new messagesController();
        //Just seeing if no errors occur, it doesn't return anything
        $controller->messagesCron($request);
    }

    public function testVoiceMessageTextExists(){
        $this->singleRouteDefinition("voice_message_text");
    }

    //Weak test, pretty much auto failure because of the header modification in the route
    public function testVoiceMessageTextFunctionality(){
        $request = new Request(['t' => 'somerandomaddress12345670125@gmail.com', 'i' => 0]);
        $controller = new messagesController();
        $controller->voiceText($request);
    }

    public function testSAEmailsExists(){
        $this->singleRouteDefinition("sa_emails");
    }

    public function testSAEmailsFunctionality(){
        $this->loginUser();
        $request = new Request();
        $controller = new saMessagesController();
        $view = $controller->manageEmails($request);
        $this->assertInstanceOf(View::class, $view);
        $this->loginUser();
    }

    public function testSAEmailsCreateExists(){
        $this->singleRouteDefinition("sa_emails_create");
    }

    public function testSAEmailsCreateFunctionality(){
        $request = new Request();
        $controller = new saMessagesController();
        $view = $controller->createEmails($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSAEmailsEditExists(){
        $this->singleRouteDefinition("sa_emails_edit");
    }

    public function testSAEmailsEditFunctionality(){
        /** @var saEmail $email */
        $email = ioc::resolve('saEmail');
        $email->setBatchId(100);
        $email->setBody("TestEmailEditBody");
        $email->setFromName("Test");

        app::$entityManager->persist($email);
        app::$entityManager->flush();

        $id = $email->getId();

        //Todo: Create the email
        $request = new Request([],[]);
        $request->setRouteParams(new ParameterBag(['id' =>$id]));
        $controller = new saMessagesController();
        $view = $controller->editEmails($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSAEmailsSaveExists(){
        $this->singleRouteDefinitionPost("sa_emails_save");
    }

    public function testSAEmailsSaveNewFunctionality(){
        $request = new Request([],['body' => 'TestEmailSaveNewBody']);
        $request->setRouteParams(new ParameterBag(['id' =>0]));
        $controller = new saMessagesController();
        $controller->saveEmails($request);

        $repo = ioc::getRepository('saEmail');
        $email = $repo->search(['body' => 'TestEmailSaveNewBody'])[0];
        $this->assertInstanceOf(saEmail::class, $email);
    }

    public function testSAEmailsSaveOldFunctionality(){
        /** @var saEmail $email */
        $email = ioc::resolve('saEmail');
        $email->setBatchId(200);
        $email->setBody("TestEmailSaveBody");
        $email->setFromName("TestSave");

        app::$entityManager->persist($email);
        app::$entityManager->flush();

        $id = $email->getId();


        $request = new Request([],['body' => 'TestEmailEditSaveBody']);
        $request->setRouteParams(new ParameterBag(['id' =>$id]));
        $controller = new saMessagesController();
        $controller->saveEmails($request);

        $repo = ioc::getRepository('saEmail');
        $email = $repo->search(['body' => 'TestEmailEditSaveBody'])[0];
        $this->assertInstanceOf(saEmail::class, $email);
    }

    public function testSAEmailsDeleteExists(){
        $this->singleRouteDefinition("sa_emails_delete");
    }

    public function testSAEmailsDeleteFunctionality(){
        /** @var saEmail $email */
        $email = ioc::resolve('saEmail');
        $email->setBatchId(150);
        $email->setBody("TestEmailDeleteBody");
        $email->setFromName("TestDelete");

        app::$entityManager->persist($email);
        app::$entityManager->flush();

        $id = $email->getId();

        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id'=> $id]));
        $controller = new saMessagesController();
        $redirect = $controller->deleteEmails($request);
        $this->assertInstanceOf(Redirect::class,$redirect);

        $repo = ioc::getRepository('saEmail');
        $email = $repo->search(['body' => 'TestEmailDeleteBody']);
        $this->assertEmpty($email);
    }

    public function testSAEmailsResendExists(){
        $this->singleRouteDefinition("sa_emails_resend");
    }

    //Kind of weak test, not sure of a way to test the email sending
    public function testSAEmailsResendFunctionality(){
        /** @var saEmail $email */
        $email = ioc::resolve('saEmail');
        $email->setBatchId(250);
        $email->setBody("TestEmailResendBody");
        $email->setFromName("TestResend");

        app::$entityManager->persist($email);
        app::$entityManager->flush();

        $id = $email->getId();

        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id'=> $id]));
        $controller = new saMessagesController();
        //Has a thread call so it will throw this error, I will assume if it hits this error it succeeded in resending the message
        try{
            $controller->resendEmail($request);
        }catch(Exception $e){
            $this->assertStringContainsString('The thread system is unable to connect to the system', $e->getMessage());
        }
    }

    public function testSASMSExists(){
        $this->singleRouteDefinition("sa_sms");
    }

    public function testSASMSFunctionality(){
        $request = new Request();
        $controller = new saMessagesController();
        $view = $controller->manageSMS($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSASMSCreateExists(){
        $this->singleRouteDefinition("sa_sms_create");
    }

    public function testSASMSCreateFunctionality(){
        $request = new Request();
        $controller = new saMessagesController();
        $view = $controller->createSms($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSASMSEditExists(){
        $this->singleRouteDefinition("sa_sms_edit");
    }

    public function testSASMSEditFunctionality(){
        /** @var saSMS $sms */
        $sms = ioc::resolve('saSMS');
        $sms->setBody("TestSMSEditBody");

        app::$entityManager->persist($sms);
        app::$entityManager->flush();

        $id = $sms->getId();

        //Todo: Create the email
        $request = new Request([],[]);
        $request->setRouteParams(new ParameterBag(['id' =>$id]));
        $controller = new saMessagesController();
        $view = $controller->editSms($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSASMSSaveExists(){
        $this->singleRouteDefinitionPost("sa_sms_save");
    }

    public function testSASMSSaveNewFunctionality(){
        $request = new Request([],['body' => 'TestSMSSaveNewBody']);
        $request->setRouteParams(new ParameterBag(['id' =>0]));
        $controller = new saMessagesController();
        $controller->saveSms($request);

        $repo = ioc::getRepository('saSMS');
        $email = $repo->search(['body' => 'TestSMSSaveNewBody'])[0];
        $this->assertInstanceOf(saSMS::class, $email);
    }

    public function testSASMSSaveOldFunctionality(){
        /** @var saSMS $sms */
        $sms = ioc::resolve('saSMS');
        $sms->setBody("TestSMSSaveOldBody");

        app::$entityManager->persist($sms);
        app::$entityManager->flush();

        $id = $sms->getId();


        $request = new Request([],['body' => 'TestSMSEditSaveBody']);
        $request->setRouteParams(new ParameterBag(['id' =>$id]));
        $controller = new saMessagesController();
        $controller->saveSms($request);

        $repo = ioc::getRepository('saSMS');
        $sms = $repo->search(['body' => 'TestSMSEditSaveBody'])[0];
        $this->assertInstanceOf(saSMS::class, $sms);
    }

    public function testSASMSDeleteExists(){
        $this->singleRouteDefinition("sa_sms_delete");
    }

    public function testSASMSDeleteFunctionality(){
        /** @var saSMS $sms */
        $sms = ioc::resolve('saSMS');
        $sms->setBody("TestSMSDeleteBody");

        app::$entityManager->persist($sms);
        app::$entityManager->flush();

        $id = $sms->getId();

        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id'=> $id]));
        $controller = new saMessagesController();
        $redirect = $controller->deleteSms($request);
        $this->assertInstanceOf(Redirect::class,$redirect);

        $repo = ioc::getRepository('saSMS');
        $sms = $repo->search(['body' => 'TestSMSDeleteBody']);
        $this->assertEmpty($sms);
    }


    public function testSAVoiceExists(){
        $this->singleRouteDefinition("sa_voice");
    }

    public function testSAVoiceFunctionality(){
        $request = new Request();
        $controller = new saMessagesController();
        $view = $controller->manageVoice($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSAVoiceCreateExists(){
        $this->singleRouteDefinition("sa_voice_create");
    }

    public function testSAVoiceCreateFunctionality(){
        $request = new Request();
        $controller = new saMessagesController();
        $view = $controller->createVoice($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSAVoiceEditExists(){
        $this->singleRouteDefinition("sa_voice_edit");
    }

    public function testSAVoiceEditFunctionality(){
        /** @var saVoice $voice */
        $voice = ioc::resolve('saVoice');
        $voice->setToAddress("VoiceEditAddress");

        app::$entityManager->persist($voice);
        app::$entityManager->flush();

        $id = $voice->getId();

        //Todo: Create the email
        $request = new Request([],[]);
        $request->setRouteParams(new ParameterBag(['id' =>$id]));
        $controller = new saMessagesController();
        $view = $controller->editVoice($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSAVoiceSaveExists(){
        $this->singleRouteDefinitionPost("sa_voice_save");
    }

    public function testSAVoiceSaveNewFunctionality(){
        $request = new Request([],['to_address' => 'VoiceSaveNewAddress']);
        $request->setRouteParams(new ParameterBag(['id' =>0]));
        $controller = new saMessagesController();
        $controller->saveVoice($request);

        $repo = ioc::getRepository('saVoice');
        $email = $repo->search(['to_address' => 'VoiceSaveNewAddress'])[0];
        $this->assertInstanceOf(saVoice::class, $email);
    }

    public function testSAVoiceSaveOldFunctionality(){
        /** @var saSMS $sms */
        $sms = ioc::resolve('saVoice');
        $sms->setToAddress("VoiceSaveOldAddress");

        app::$entityManager->persist($sms);
        app::$entityManager->flush();

        $id = $sms->getId();


        $request = new Request([],['to_address' => 'VoiceEditSaveAddress']);
        $request->setRouteParams(new ParameterBag(['id' =>$id]));
        $controller = new saMessagesController();
        $controller->saveVoice($request);

        $repo = ioc::getRepository('saVoice');
        $sms = $repo->search(['to_address' => 'VoiceEditSaveAddress'])[0];
        $this->assertInstanceOf(saVoice::class, $sms);
    }

    public function testSAVoiceDeleteExists(){
        $this->singleRouteDefinition("sa_voice_delete");
    }

    public function testSAVoiceDeleteFunctionality(){
        /** @var saVoice $voice */
        $voice = ioc::resolve('saVoice');
        $voice->setToAddress("VoiceDeleteAddress");

        app::$entityManager->persist($voice);
        app::$entityManager->flush();

        $id = $voice->getId();

        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id'=> $id]));
        $controller = new saMessagesController();
        $redirect = $controller->deleteVoice($request);
        $this->assertInstanceOf(Redirect::class,$redirect);

        $repo = ioc::getRepository('saVoice');
        $voice = $repo->search(['to_address' => 'VoiceDeleteAddress']);
        $this->assertEmpty($voice);
    }

    public function testPushNotificationSendExists(){
        $this->singleRouteDefinition('push_notification_send');
    }

    public function testPushNotificationSendFunctionality(){
        $controller = new saPushNotificationController();
        $view = $controller->sendNotificationView();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSANotificationViewExists(){
        $this->singleRouteDefinition('sa_notification_view');
    }

    public function testSANotificationViewFunctionality(){
        /** @var PushNotification $pushNotification */
        $pushNotification = ioc::get('PushNotification');
        $pushNotification->setTopic('Test');
        $pushNotification->setTitle('Test');
        $pushNotification->setMessage('TestTest');
        $pushNotification->setDateCreated(new DateTime());
        $pushNotification->setAttemptedSend(false);

        app::$entityManager->persist($pushNotification);
        app::$entityManager->flush();

        $id = $pushNotification->getId();

        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id' => $id]));
        $controller = new saPushNotificationController();
        $view = $controller->show($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testPushNotificationIndexExists(){
        $this->singleRouteDefinition('push_notification_index');
    }

    public function testPushNotificationIndexFunctionality(){
        $request = new Request();
        $controller = new saPushNotificationController();
        $view = $controller->index($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testMessagesCentralAcknowledgeAllExists(){
        $this->singleRouteDefinition('messages_central_acknowledge_all');
    }

    public function testMessagesCentralAcknowledgeAllFunctionality(){
        $controller = new saMessagesController();
        $redirect = $controller->acknowledgeAll();
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testMessagesSAImagesExists(){
        $this->singleRouteDefinition('messages_sa_images');
    }

//    No files to effectively test on, uncomment when/if an image is added
//    public function testMessagesSAImagesFunctionality(){
//
//    }

    public function testMessagesSACSSExists(){
        $this->singleRouteDefinition('messages_sa_css');
    }

//    No fies to effectively test on, uncomment when/if css is added
//    public function testMessagesSACSSFunctionality(){
//        $request = new Request();
//        $request->setRouteParams(new ParameterBag(['file' => 'browser.css']));
//        $controller = new messagesController();
//        $response = $controller->css($request);
//        $this->assertInstanceOf(File::class, $response);
//    }

    public function testMessagesSAJSExists(){
        $this->singleRouteDefinition('messages_sa_js');
    }

    public function testMessagesSAJSFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['file' => 'sendPushNotification.js']));
        $controller = new messagesController();
        $response = $controller->js($request);
        $this->assertInstanceOf(File::class, $response);
    }

    /** HELPER FUNCTIONS */
    protected function moduleName()
    {
        return "messages";
    }

    protected function configClass()
    {
        return messagesConfig::class;
    }
}