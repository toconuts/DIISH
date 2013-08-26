<?php

namespace DIISH\SComDisBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use DIISH\SComDisBundle\Entity\Outbreak;
use DIISH\SComDisBundle\Entity\OutbreakRepository;

use DIISH\SComDisBundle\Form\SearchOutbreakType;
use DIISH\SComDisBundle\Form\OutbreakType;

/**
 * OutbreakController fot SComDis site.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @Route("/scomdis/outbreak")
 *
 */
class OutbreakController extends AppController
{
    /**
     * @Route("/", name="scomdis_outbreak")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Outbreak');
        $query = $repo->createQueryBuilder('r')->orderBy('r.weekend', 'DESC')->getQuery();
        
        $pagenator = $this->get('knp_paginator');
        $pagination = $pagenator->paginate($query, $request->
                query->get('page', 1), 100);
      
        return array(
            'pagination' => $pagination,
        );
    }
    
    /**
     * @Route("/new", name="scomdis_outbreak_new")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $displayname = (method_exists($user, 'getAttribute')) ? 
            $displayname = $user->getAttribute('displayname') :
            $displayname = $user->getUsername();
        
        $manager = $this->getDoctrine()->getManager('scomdis');
        
        $outbreak = new Outbreak();
        $outbreak->setReportedBy($displayname);
        
        $form = $this->createForm(new OutbreakType(), $outbreak);
        
        $sentinelSiteRepository = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
        $sentinelSites = $sentinelSiteRepository->findAll();
        
        $clinicRepository = $manager->getRepository('DIISHSComDisBundle:Clinic');
        $clinics = $clinicRepository->findAll();
        
        $syndromeRepository = $manager->getRepository('DIISHSComDisBundle:Syndrome4Outbreak');
        $syndromes = $syndromeRepository->findAll();

        if ($request->isMethod('POST')) {
            $data = $request->request->get($form->getName());
            $form->submit($data);
            
            if ($form->isValid()) {
                try {
                    $outbreakRepository = $manager->getRepository('DIISHSComDisBundle:Outbreak');
                    $outbreakRepository->isExist_EX($outbreak);
                    $request->getSession()->set('scomdis_outbreak/new', $data);
                    return $this->redirect($this->generateUrl('scomdis_outbreak_confirm'));
                } catch (\Exception $e) {
                    $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                }
            }
        }
        
        return array(
            'sentinelSites' => $sentinelSites,
            'clinics'       => $clinics,
            'syndromes'     => $syndromes,
            'form'          => $form->createView(),
        );
    }
    
    /**
     * @Route("/confirm", name="scomdis_outbreak_confirm")
     * @Template()
     */
    public function confirmAction(Request $request)
    {
        $outbreak = new Outbreak();
        
        if (!$this->restoreOutbreakForms($outbreak, array('outbreak',))) {
            return $this->redirect($this->generateUrl('scomdis_outbreak_new'));
        }

        $manager = $this->getDoctrine()->getManager('scomdis');
        $outbreakRepository = $manager->getRepository('DIISHSComDisBundle:Outbreak');
        
        $res = true;
        if ($request->isMethod('POST')) {
            try {
                
                $outbreakRepository->saveOutbreak($outbreak);
                $this->logNewOutbreak($outbreak);
                
                $session = $request->getSession();
                $session->remove('scomdis_outbreak/new');
                $message = 'Complete adding the new outbreak. '.
                            $outbreak->getUniqueTitle();
                $request->getSession()->getFlashBag()->add('success', $message);
                
                return $this->redirect($this->generateUrl('scomdis_outbreak'));
                
            } catch (\Exception $e) {
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                $res = false;
            }
        }
        
        return array(
            'outbreak' => $outbreak,
            'res' => $res,
        );
    }
    
    /**
     * @Route("/{id}/show", name="scomdis_outbreak_show", requirements={"id" = "\d+"})
     * @Template()
     */
    public function showAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Outbreak');
        $outbreak = $repo->find($id);
        
        if (!$outbreak) {
            throw $this->createNotFoundException('No outbreak daily Tally found for id '.$id);
        }
        
        $pre_url = null;
        $session = $request->getSession();
        if ($session->has('outbreak_edit/after_update_url')) {
            $pre_url = $session->get('outbreak_edit/after_update_url');
            $session->remove('outbreak_edit/after_update_url');
        } else {
            $referer = $request->server->get('HTTP_REFERER');
            $pre_url = $referer;
            $session->set('outbreak_edit/before_show_url', $referer);
        }
        
        return array(
            'outbreak'  => $outbreak,
            'pre_url'   => $pre_url,
        );
    }
    
    /**
     * @Route("/{id}/edit", name="scomdis_outbreak_edit", requirements={"id" = "\d+"})
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $outbreakRepository = $manager->getRepository('DIISHSComDisBundle:Outbreak');
        $outbreak = $outbreakRepository->find($id);
        if (!$outbreak) {
            throw $this->createNotFoundException('No outbreak found for id '.$id);
        }
        
        $form = $this->createForm(new OutbreakType(), $outbreak);
        
        $sentinelSiteRepository = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
        $sentinelSites = $sentinelSiteRepository->findAll();
        
        $clinicRepository = $manager->getRepository('DIISHSComDisBundle:Clinic');
        $clinics = $clinicRepository->findAll();
        
        $syndromeRepository = $manager->getRepository('DIISHSComDisBundle:Syndrome4Outbreak');
        $syndromes = $syndromeRepository->findAll();
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $outbreakRepository->saveOutbreak($outbreak);
                $this->logUpdateOutbreak($outbreak);
                    
                $session = $request->getSession();
                $session->getFlashBag()->add('success', 'Update complete.');
                if ($session->has('outbreak_edit/before_show_url')) {
                    $pre_url = $session->get('outbreak_edit/before_show_url');
                    $session->remove('outbreak_edit/before_show_url');
                    $session->set('outbreak_edit/after_update_url', $pre_url);
                }
                return $this->redirect(
                    $this->generateUrl('scomdis_outbreak_show', 
                    array('id' => $outbreak->getId()))
                );

            } catch (\Exception $e) {
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
            }
        }
        
        return array(
            'outbreak'      => $outbreak,
            'sentinelSites' => $sentinelSites,
            'clinics'       => $clinics,
            'syndromes'     => $syndromes,
            'form'          => $form->createView(),
        );
    }
    
    /**
     * @Route("/{id}/delete", name="scomdis_outbreak_delete", requirements={"id" = "\d+"})
     */
    public function deleteAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Outbreak');

        try {
            $outbreak = $repo->find($id);
            $repo->deleteOutbreak($id);
            $this->logDeleteOutbreak($outbreak);
            
            $message = 'Complete deleting the outbreak.';
            $request->getSession()->getFlashBag()->add('success', $message);
            
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
            $this->logError($e->getMessage());
        }
        
        return $this->redirect($this->generateUrl('scomdis_outbreak'));
    }
    
    /**
     * @Route("/search", name="scomdis_outbreak_search")
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
        
        $syndromeRepository = $manager->getRepository('DIISHSComDisBundle:Syndrome4Outbreak');
        $syndromes = $syndromeRepository->findAll();

        $form = $this->createForm(new SearchOutbreakType(), null, array(
            'action' => $this->generateUrl('scomdis_outbreak_search'),
            'method' => 'POST',
        ));
        $form['weekend']->setData($weekend);
        $form['weekOfYear']->setData(strftime('%V', time()));
        $form['year']->setData(strftime('%G', time()));
        
        $form['syndrome']->setData($syndromes);
        
        /*if ($request->getMethod() === 'POST') {
            $data = $request->request->get($form->getName());
            $form->bind($data);*/
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            $weekend = $form['weekend']->getData();
            $clinic = $form['clinic']->getData();
            $syndrome = $form['syndrome']->getData();

            $manager = $this->getDoctrine()->getManager('scomdis');
            $outbreakRepository = $manager->getRepository('DIISHSComDisBundle:Outbreak');
            $outbreak = $outbreakRepository->findOneBy(array(
                'weekend' => $weekend,
                'clinic' => $clinic,
                'syndrome' => $syndrome
            ));

            if ($outbreak) {
                return $this->redirect($this->generateUrl(
                    'scomdis_outbreak_show', array('id' => $outbreak->getId()))
                );
            } else {
                $request->getSession()->getFlashBag()->add('error', "No outbreak found");
            }
        }
        //}
        
        return array(
            'sentinelSites' => $sentinelSites,
            'clinics'       => $clinics,
            'syndromes'     => $syndromes,
            'form'          => $form->createView(),
        );
    }
    
    /**
     * Restore User data
     * @param User $user
     * @param array $formKeys
     * @return boolean
     * @throws \InvalidArgumentException 
     */
    private function restoreOutbreakForms(Outbreak $outbreak, array $formKeys) {
        
        $session = $this->getRequest()->getSession();
        
        $factory = $this->get('form.factory');
        $binder = function($type, $data) use($factory, $outbreak) {
            if (isset($data['_token'])) {
                unset($data['_token']);
            }
            $form = $factory->create($type, $outbreak, array('csrf_protection' => false));
            $form->bind($data);
            
            return $form->isValid();
        };
        
        $valid = true;
        foreach ($formKeys as $formKey) {
            switch ($formKey) {
                case 'outbreak':
                    $valid = $binder(new OutbreakType(),
                                     $session->get('scomdis_outbreak/new'));
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
    
    /**
     * @Route("/{id}/out_daily_tally_report", name="scomdis_outbreak_out_daily_tally_report")
     * @Template
     */
    public function outDailyTallyReportAction(Request $request, $id)
    {
        return array();
    }
    
    public function logNewOutbreak($outbreak)
    {
        $this->logBase($outbreak, 'Add new outbreak.');
    }
    
    public function logUpdateOutbreak($outbreak)
    {
        $this->logBase($outbreak, 'Update outbreak.');
    }
    
    public function logDeleteOutbreak($outbreak)
    {
        $this->logBase($outbreak, 'Delete outbreak.');
    }
    
    protected function logBase($outbreak, $premessage)
    {
        $message = $premessage.' ID: '.
                    $outbreak->getId().' '.
                    $outbreak->getYear().'-'.
                    $outbreak->getWeekOfYear().' '.
                    $outbreak->getClinic().'@'.
                    $outbreak->getSentinelSite().' '.
                    $outbreak->getSyndrome();
     
        $this->logInfo($message);
    }
}
