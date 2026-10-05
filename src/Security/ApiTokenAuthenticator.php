<?php

namespace App\Security;

use App\Entity\ApiError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PreAuthenticatedUserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 *      !!!! NOT IN USE
 */
class ApiTokenAuthenticator extends AbstractAuthenticator
{
    private $apiKey;
    private $apiValue;

    public function __construct()
    {
        $this->apiKey = $_ENV['BACKEND_API_KEY'] ?? null;
        $this->apiValue = $_ENV['BACKEND_API_VALUE'] ?? null;
    }


    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
//        return true;
        dd($request->headers);
        dd($request->headers->get('Authorization'));
        dd($request->headers->has($this->apiKey));
        return $request->headers->has($this->apiKey);
    }

    public function authenticate(Request $request): Passport
    {
        $apiValueExtracted = $request->headers->get($this->apiKey);
        $apiValueExtracted = $_ENV['BACKEND_API_VALUE'];

        if (null === $apiValueExtracted || $apiValueExtracted !== $this->apiValue) {
            // The token was empty or did not match the expected value
            // Authentication fails with HTTP Status Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('Invalid API token');
        }


        // Implement your own logic to get the user identifier from `$apiToken`
        // e.g., by looking up a user in the database using its API key
//        $userIdentifier = 'this_is_API_user';
//        return new SelfValidatingPassport(new UserBadge($userIdentifier));

        $user = new FakeApiUser('anonymous');
        $userLoader = function (string $userIdentifier) {
            return new FakeApiUser($userIdentifier);
        };

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier(), $userLoader));

        $userBadge = new UserBadge($user->getUserIdentifier(), $userLoader);
        $customBadge = new CustomCredentials(
        // If this function returns anything else than `true`, the credentials are marked as invalid.
        // The $credentials parameter is equal to the next argument of this class
            function (string $credentials, UserInterface $user) use ($apiValueExtracted): bool {
//                return $user && $credentials === $apiValueExtracted;
                return true;
            },
            // The custom credentials
            $this->apiValue
        );

//        $checker = function (string $credentials, UserInterface $user) use ($apiValueExtracted): bool {
////                return $user && $credentials === $apiValueExtracted;
//            return true;
//        };
//
//        if (true !== $checker($this->apiValue, $userBadge->getUser())) {
//            throw new BadCredentialsException('Credentials check failed as the callable passed to CustomCredentials did not return "true".');
//        }
//
//        dd('isresolved=true');

        $customBadge->executeCustomChecker($userBadge->getUser());
//        dd($customBadge->isResolved());

        $passport = new Passport($userBadge, $customBadge);


//        $passport = new Passport(
//            new UserBadge($user->getUserIdentifier(), $userLoader),
//            new CustomCredentials(
//                // If this function returns anything else than `true`, the credentials are marked as invalid.
//                // The $credentials parameter is equal to the next argument of this class
//                function (string $credentials, UserInterface $user) use ($apiValueExtracted): bool {
//                    return $credentials === $apiValueExtracted ;
//                },
//                $this->apiValue  // The custom credentials
//            )
//        );

//        dd($passport);
//        dd($passport->getUser());

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
