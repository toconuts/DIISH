<?php

namespace DIISH\SComDisBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use DIISH\SComDisBundle\Entity\Clinic;
use DIISH\SComDisBundle\Form\Admin\Clinic\ClinicRegistrationType;
use DIISH\SComDisBundle\Form\Admin\Clinic\ClinicEditType;

/**
 * ClinicController for SComDis Admin site.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @Route("/admin/clinic")
 */
class ClinicController extends AdminAppController
{
    /**
     * @Route("/", name="scomdis_admin_clinic")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Clinic');
        $query = $repo->createQueryBuilder('r')->orderBy('r.id', 'ASC')->getQuery();
        
        $pagenator = $this->get('knp_paginator');
        $pagination = $pagenator->paginate($query, $request->query->get('page', 1), 100);
      
        return array(
            'pagination' => $pagination,
        );
    }
    
    /**
     * @Route("/{id}", name="scomdis_admin_clinic_show", requirements={"id" = "\d+"})
     * @Template()
     */
    public function showAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Clinic');
        $clinic = $repo->find($id);
        if (!$clinic) {
            throw $this->createNotFoundException();
        }
        
        $referer = $request->server->get('HTTP_REFERER');
        $request->getSession()->set('scomdis_admin_clinic/before_edit_url', $referer);
        
        return array(
            'clinic' => $clinic,
        );
    }
    
    /**
     * @Route("/register", name="scomdis_admin_clinic_register")
     * @Template()
     */
    public function registerAction(Request $request)
    {
        $clinic = new Clinic();
        $form = $this->createForm(new ClinicRegistrationType(), $clinic, array(
            'action' => $this->generateUrl('scomdis_admin_clinic_register'),
            'method' => 'POST',
        ));

       if ($request->isMethod('POST')) {
            $data = $request->request->get($form->getName());
            $form->submit($data);
            
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:Clinic');
                if (!$repo->isExist($clinic)) {
                    $request->getSession()->set('scomdis_admin_clinic/registration', $data);                    
                    return $this->redirect($this->generateUrl('scomdis_admin_clinic_confirm'));
                } else {
                    $request->getSession()->getFlashBag()->add('error', "Clinic ID is duplicated.");
                }
            }
        }
        
        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/confirm", name="scomdis_admin_clinic_confirm")
     * @Template()
     */
    public function confirmAction(Request $request)
    {
        $clinic = new Clinic();
        if (!$this->restoreClinicForms($clinic, array('clinic_registration'))) {
            return $this->redirect($this->generateUrl('scomdis_admin_clinic_registration'));
        }

        if ($request->isMethod('POST')) {
            
            try {

                $manager = $this->get('doctrine')->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:Clinic');
                $repo->saveClinic($clinic);

                $session = $request->getSession();
                $session->remove('scomdis_admin_clinic/registration');
                $message = "Add clinic. ID: " . $clinic->getId();
                $session->getFlashBag()->add('success', $message);
                
                return $this->redirect($this->generateUrl('scomdis_admin_clinic'));
                
            } catch (\Exception $e) {
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                return $this->redirect($this->generateUrl('scomdis_admin_clinic_register'));
            }
        }
        
        return array(
            'clinic' => $clinic,
        );
    }
    
    /**
     * @Route("/edit/{id}", name="scomdis_admin_clinic_edit", requirements={"id" = "\d+"})
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        $manager = $this->get('doctrine')->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Clinic');
        $clinic = $repo->find($id);
        if (!$clinic) {
            throw $this->createNotFoundException();
        }
        
        $form = $this->createForm(new ClinicEditType(), $clinic, array(
            'action' => $this->generateUrl('scomdis_admin_clinic_edit', array('id' => $id)),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {            
            try {

                $manager = $this->get('doctrine')->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:Clinic');
                $repo->updateClinic($clinic);

                $session = $request->getSession();
                $session->remove('scomdis_admin_clinic/registration');

                return $this->redirect($this->generateUrl('scomdis_admin_clinic_update', array('id' => $clinic->getId())));

            } catch (\Exception $e) {
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
            }
        }

        return array(
            'clinic' => $clinic,
            'form' => $form->createView(),
        );
    }
    
    /**
     * @Route("/update/{id}", name="scomdis_admin_clinic_update", requirements={"id" = "\d+"})
     * @Template()
     */
    public function updateAction(Request $request, $id)
    {
        $manager = $this->get('doctrine')->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Clinic');
        $clinic = $repo->find($id);
        if (!$clinic) {
            throw $this->createNotFoundException();
        }
        
        $pre_url = null;
        $session = $request->getSession();
        if ($session->has('scomdis_admin_clinic/before_edit_url')) {
            $pre_url = $session->get('scomdis_admin_clinic/before_edit_url');
            $session->remove('scomdis_admin_clinic/before_edit_url');
        }
        
        $session->getFlashBag()->add('success', 'Update complete.');
        
        return array(
            'clinic' => $clinic,
            'pre_url' => $pre_url,
        );
    }

    /**
     * @Route ("/delete/{id}", name="scomdis_admin_clinic_delete", requirements={"id" = "\d+"})
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $manager = $this->getDoctrine()->getManager('scomdis');
            $repo = $manager->getRepository('DIISHSComDisBundle:Clinic');
            $repo->deleteClinic($id);
            
            $message = "Complete deleting clinic. id: $id.";
            $request->getSession()->getFlashBag()->add('success', $message);
            
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
        }
        
        return $this->redirect($this->generateUrl('scomdis_admin_clinic'));
    }
    
    /**
     * Restore Clinic data
     * 
     * @param Clinic $clinic
     * @param array $formKeys
     * @return boolean
     * @throws \InvalidArgumentException 
     */
    private function restoreClinicForms(Clinic $clinic, array $formKeys) {
        
        $session = $this->getRequest()->getSession();
        
        $factory = $this->get('form.factory');
        $binder = function($type, $data) use($factory, $clinic) {
            if (isset($data['_token'])) {
                unset($data['_token']);
            }
            $form = $factory->create($type, $clinic, array('csrf_protection' => false));
            $form->bind($data);
            
            return $form->isValid();
        };
        
        $valid = true;
        foreach ($formKeys as $formKey) {
            switch ($formKey) {
                case 'clinic_registration':
                    $valid = $binder(new ClinicRegistrationType(),
                                     $session->get('scomdis_admin_clinic/registration'));
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
