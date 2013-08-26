<?php

namespace DIISH\CommonBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * LDAPUserProfileController for Common site.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @Route("/common/userprofile")
 */
class LDAPUserProfileController extends AppController
{
    /**
     * @Route("/", name="common_userprofile_show")
     * @Template()
     */
    public function showAction()
    {
        $displayname = 
        $userPrincipalName = null;
        
        $user = $this->get('security.context')->getToken()->getUser();
        if (method_exists($user, 'getAttribute')) {
            $displayname = $user->getAttribute('displayname');
            $userPrincipalName = $user->getAttribute('userprincipalname');
        } else {
            $displayname = $user->getUsername();
            $userPrincipalName = '';
        }
        
        return array(
            'user' => $user,
            'displayname' => $displayname,
            'userPrincipalName' => $userPrincipalName
        );
    }
}
