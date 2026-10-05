<?php

namespace App\Security;

use App\Entity\ApiError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiAuthenticatorBearerToken extends AbstractAuthenticator
{
    private $expectedApiToken;

    public function __construct()
    {
        $this->expectedApiToken = $_ENV['BACKEND_API_TOKEN'] ?? null;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if (0 !== strpos($authorizationHeader, 'Bearer ')) {
            // The 'Bearer' is missing, then authentication fails
            throw new CustomUserMessageAuthenticationException('Authorization Bearer token is missing.');
        }

        $apiToken = substr($authorizationHeader, 7); // Remove 'Bearer ' prefix

        if (null === $apiToken || $apiToken !== $this->expectedApiToken) {
            // The token was empty or did not match the expected value
            // Authentication fails with HTTP Status Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('Invalid API token.');
        }

//        // Implement your own logic to get the user identifier from `$apiToken`
//        // e.g., by looking up a user in the database using its API key
//        $userIdentifier = 'this_is_API_user';
//        return new SelfValidatingPassport(new UserBadge($userIdentifier));

        $user = new FakeApiUser('anonymous');
        $userLoader = function (string $userIdentifier) {
            return new FakeApiUser($userIdentifier);
        };

        $userBadge = new UserBadge($user->getUserIdentifier(), $userLoader);
        $credentialsBadge = new CustomCredentials(
            // If this function returns anything else than `true`, the credentials are marked as invalid.
            // The $credentials parameter is equal to the next argument of this class
            function (string $credentials, UserInterface $user) use ($apiToken): bool {
                return ($user instanceof FakeApiUser && $credentials === $apiToken);
//                return true;
            },
            $this->expectedApiToken // The custom credentials
        );

        $credentialsBadge->executeCustomChecker($userBadge->getUser());

        $passport = new Passport($userBadge, $credentialsBadge);
        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'error' => ApiError::HTTP_UNAUTHORIZED,
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
