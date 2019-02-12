<?php
namespace OpcodingAADBundle\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AzureController
 *
 * @package App\Controller
 */
class AzureController extends AbstractController
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/login", name="connect_azure")
     *
     * @param ClientRegistry $clientRegistry
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function connect(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('azure')
            ->redirect();
    }

    /**
     * Azure redirects to back here afterwards
     *
     * @Route("/login-azure", name="connect_azure_check")
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function connectCheck()
    {
        if (!$this->getUser()) {
            return new JsonResponse([
                'status' => false,
                'message' => 'User not found!'
            ]);
        }

        return $this->redirectToRoute('homepage');
    }
}
