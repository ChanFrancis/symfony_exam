<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthCheckService
{
    public function __construct(
        private Security $security,
        private RequestStack $requestStack,
    ) {}

    public function someMethod(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            throw new \RuntimeException('No current request found.');
        }


        $firewallName = $this->security->getFirewallConfig($request)?->getName();

        if ($firewallName) {
            echo sprintf('The current firewall is: %s', $firewallName);
        } else {
            echo 'No firewall configuration found for this request.';
        }
    }

    public function isUserAuthenticated(): bool
    {
        $token = $this->security->getToken();

        return $token !== null && $this->security->isGranted('IS_AUTHENTICATED_FULLY');
    }

    public function getCurrentUserRoles(): array
    {
        $user = $this->security->getUser();

        if (!$user) {
            return [];
        }

        return $this->security->getToken()?->getRoleNames() ?? [];
    }

}
