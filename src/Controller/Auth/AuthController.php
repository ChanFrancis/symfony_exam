<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Entity\User;
use App\Service\AuthCheckService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security, private AuthCheckService $authCheckService)
    {
        $this->security = $security;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_BANNED')) {
                throw new AccessDeniedException('Your account is banned. Access denied.');
            }

            if ($this->isGranted('ROLE_USER')) {
                return $this->redirectToRoute('homepage');
            }

            throw new AccessDeniedException('Access denied. You do not have the required role.');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    
    #[Route(path: '/register', name: 'page_register')]
    public function register(): Response
    {
        return $this->render(view: 'auth/register.html.twig');
    }

    #[Route(path: '/forgot', name: 'page_forgot')]
    public function forgot(): Response
    {
        return $this->render(view: 'auth/forgot.html.twig');
    }

    #[Route(path: '/reset', name: 'page_reset')]
    public function reset(): Response
    {
        return $this->render(view: 'auth/reset.html.twig');
    }

    #[Route(path: '/confirm', name: 'page_confirm')]
    public function confirm(): Response
    {
        return $this->render(view: 'auth/confirm.html.twig');
    }

    public function registration(UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User('something');
        $plaintextPassword = "myExtraSecrurePassword";

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);

        return new Response('Successful authentication.');
    }

    public function delete(UserPasswordHasherInterface $passwordHasher, PasswordAuthenticatedUserInterface $user): void
    {
        // ... e.g. get the password from a "confirm deletion" dialog
        $plaintextPassword = "myExtraSecrurePassword";

        if (!$passwordHasher->isPasswordValid($user, $plaintextPassword)) {
            throw new AccessDeniedHttpException();
        }
    }

    public function index(): Response
    {
        $this->authCheckService->someMethod();

        if ($this->authCheckService->isUserAuthenticated()) {
            $roles = $this->authCheckService->getCurrentUserRoles();
            return new Response('User is authenticated with roles: ' . implode(', ', $roles));
        }

        return new Response('User is not authenticated.');
    }
}
