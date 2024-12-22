<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class AccessHandler extends AbstractAuthenticator
{
    use TargetPathTrait;

    public function __construct(private UserProviderInterface $userProvider,
    private PasswordUpgraderInterface $passwordUpgrader)
    {
    }

    public function supports(Request $request): ?bool
    {
        // Support POST requests to /login
        return $request->isMethod('POST') && $request->getPathInfo() === '/login';
    }

    public function authenticate(Request $request): Passport
    {
        // Retrieve username and password from the request
        $username = $request->request->get('username', '');
        $password = $request->request->get('password', '');

        if (empty($username) || empty($password)) {
            throw new AuthenticationException('Username or password cannot be empty.');
        }

        return new Passport(
            new UserBadge($username, [$this->userProvider, 'loadUserByIdentifier']),
            new PasswordCredentials($password),
            [
                new PasswordUpgradeBadge($password, $this->passwordUpgrader),
            ]
        );
    }   

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Redirect to the originally requested page or a default route
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        return new RedirectResponse($targetPath ?? '/');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Redirect back to login with an error message
        return new RedirectResponse('/login?error=' . urlencode($exception->getMessageKey()));
    }
}
