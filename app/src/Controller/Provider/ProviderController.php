<?php

namespace App\Controller\Provider;

use App\Controller\AbstractController;
use App\DTO\Member\NstMemberDTO;
use App\DTO\Member\NstMemberUserDTO;
use App\DTO\Member\NurseDTO;
use App\Service\Provider\ProviderService;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProviderController extends AbstractController
{
    private ProviderService $providerService;

    public function __construct(InertiaInterface $inertia, ProviderService $providerService)
    {
        parent::__construct($inertia);
        $this->providerService = $providerService;
    }

    #[Route('/providers/nurses', name: 'app_provider_nurses', methods: ['GET'])]
    public function getProviderNurses(#[CurrentUser] $user, Request $request): Response
    {
        $start = $request->query->get('start') ? new \DateTime($request->query->get('start')) : null;
        $end = $request->query->get('end') ? new \DateTime($request->query->get('end')) : null;

        $nurses = $this->providerService->loadAssignableNurses(
            $user,
            $request->query->get('nurse_type'),
            $request->query->get('provider'),
            $start,
            $end,
        );

        $nurseDTOs = array_map(function ($nurse) {
            return new NurseDTO($nurse);
        }, $nurses);

        $response = new JsonResponse();
        $response->setData([
            'nurses' => $nurseDTOs
        ]);
        return $response;
    }


    #[Route('/provider/{providerId}/timeslots', name: 'app_provider_timeslots', methods: ['GET'])]
    public function getProviderTimeslots(#[CurrentUser] $user, int $providerId): Response
    {
        $timeslots = $this->providerService->loadProviderTimeslots(
            $user,
            $providerId,
        );

        $response = new JsonResponse();
        $response->setData([
            'timeslots' => $timeslots
        ]);
        return $response;
    }

    #[Route('/provider/{providerId}/credentials', name: 'app_provider_credentials', methods: ['GET'])]
    public function getProviderCredentials(#[CurrentUser] $user, int $providerId): Response
    {
        $credentials = $this->providerService->loadProviderCredentials(
            $user,
            $providerId,
        );

        $response = new JsonResponse();
        $response->setData([
            'credentials' => $credentials
        ]);
        return $response;
    }

    #[Route('/provider/locations', name: 'app_provider_locations', methods: ['GET'])]
    public function getProviders(#[CurrentUser] $user, Request $request): Response
    {
        $locations = $this->providerService->getProvidersData(
            $user,
        );

        return $this->inertia->render('ProviderLocations', [
            'user' => new NstMemberUserDTO($user),
            'locations' => $locations,
        ]);
    }

    #[Route('/provider/{id}', name: 'app_provider', methods: ['GET'])]
    public function getProvider(#[CurrentUser] $user, int $id): Response
    {
        $provider = $this->providerService->getProviderData(
            $user,
            $id,
        );

        return $this->inertia->render('ProviderLocation', [
            'user' => new NstMemberUserDTO($user),
            'location' => $provider,
        ]);
    }

    #[Route('/provider/{id}/rates', name: 'app_provider_rates', methods: ['GET'])]
    public function getProviderRates(#[CurrentUser] $user, int $id): Response
    {
        $rates = $this->providerService->getProviderRates(
            $user,
            $id,
        );

        // return json
        return new JsonResponse([
            'rates' => $rates,
        ]);
    }

    //set the route, so [site URL]/hello will trigger this
    #[Route('/hello', name: 'hello_world')]
    public function hello(): Response
    {
        //create a new Response object
        $response = new Response();

        //set the return value
        $response->setContent('Hello World!');

        //make sure we send a 200 OK status
        $response->setStatusCode(Response::HTTP_OK);

        // set the response content type to plain text
        $response->headers->set('Content-Type', 'text/plain');

        // send the response with appropriate headers
        $response->send();
    }
}
