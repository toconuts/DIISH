<?php

namespace DIISH\CommonBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * SecurityController.
 * 
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 */
class SecurityController extends AppController
{
    /**
     * @Route("/login", name="common_login")
     * @Template()
     */
    public function loginAction()
    {   
        $error = $this->getAuthenticationError();

        return array(
            'last_username' => $this->get('request')->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
            'token'         => $this->generateToken(),
        );        
    }

    private function getAuthenticationError()
    {
        if ($this->get('request')->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            return $this->get('request')->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->get('request')->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
    }

    private function generateToken()
    {
        $token = $this->get('form.csrf_provider')
                      ->generateCsrfToken('authenticate');

        return $token;
    }
}
