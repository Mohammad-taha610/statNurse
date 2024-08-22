<?php

namespace App\Controller;

use App\Entity\Nst\Member\NstMemberUsers;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\Inertia;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    private EntityManager $entityManager;

    private UserPasswordHasherInterface $passwordHasher;

    private Inertia $inertia;

    public function __construct(
        $inertia,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->inertia = $inertia;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/admin/welcome', name: 'app_welcome_admin', methods: ['GET'])]
    public function welcomeAdmin()
    {
        return $this->inertia->render('welcome', [
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout()
    {
        throw new \Exception('This should never be reached!');
    }
}
