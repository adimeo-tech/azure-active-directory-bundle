<?php
namespace OpcoAADBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use OpcoAADBundle\Entity\User;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use TheNetworg\OAuth2\Client\Provider\AzureResourceOwner;

/**
 * Class AzureAuthenticator
 *
 * @package App\Security
 */
class AzureAuthenticator extends SocialAuthenticator
{
    /** @var ClientRegistry */
    protected $clientRegistry;

    /** @var EntityManagerInterface */
    protected $em;

    /**
     * AzureAuthenticator constructor.
     *
     * @param ClientRegistry         $clientRegistry
     * @param EntityManagerInterface $em
     */
    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
    }

    /**
     * Check if the request is supported by this listener class
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        return $request->getPathInfo() === '/login-azure' && $request->isMethod('GET');
    }

    /**
     * Retrieve the credentials for the user
     *
     * @param Request $request
     * @return \League\OAuth2\Client\Token\AccessToken|mixed
     */
    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getAzureClient());
    }

    /**
     * Retrieve the user according to the token sent by the provider
     *
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return User|object|\Symfony\Component\Security\Core\User\UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var AzureResourceOwner $userInfo */
        $userInfo = $this
            ->getAzureClient()
            ->fetchUserFromToken($credentials);

        $email = $userInfo->claim('upn') ?: $userInfo->claim('unique_name');
        $name = $userInfo->claim('name');

        /**
         * Find the user by it's email
         * If it does not exists => create it
         */
        $user = $this
            ->em
            ->getRepository(User::class)
            ->findOneBy([
                'email' => $email
            ]);

        if (null === $user) {
            $user = new User();
            $user->setUsername($email);
            $user->setFullname($name);

            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    /**
     * @return \KnpU\OAuth2ClientBundle\Client\OAuth2Client
     */
    protected function getAzureClient()
    {
        return $this
            ->clientRegistry
            ->getClient('azure');
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse('/login');
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }
}
