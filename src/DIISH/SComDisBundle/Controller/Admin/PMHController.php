<?php

namespace DIISH\SComDisBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use DIISH\SComDisBundle\Entity\PMH;
use DIISH\SComDisBundle\Form\Admin\PMH\PMHRegistrationType;
use DIISH\SComDisBundle\Form\Admin\PMH\PMHEditType;

/**
 * PMH for SComDis Admin site.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @Route("/admin/pmh")
 */
class PMHController extends AdminAppController
{
    /**
     * @Route("/", name="scomdis_admin_pmh")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:PMH');
        $query = $repo->createQueryBuilder('r')->orderBy('r.id', 'ASC')->getQuery();
        
        $pagenator = $this->get('knp_paginator');
        $pagination = $pagenator->paginate($query, $request->query->get('page', 1), 20);
      
        return array(
            'pagination' => $pagination,
        );
    }
    
    /**
     * @Route("/{id}", name="scomdis_admin_pmh_show", requirements={"id" = "\d+"})
     * @Template()
     */
    public function showAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:PMH');
        $pmh = $repo->find($id);
        if (!$pmh) {
            throw $this->createNotFoundException();
        }
        
        $referer = $request->server->get('HTTP_REFERER');
        $request->getSession()->set('scomdis_admin_pmh/before_edit_url', $referer);
        
        return array(
            'pmh' => $pmh,
        );
    }
    
    /**
     * @Route("/register", name="scomdis_admin_pmh_register")
     * @Template()
     */
    public function registerAction(Request $request)
    {
        $pmh = new PMH();
        $form = $this->createForm(new PMHRegistrationType(), $pmh, array(
            'action' => $this->generateUrl('scomdis_admin_pmh_register'),
            'method' => 'POST',
        ));
        
        if ($request->isMethod('POST')) {
            $data = $request->request->get($form->getName());
            $form->submit($data);
            
            if ($form->isValid()) {    
                $manager = $this->getDoctrine()->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:PMH');
                if ($repo->isExist($pmh)) {
                    $message = $pmh->getClinic()->getName() . " already exists.";
                    $request->getSession()->getFlashBag()->add('error', $message);
                } else {
                    $request->getSession()->set('scomdis_admin_pmh/registration', $data);
                    return $this->redirect($this->generateUrl('scomdis_admin_pmh_confirm'));
                }
            }
        }
        
        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/confirm", name="scomdis_admin_pmh_confirm")
     * @Template()
     */
    public function confirmAction(Request $request)
    {
        $pmh = new PMH();
        if (!$this->restorePMHForms($pmh, array('pmh_registration'))) {
            return $this->redirect($this->generateUrl('scomdis_admin_pmh_registration'));
        }

        if ($request->isMethod('POST')) {
            
            try {

                $manager = $this->getDoctrine()->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:PMH');
                $repo->savePMH($pmh);

                $session = $request->getSession();
                $session->remove('scomdis_admin_pmh/registration');
                $message = "Add PMH. ID: " . $pmh->getId();
                $session->getFlashBag()->add('success', $message);
                
                return $this->redirect($this->generateUrl('scomdis_admin_pmh'));
                
            } catch (\Exception $e) {
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                return $this->redirect($this->generateUrl('scomdis_admin_pmh_register'));
            }
        }
        
        return array(
            'pmh' => $pmh,
        );
    }
    
    /**
     * @Route("/edit/{id}", name="scomdis_admin_pmh_edit", requirements={"id" = "\d+"})
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:PMH');
        $pmh = $repo->find($id);
        if (!$pmh) {
            throw $this->createNotFoundException();
        }
        
        $form = $this->createForm(new PMHEditType(), $pmh, array(
            'action' => $this->generateUrl('scomdis_admin_pmh_edit', array('id' => $id)),
            'method' => 'POST',
        ));
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            if ($repo->isExist($pmh, true)) {
                $message = $pmh->getClinic()->getName() . " already exists.";
                $request->getSession()->getFlashBag()->add('error', $message);
            } else {
                try {

                    $manager = $this->getDoctrine()->getManager('scomdis');
                    $repo = $manager->getRepository('DIISHSComDisBundle:Pmh');

                    $repo->updatePMH($pmh);

                    $session = $request->getSession();
                    $session->remove('scomdis_admin_pmh/registration');

                    return $this->redirect($this->generateUrl('scomdis_admin_pmh_update', array('id' => $pmh->getId())));

                } catch (\Exception $e) {
                    $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                }
            }
        }

        return array(
            'pmh' => $pmh,
            'form' => $form->createView(),
        );
    }
    
    /**
     * @Route("/update/{id}", name="scomdis_admin_pmh_update", requirements={"id" = "\d+"})
     * @Template()
     */
    public function updateAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:PMH');
        $pmh = $repo->find($id);
        if (!$pmh) {
            throw $this->createNotFoundException();
        }
        
        $pre_url = null;
        $session = $request->getSession();
        if ($session->has('scomdis_admin_pmh/before_edit_url')) {
            $pre_url = $session->get('scomdis_admin_pmh/before_edit_url');
            $session->remove('scomdis_admin_pmh/before_edit_url');
        }
        $session->getFlashBag()->add('success', 'Update complete.');
        
        return array(
            'pmh' => $pmh,
            'pre_url' => $pre_url,
        );
    }

    /**
     * @Route ("/delete/{id}", name="scomdis_admin_pmh_delete", requirements={"id" = "\d+"})
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $manager = $this->getDoctrine()->getManager('scomdis');
            $repo = $manager->getRepository('DIISHSComDisBundle:PMH');
            $repo->deletePMH($id);
            
            $message = "Complete deleting pmh. id: $id.";
            $request->getSession()->getFlashBag()->add('success', $message);
            
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
        }
        
        return $this->redirect($this->generateUrl('scomdis_admin_pmh'));
    }
    
    /**
     * Restore PMH data
     * 
     * @param PMH $pmh
     * @param array $formKeys
     * @return boolean
     * @throws \InvalidArgumentException 
     */
    private function restorePMHForms(PMH $pmh, array $formKeys) {
        
        $session = $this->getRequest()->getSession();
        
        $factory = $this->get('form.factory');
        $binder = function($type, $data) use($factory, $pmh) {
            if (isset($data['_token'])) {
                unset($data['_token']);
            }
            $form = $factory->create($type, $pmh, array('csrf_protection' => false));
            $form->bind($data);
            
            return $form->isValid();
        };
        
        $valid = true;
        foreach ($formKeys as $formKey) {
            switch ($formKey) {
                case 'pmh_registration':
                    $valid = $binder(new PMHRegistrationType(),
                                     $session->get('scomdis_admin_pmh/registration'));
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
