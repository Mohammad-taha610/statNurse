<?php

namespace sa\events;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\responses\Json;
use sacore\application\ValidateException;

class ReservationsApiController
{
    public function reserve($request)
    {
        $id = $request->getRouteParams()->get('id');
        $event = ioc::getRepository('Event')->find($id);

        $json = new JSON();
        try {
            /** @var ReservationRepository $reservationRepo */
            $reservationRepo = app::$entityManager->getRepository(ioc::staticGet('EventReservation'));
            $reservation = $reservationRepo->findOneBy(['email' => $request->request->get('email')]);

            if ($reservation) {
                throw new ValidateException('You have already reserved this event.');
            }

            /** @var Reservation $reservation */
            $reservation = ioc::resolve('EventReservation');
            $reservation->setEmail($request->request->get('email'));
            $reservation->setEvent($event);

            app::$entityManager->persist($reservation);
            app::$entityManager->flush();
            $json->data['success'] = true;
            $json->data['reservation'] = $reservation;

            return $json;
            //Keeping this here for posterity
//            $response = $api->bldSuccessArray();
//            $response['reservation'] = $reservation;
//            $api->response(200, $response);
        } catch(ValidateException $e) {
            $json->data['success'] = false;
            $json->data['message'] = $e->getMessage();

            return $json;
//            $api->response(200, $api->bldErrorArray($e->getMessage()));
        } catch(\Exception $e) {
            $json->data['success'] = false;
            $json->data['message'] = "Oops! We couldn't complete your reservation. Please refresh and try again.";

            return $json;
//            $api->response(200, $api->bldErrorArray("Oops! We couldn't complete your reservation.
//                Please refresh and try again."));
        }
    }

    public function cancel($request)
    {
        $id = $request->getRouteParams()->get('id');
        $json = new JSON();
        try {
            /** @var ReservationRepository $reservationRepo */
            $reservationRepo = app::$entityManager->getRepository(ioc::staticGet('EventReservation'));
            $reservation = $reservationRepo->findOneBy(['email' => $request->request->get('email')]);

            if (is_null($reservation)) {
                $json->data['message'] = "Sorry, we couldn't find a reservation with that email.";
            }

            app::$entityManager->remove($reservation);
            app::$entityManager->flush();
            $json->data['success'] = true;
            //$api->response(200, $api->bldSuccessArray());
            return $json;
        } catch (\Exception $e) {
            $json->data['success'] = true;
            $json->data['message'] = 'Oops! We were unable to cancel your reservation. Please refresh the page and try again. If the problem continues, please give us a call.';

            return $json;
//            $api->response(200, $api->bldErrorArray("Oops! We were unable to cancel your reservation. Please refresh the page and try again. If the problem continues, please give us a call."));
        }
    }

    public function reserveRecurrence($request)
    {
        $id = $request->getRouteParams()->get('id');
        $recurrenceId = $request->getRouteParams()->get('recurrenceId');
        $recurrenceUniqueId = $request->getRouteParams()->get('recurrenceUniqueId');
        $event = ioc::getRepository('Event')->find($id);

        $json = new JSON();
        try {
            /** @var ReservationRepository $reservationRepo */
            $reservationRepo = app::$entityManager->getRepository(ioc::staticGet('EventReservation'));
            $reservation = $reservationRepo->findOneBy(['email' => $request->request->get('email')]);

            if ($reservation) {
                throw new ValidateException('You have already reserved this event.');
            }

            /** @var Reservation $reservation */
            $reservation = ioc::resolve('EventReservation');
            $reservation->setEmail($request->request->get('email'));
            if ($recurrenceId != 0) {
                $recurrence = ioc::getRepository('EventRecurrence')->find($recurrenceId);
                $reservation->setRecurrence($recurrence);
                $recurrence->setRecurrence($reservation);
            } else {
                $recurrence = ioc::getRepository('EventRecurrence')->createRecurrenceFromEventAndUniqueId($id, $recurrenceUniqueId);
                $reservation->setRecurrence($recurrence);
                $recurrence->addReservation($reservation);
            }
            $reservation->setEvent($event);

            app::$entityManager->persist($reservation);
            app::$entityManager->persist($recurrence);
            app::$entityManager->flush();

            $json->data['success'] = true;
            $json->data['reservation'] = $reservation;

            return $json;
            //Keeping this here for posterity
//            $response = $api->bldSuccessArray();
//            $response['reservation'] = $reservation;
//            $api->response(200, $response);
        } catch(ValidateException $e) {
            $json->data['success'] = false;
            $json->data['errorMsg'] = $e->getMessage();

            return $json;
//            $api->response(200, $api->bldErrorArray($e->getMessage()));
        } catch(\Exception $e) {
            $json->data['success'] = false;
            $json->data['message'] = "Oops! We couldn't complete your reservation. Please refresh and try again.";
            $json->data['error'] = $e->getMessage();

            return $json;
//            $api->response(200, $api->bldErrorArray("Oops! We couldn't complete your reservation.
//                Please refresh and try again."));
        }
    }

    public function cancelRecurrence($request)
    {
        $id = $request->getRouteParams()->get('id');
        $recurrenceId = $request->getRouteParams()->get('recurrenceId');
        $json = new JSON();
        try {
            /** @var ReservationRepository $reservationRepo */
            $reservationRepo = app::$entityManager->getRepository(ioc::staticGet('EventReservation'));
            $reservation = $reservationRepo->findOneBy(['email' => $request->request->get('email')]);

            if (is_null($reservation)) {
                $json->data['message'] = "Sorry, we couldn't find a reservation with that email.";
            }

            app::$entityManager->remove($reservation);
            app::$entityManager->flush();
            $json->data['success'] = true;
            //$api->response(200, $api->bldSuccessArray());
            return $json;
        } catch (\Exception $e) {
            $json->data['success'] = true;
            $json->data['message'] = 'Oops! We were unable to cancel your reservation. Please refresh the page and try again. If the problem continues, please give us a call.';

            return $json;
//            $api->response(200, $api->bldErrorArray("Oops! We were unable to cancel your reservation. Please refresh the page and try again. If the problem continues, please give us a call."));
        }
    }
}
