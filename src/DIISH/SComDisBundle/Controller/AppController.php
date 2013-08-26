<?php

namespace DIISH\SComDisBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use DIISH\SComDisBundle\Entity\Log;

/**
 * AppController
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
abstract class AppController extends Controller
{
    public function logInfo($message)
    {
        $this->log($message, Log::LOG_LEVEL_INFO);
    }
    
    public function logError($message)
    {
        $this->log($message, Log::LOG_LEVEL_ERROR);
    }
    
    public function logWarn($message)
    {
        $this->log($message, Log::LOG_LEVEL_WARN);
    }

    public function log($message, $level)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $displayname = (method_exists($user, 'getAttribute')) ? 
            $displayname = $user->getAttribute('displayname') :
            $displayname = $user->getUsername();
        
        $service = $this->get('log_service');
        
        switch ($level) {
            case Log::LOG_LEVEL_INFO :
                $service->info($message, $displayname);
                break;
            case Log::LOG_LEVEL_WARN :
                $service->warn($message, $displayname);
                break;
            case Log::LOG_LEVEL_ERROR :
                $service->error($message, $displayname);
                break;
        }
    }
}
