<?php

namespace DIISH\SComDisBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use DIISH\SComDisBundle\Entity\SentinelSite;
use DIISH\SComDisBundle\Form\Admin\SentinelSite\SentinelSiteRegistrationType;
use DIISH\SComDisBundle\Form\Admin\SentinelSite\SentinelSiteEditType;

/**
 * SentinelSiteController for SComDis Admin site.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @Route("/admin/sentinel_site")
 */
class SentinelSiteController extends AdminAppController
{
    /**
     * @Route("/", name="scomdis_admin_sentinel_site")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
        $query = $repo->createQueryBuilder('r')->orderBy('r.id', 'ASC')->getQuery();
        
        $pagenator = $this->get('knp_paginator');
        $pagination = $pagenator->paginate($query, $request->query->get('page', 1), 20);
      
        return array(
            'pagination' => $pagination,
        );
    }
    
    /**
     * @Route("/{id}", name="scomdis_admin_sentinel_site_show", requirements={"id" = "\d+"})
     * @Template()
     */
    public function showAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
        $sentinelSite = $repo->find($id);
        if (!$sentinelSite) {
            throw $this->createNotFoundException();
        }
        
        $referer = $request->server->get('HTTP_REFERER');
        $request->getSession()->set('scomdis_admin_sentinel_site/before_edit_url', $referer);
        
        return array(
            'sentinelSite' => $sentinelSite,
        );
    }
    
    /**
     * @Route("/register", name="scomdis_admin_sentinel_site_register")
     * @Template()
     */
    public function registerAction(Request $request)
    {
        $sentinelSite = new SentinelSite();
        $form = $this->createForm(new SentinelSiteRegistrationType(), $sentinelSite);
        
        if ('POST' === $request->getMethod()) {
            $data = $request->request->get($form->getName());
            $form->bind($data);
            if ($form->isValid()) {
                
                $manager = $this->getDoctrine()->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
                if (!$repo->isExist($sentinelSite)) {
                    $request->getSession()->set('scomdis_admin_sentinel_site/registration', $data);
                    return $this->redirect($this->generateUrl('scomdis_admin_sentinel_site_confirm'));
                } else {
                    $request->getSession()->getFlashBag()->add('error', "Sentinel Site ID is duplicated.");
                }
            }
        }
        
        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/confirm", name="scomdis_admin_sentinel_site_confirm")
     * @Template()
     */
    public function confirmAction(Request $request)
    {
        $sentinelSite = new SentinelSite();
        if (!$this->restoreSentinelSiteForms($sentinelSite, array('sentinel_site_registration'))) {
            return $this->redirect($this->generateUrl('scomdis_admin_sentinel_site_registration'));
        }

        if ('POST' === $request->getMethod()) {
            
            try {

                $manager = $this->getDoctrine()->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
                $repo->saveSentinelSite($sentinelSite);

                $session = $request->getSession();
                $session->remove('scomdis_admin_sentinel_site/registration');
                $message = "Add sentinel site. ID: " . $sentinelSite->getId();
                $session->getFlashBag()->add('success', $message);
                
                return $this->redirect($this->generateUrl('scomdis_admin_sentinel_site'));
                
            } catch (\InvalidArgumentException $e) {
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                return $this->redirect($this->generateUrl('scomdis_admin_sentinel_site_register'));
            }
        }
        
        return array(
            'sentinelSite' => $sentinelSite,
        );
    }
    
    /**
     * @Route("/edit/{id}", name="scomdis_admin_sentinel_site_edit", requirements={"id" = "\d+"})
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
        $sentinelSite = $repo->find($id);
        if (!$sentinelSite) {
            throw $this->createNotFoundException();
        }
        
        $form = $this->createForm(new SentinelSiteEditType(), $sentinelSite);
        if ('POST' === $request->getMethod()) {
            $data = $request->request->get($form->getName());
            $form->bind($data);
            if ($form->isValid()) {                
                try {
                    
                    $manager = $this->getDoctrine()->getManager('scomdis');
                    $repo = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
                    $repo->updateSentinelSite($sentinelSite);

                    $session = $request->getSession();
                    $session->remove('scomdis_admin_sentinel_site/registration');
                    
                    return $this->redirect($this->generateUrl('scomdis_admin_sentinel_site_update', array('id' => $sentinelSite->getId())));

                } catch (\InvalidArgumentException $e) {
                    $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                }
            }
        }

        return array(
            'sentinelSite' => $sentinelSite,
            'form' => $form->createView(),
        );
    }
    
    /**
     * @Route("/update/{id}", name="scomdis_admin_sentinel_site_update", requirements={"id" = "\d+"})
     * @Template()
     */
    public function updateAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
        $sentinelSite = $repo->find($id);
        if (!$sentinelSite) {
            throw $this->createNotFoundException();
        }
        
        $pre_url = null;
        $session = $request->getSession();
        if ($session->has('scomdis_admin_sentinel_site/before_edit_url')) {
            $pre_url = $session->get('scomdis_admin_sentinel_site/before_edit_url');
            $session->remove('scomdis_admin_sentinel_site/before_edit_url');
        }
        $session->getFlashBag()->add('success', 'Update complete.');
        
        return array(
            'sentinelSite' => $sentinelSite,
            'pre_url' => $pre_url,
        );
    }

    /**
     * @Route ("/delete/{id}", name="scomdis_admin_sentinel_site_delete", requirements={"id" = "\d+"})
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $manager = $this->getDoctrine()->getManager('scomdis');
            $repo = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
            $repo->deleteSentinelSite($id);
            
            $message = "Complete deleting sentinel site. id: $id.";
            $request->getSession()->getFlashBag()->add('success', $message);
            
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
        }
        
        return $this->redirect($this->generateUrl('scomdis_admin_sentinel_site'));
    }
    
    /**
     * Restore SentinelSite data
     * @param SentinelSite $sentinelSite
     * @param array $formKeys
     * @return boolean
     * @throws \InvalidArgumentException 
     */
    private function restoreSentinelSiteForms(SentinelSite $sentinelSite, array $formKeys) {
        
        $session = $this->getRequest()->getSession();
        
        $factory = $this->get('form.factory');
        $binder = function($type, $data) use($factory, $sentinelSite) {
            if (isset($data['_token'])) {
                unset($data['_token']);
            }
            $form = $factory->create($type, $sentinelSite, array('csrf_protection' => false));
            $form->bind($data);
            
            return $form->isValid();
        };
        
        $valid = true;
        foreach ($formKeys as $formKey) {
            switch ($formKey) {
                case 'sentinel_site_registration':
                    $valid = $binder(new SentinelSiteRegistrationType(),
                                     $session->get('scomdis_admin_sentinel_site/registration'));
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
}
