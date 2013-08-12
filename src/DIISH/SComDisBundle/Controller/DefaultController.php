<?php

namespace DIISH\SComDisBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use DIISH\SComDisBundle\Entity\Surveillance;
use DIISH\SComDisBundle\Entity\SurveillanceRepository;
use DIISH\SComDisBundle\Entity\BBS;
use DIISH\SComDisBundle\Entity\BBSRepository;

use DIISH\SComDisBundle\Form\SurveillanceType;
use DIISH\SComDisBundle\Form\SearchSurveillanceType;      
use DIISH\SComDisBundle\Form\BBSNewType;

/**
 * DefaultController for SComDis site.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @Route("/scomdis")
 */
class DefaultController extends AppController
{
    /**
     * @Route("/", name="scomdis")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        
        // BBS
        $bbsRepository = $manager->getRepository('DIISHSComDisBundle:BBS');
        
        $bbs = new BBS();
        $form = $this->createForm(new BBSNewType(), $bbs, array(
            'action' => $this->generateUrl('scomdis'),
            'method' => 'POST',
        ));
        
        if ($request->getMethod() === 'POST') {
            $data = $request->request->get($form->getName());
            $form->bind($data);
            
            if ($form->isValid()) {               
                $securityContext = $this->get('security.context');
                $bbs->setUsername($securityContext->getToken()->getUser()->getDisplayname());
                
                $bbsRepository->postMessage($bbs);
                return $this->redirect($this->generateUrl('scomdis'));
            }
        }
        
        $paginator = $this->get('knp_paginator');
        
        $bbsPageName = 'pbbs';
        $bbsQuery = $bbsRepository->createQueryBuilder('r')
                ->orderBy('r.updatedAt', 'DESC')
                ->getQuery();
        $bbsPage = $request->query->get($bbsPageName, 1);
        $bbsPagination = $paginator->paginate(
                $bbsQuery, 
                $bbsPage,
                5,
                array('pageParameterName' => $bbsPageName)
        );
        
        // Log
        $logPageName = 'plog';
        $logRepository = $manager->getRepository('DIISHSComDisBundle:Log');
        $logQuery = $logRepository->createQueryBuilder('r')
                ->orderBy('r.updatedAt', 'DESC')
                ->getQuery();
        
        $logPage = $request->query->get($logPageName, 1);
        $logPagination = $paginator->paginate(
                $logQuery, 
                $logPage,
                10,
                array('pageParameterName' => $logPageName)
        );
        
        return array(
            'form' => $form->createView(),
            'bbsPagination' => $bbsPagination,
            'logPagination' => $logPagination,
        );
    }
    
    /**
     * @Route("/list", name="scomdis_list")
     * @Template()
     */
    public function listAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Surveillance');
        $query = $repo->createQueryBuilder('r')->orderBy('r.weekend', 'DESC')->getQuery();
        
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($query, $request->
                query->get('page', 1), 100);
      
        return array(
            'pagination' => $pagination,
        );
     }
    
    /**
     * @Route("/new", name="scomdis_new")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $syndromeRepository = $manager->getRepository('DIISHSComDisBundle:Syndrome4Surveillance');
        $syndromes = $syndromeRepository->findAll();
        $surveillance = new Surveillance($syndromes);

        $form = $this->createForm(new SurveillanceType(), $surveillance);
        
        $sentinelSiteRepository = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
        $sentinelSites = $sentinelSiteRepository->findAll();
        
        $clinicRepository = $manager->getRepository('DIISHSComDisBundle:Clinic');
        $clinics = $clinicRepository->findAll();
        
        $userRepository = $this->getDoctrine()->getManager('common')->getRepository('DIISHCommonBundle:User');
        $query = $userRepository->createQueryBuilder('r')->orderBy('r.displayname', 'ASC')->getQuery();
        $users = $query->getResult();
        
        if ($request->getMethod() === 'POST') {
            $data = $request->request->get($form->getName());
            $form->bind($data);
            
            if ($form->isValid()) {                
                $request->getSession()->set('scomdis_surveillance/new', $data);
                return $this->redirect($this->generateUrl('scomdis_confirm'));
            }
        }
        
        return array(
            'sentinelSites' => $sentinelSites,
            'clinics'       => $clinics,
            'users'         => $users,
            'form'          => $form->createView(),
        );
    }
    
    /**
     * @Route("/confirm", name="scomdis_confirm")
     * @Template()
     */
    public function confirmAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Syndrome4Surveillance');
        $syndromes = $repo->findAll();
        
        $surveillance = new Surveillance($syndromes);
        
        if (!$this->restoreSurveillanceForms($surveillance, array('surveillance',))) {
            return $this->redirect($this->generateUrl('scomdis_surveillance_new'));
        }

        $surveillanceRepository = $manager->getRepository('DIISHSComDisBundle:Surveillance');
        
        if ('POST' === $request->getMethod()) {
            try {
                
                $surveillanceRepository->saveSurveillance($surveillance);
                $this->logNewSurveillance($surveillance);
                
                $session = $request->getSession();
                $session->remove('scomdis_surveillance/new');
                $message = 'Complete adding the new surveillance. '.
                            $surveillance->getYear().'-'.
                            $surveillance->getWeekOfYear().' '.
                            $surveillance->getClinic().'@'.
                            $surveillance->getSentinelSite();
                $request->getSession()->getFlashBag()->add('success', $message);

                return $this->redirect($this->generateUrl('scomdis_list'));
                
            } catch (\InvalidArgumentException $e) {
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                $this->logError($e->getMessage());
            }
        } else {
            if ($surveillanceRepository->isExist($surveillance)) {
                $request->getSession()->getFlashBag()->add('error', "Warning: This surveillance is already exist.");
            }
        }
        
        return array(
            'surveillance' => $surveillance,
        );
    }
    
    /**
     * @Route("/{id}/show", name="scomdis_show", requirements={"id" = "\d+"})
     * @Template()
     */
    public function showAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Surveillance');
        $surveillance = $repo->find($id);
        if (!$surveillance) {
            throw $this->createNotFoundException('No surveillance found for id '.$id);
        }
        
        $referer = $request->server->get('HTTP_REFERER');
        $request->getSession()->set('surveillance_edit/before_show_url', $referer);
        
        return array(
            'surveillance' => $surveillance,
        );
    }

    /**
     * @Route("/{id}/edit", name="scomdis_edit", requirements={"id" = "\d+"})
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $surveillanceRepository = $manager->getRepository('DIISHSComDisBundle:Surveillance');
        $surveillance = $surveillanceRepository->find($id);
        if (!$surveillance) {
            throw $this->createNotFoundException('No surveillance found for id '.$id);
        }

        $form = $this->createForm(new SurveillanceType(), $surveillance);
        
        $sentinelSiteRepository = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
        $sentinelSites = $sentinelSiteRepository->findAll();
        
        $clinicRepository = $manager->getRepository('DIISHSComDisBundle:Clinic');
        $clinics = $clinicRepository->findAll();
        
        $userRepository = $this->getDoctrine()->getManager('common')->getRepository('DIISHCommonBundle:User');
        $query = $userRepository->createQueryBuilder('r')->orderBy('r.displayname', 'ASC')->getQuery();
        $users = $query->getResult();
        
        if ($request->getMethod() === 'POST') {
            $data = $request->request->get($form->getName());
            $form->bind($data);
            
            if ($form->isValid()) {
                try {
                    
                    $surveillanceRepository->saveSurveillance($surveillance);
                    $this->logUpdateSurveillance($surveillance);
                    
                    $request->getSession()->getFlashBag()->add('success', 'Update complete.');
                    return $this->redirect(
                            $this->generateUrl('scomdis_update', 
                                    array('id' => $surveillance->getId()))
                    );

                } catch (\InvalidArgumentException $e) {
                    $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                    $this->logError($e->getMessage());
                }   
            }
        }
        
        return array(
            'surveillance'  => $surveillance,
            'sentinelSites' => $sentinelSites,
            'clinics'       => $clinics,
            'users'         => $users,
            'form'          => $form->createView(),
        );
    }
    
    /**
     * @Route("/{id}/update", name="scomdis_update", requirements={"id" = "\d+"})
     * @Template
     */
    public function updateAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Surveillance');
        $surveillance = $repo->find($id);
        if (!$surveillance) {
            throw $this->createNotFoundException('No surveillance found for id '.$id);
        }
        
        $session = $request->getSession();
        if ($session->has('surveillance_edit/before_show_url')) {
            $pre_url = $session->get('surveillance_edit/before_show_url');
            $session->remove('surveillance_edit/before_show_url');
        }
        
        return array(
            'surveillance'  => $surveillance,
            'pre_url' => $pre_url,
        );
    }
    
    /**
     * @Route("/{id}/receive", name="scomdis_receive", requirements={"id" = "\d+"})
     */
    public function receiveAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Surveillance');
        $user = $this->get('security.context')->getToken()->getUser();
        
        try {
            $surveillance = $repo->find($id);
            $repo->receiveSurveillance($id, $user);
            $this->logReceiveSurveillance($surveillance);
            
            $message = 'Received the surveillance.';
            $request->getSession()->getFlashBag()->add('success', $message);
            
        } catch (\InvalidArgumentException $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
            $this->logError($e->getMessage());
        }
        
        return $this->redirect($this->generateUrl(
                'scomdis_update', array('id' => $id))
        );
    }
    
    /**
     * @Route("/{id}/delete", name="scomdis_delete", requirements={"id" = "\d+"})
     */
    public function deleteAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Surveillance');

        try {
            $surveillance = $repo->find($id);
            $repo->deleteSurveillance($id);
            $this->logDeleteSurveillance($surveillance);
            $message = 'Complete deleting the surveillance.';
            $request->getSession()->getFlashBag()->add('success', $message);
            
        } catch (\InvalidArgumentException $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
            $this->logError($e->getMessage());
        }
        
        return $this->redirect($this->generateUrl('scomdis_list'));
    }
    
    /**
     * @Route("/search", name="scomdis_search")
     * @Template
     */
    public function searchAction(Request $request)
    {
        $weekend = new \DateTime('last Saturday');
        $weekend->setTime(0, 0, 0);
        
        $manager = $this->getDoctrine()->getManager('scomdis');
        $sentinelSiteRepository = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
        $sentinelSites = $sentinelSiteRepository->findAll();
        
        $clinicRepository = $manager->getRepository('DIISHSComDisBundle:Clinic');
        $clinics = $clinicRepository->findAll();

        $form = $this->createForm(new SearchSurveillanceType, null);
        
        $form['weekend']->setData($weekend);
        $form['weekOfYear']->setData(strftime('%V', time()));
        $form['year']->setData(strftime('%G', time()));
        
        if ($request->getMethod() === 'POST') {
            $data = $request->request->get($form->getName());
            $form->bind($data);
            
            if ($form->isValid()) {
                $weekend = $form['weekend']->getData();
                $clinic = $form['clinic']->getData();
                
                $manager = $this->getDoctrine()->getManager('scomdis');
                $surveillanceRepository = $manager->getRepository('DIISHSComDisBundle:Surveillance');
                $surveillance = $surveillanceRepository->findOneBy(array(
                    'weekend' => $weekend,
                    'clinic' => $clinic
                ));
                
                if ($surveillance) {
                    return $this->redirect($this->generateUrl(
                        'scomdis_show', array('id' => $surveillance->getId()))
                    );
                } else {
                    $request->getSession()->getFlashBag()->add('error', "No surveillance found");
                }
            }
        }
        
        return array(
            'sentinelSites' => $sentinelSites,
            'clinics'       => $clinics,
            'form' => $form->createView(),
        );
    }
    
    /**
     * Restore User data
     * @param User $user
     * @param array $formKeys
     * @return boolean
     * @throws \InvalidArgumentException 
     */
    private function restoreSurveillanceForms(Surveillance $surveillance, array $formKeys) {
        
        $session = $this->getRequest()->getSession();
        
        $factory = $this->get('form.factory');
        $binder = function($type, $data) use($factory, $surveillance) {
            if (isset($data['_token'])) {
                unset($data['_token']);
            }
            $form = $factory->create($type, $surveillance, array('csrf_protection' => false));
            $form->bind($data);
            
            return $form->isValid();
        };
        
        $valid = true;
        foreach ($formKeys as $formKey) {
            switch ($formKey) {
                case 'surveillance':
                    $valid = $binder(new SurveillanceType(),
                                     $session->get('scomdis_surveillance/new'));
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown form key "%s"', $formKeys));
            }
            
            if (!$valid) {
                return false;
            }
        }
        
        return true;
    }
    
    public function logNewSurveillance($surveillance)
    {
        $this->logBase($surveillance, 'Add new surveillance.');
    }
    
    public function logUpdateSurveillance($surveillance)
    {
        $this->logBase($surveillance, 'Update surveillance.');
    }
    
    public function logDeleteSurveillance($surveillance)
    {
        $this->logBase($surveillance, 'Delete surveillance.');
    }
    
    public function logReceiveSurveillance($surveillance)
    {
        $this->logBase($surveillance, 'Receive surveillance.');
    }
    
    protected function logBase($surveillance, $premessage)
    {
        $message = $premessage.' ID: '.
                    $surveillance->getId().' '.
                    $surveillance->getYear().'-'.
                    $surveillance->getWeekOfYear().' '.
                    $surveillance->getClinic().'@'.
                    $surveillance->getSentinelSite();
        
        $this->logInfo($message);
    }
}
