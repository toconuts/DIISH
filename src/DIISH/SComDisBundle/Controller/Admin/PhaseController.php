<?php

namespace DIISH\SComDisBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use DIISH\SComDisBundle\Entity\Phase;
use DIISH\SComDisBundle\Form\Admin\Phase\PhaseRegistrationType;
use DIISH\SComDisBundle\Form\Admin\Phase\PhaseEditType;

/**
 * Phase for SComDis Admin site.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @Route("/admin/phase")
 */
class PhaseController extends AdminAppController
{
    /**
     * @Route("/", name="scomdis_admin_phase")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Phase');
        $query = $repo->createQueryBuilder('r')->orderBy('r.id', 'ASC')->getQuery();
        
        $pagenator = $this->get('knp_paginator');
        $pagination = $pagenator->paginate($query, $request->query->get('page', 1), 20);
      
        return array(
            'pagination' => $pagination,
        );
    }
    
    /**
     * @Route("/{id}", name="scomdis_admin_phase_show", requirements={"id" = "\d+"})
     * @Template()
     */
    public function showAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Phase');
        $phase = $repo->find($id);
        if (!$phase) {
            throw $this->createNotFoundException();
        }
        
        $referer = $request->server->get('HTTP_REFERER');
        $request->getSession()->set('scomdis_admin_phase/before_edit_url', $referer);
        
        return array(
            'phase' => $phase,
        );
    }
    
    /**
     * @Route("/register", name="scomdis_admin_phase_register")
     * @Template()
     */
    public function registerAction(Request $request)
    {
        $phase = new Phase();
        $form = $this->createForm(new PhaseRegistrationType(), $phase, array(
            'action' => $this->generateUrl('scomdis_admin_phase_register'),
            'method' => 'POST',
        ));
        
        if ('POST' === $request->getMethod()) {
            $data = $request->request->get($form->getName());
            $form->bind($data);
            if ($form->isValid()) {
                
                $manager = $this->getDoctrine()->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:Phase');
                if ($repo->isExist($phase)) {
                    $request->getSession()->getFlashBag()->add('error', "Phase ID is duplicated.");
                } else {
                    $request->getSession()->set('scomdis_admin_phase/registration', $data);
                    return $this->redirect($this->generateUrl('scomdis_admin_phase_confirm'));
                }
            }
        }
        
        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/confirm", name="scomdis_admin_phase_confirm")
     * @Template()
     */
    public function confirmAction(Request $request)
    {
        $phase = new Phase();
        if (!$this->restorePhaseForms($phase, array('phase_registration'))) {
            return $this->redirect($this->generateUrl('scomdis_admin_phase_registration'));
        }

        if ('POST' === $request->getMethod()) {
            
            try {

                $manager = $this->getDoctrine()->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:Phase');
                $repo->savePhase($phase);

                $session = $request->getSession();
                $session->remove('scomdis_admin_phase/registration');
                $message = "Add Phase. ID: " . $phase->getId();
                $session->getFlashBag()->add('success', $message);
                
                return $this->redirect($this->generateUrl('scomdis_admin_phase'));
                
            } catch (\InvalidArgumentException $e) {
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                return $this->redirect($this->generateUrl('scomdis_admin_phase_register'));
            }
        }
        
        return array(
            'phase' => $phase,
        );
    }
    
    /**
     * @Route("/edit/{id}", name="scomdis_admin_phase_edit", requirements={"id" = "\d+"})
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Phase');
        $phase = $repo->find($id);
        if (!$phase) {
            throw $this->createNotFoundException();
        }
        
        $form = $this->createForm(new PhaseEditType(), $phase, array(
            'action' => $this->generateUrl('scomdis_admin_phase_edit', array('id' => $id)),
            'method' => 'POST',
        ));
        if ('POST' === $request->getMethod()) {
            $data = $request->request->get($form->getName());
            $form->bind($data);
            if ($form->isValid()) {              
                try {

                    $manager = $this->getDoctrine()->getManager('scomdis');
                    $repo = $manager->getRepository('DIISHSComDisBundle:Phase');

                    $repo->updatePhase($phase);

                    $session = $request->getSession();
                    $session->remove('scomdis_admin_phase/registration');
                    
                    return $this->redirect($this->generateUrl('scomdis_admin_phase_update', array('id' => $phase->getId())));

                } catch (\InvalidArgumentException $e) {
                    $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                }
            }
        }

        return array(
            'phase' => $phase,
            'form' => $form->createView(),
        );
    }
    
    /**
     * @Route("/update/{id}", name="scomdis_admin_phase_update", requirements={"id" = "\d+"})
     * @Template()
     */
    public function updateAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Phase');
        $phase = $repo->find($id);
        if (!$phase) {
            throw $this->createNotFoundException();
        }
        
        $pre_url = null;
        $session = $request->getSession();
        if ($session->has('scomdis_admin_phase/before_edit_url')) {
            $pre_url = $session->get('scomdis_admin_phase/before_edit_url');
            $session->remove('scomdis_admin_phase/before_edit_url');
        }
        
        $session->getFlashBag()->add('success', 'Update complete.');
        
        return array(
            'phase' => $phase,
            'pre_url' => $pre_url,
        );
    }

    /**
     * @Route ("/delete/{id}", name="scomdis_admin_phase_delete", requirements={"id" = "\d+"})
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $manager = $this->getDoctrine()->getManager('scomdis');
            $repo = $manager->getRepository('DIISHSComDisBundle:Phase');
            $repo->deletePhase($id);
            
            $message = "Complete deleting phase. id: $id.";
            $request->getSession()->getFlashBag()->add('success', $message);
            
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
        }
        
        return $this->redirect($this->generateUrl('scomdis_admin_phase'));
    }
    
    /**
     * Restore Phase data
     * @param phase $phase
     * @param array $formKeys
     * @return boolean
     * @throws \InvalidArgumentException 
     */
    private function restorePhaseForms(Phase $phase, array $formKeys) {
        
        $session = $this->getRequest()->getSession();
        
        $factory = $this->get('form.factory');
        $binder = function($type, $data) use($factory, $phase) {
            if (isset($data['_token'])) {
                unset($data['_token']);
            }
            $form = $factory->create($type, $phase, array('csrf_protection' => false));
            $form->bind($data);
            
            return $form->isValid();
        };
        
        $valid = true;
        foreach ($formKeys as $formKey) {
            switch ($formKey) {
                case 'phase_registration':
                    $valid = $binder(new PhaseRegistrationType(),
                                     $session->get('scomdis_admin_phase/registration'));
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
