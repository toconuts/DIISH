<?php

namespace DIISH\SComDisBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use DIISH\SComDisBundle\Entity\Syndrome4Outbreak;
use DIISH\SComDisBundle\Form\Admin\Syndrome4Outbreak\Syndrome4OutbreakRegistrationType;
use DIISH\SComDisBundle\Form\Admin\Syndrome4Outbreak\Syndrome4OutbreakEditType;

/**
 * Syndrome4OutbreakController for SComDis Admin site.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @Route("/admin/syndrome4outbreak")
 */
class Syndrome4OutbreakController extends AdminAppController
{
    /**
     * @Route("/", name="scomdis_admin_syndrome4outbreak")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Syndrome4Outbreak');
        $query = $repo->createQueryBuilder('r')->orderBy('r.id', 'ASC')->getQuery();
        
        $pagenator = $this->get('knp_paginator');
        $pagination = $pagenator->paginate($query, $request->query->get('page', 1), 20);
      
        return array(
            'pagination' => $pagination,
        );
    }
    
    /**
     * @Route("/{id}", name="scomdis_admin_syndrome4outbreak_show", requirements={"id" = "\d+"})
     * @Template()
     */
    public function showAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Syndrome4Outbreak');
        $syndrome = $repo->find($id);
        if (!$syndrome) {
            throw $this->createNotFoundException();
        }
        
        $referer = $request->server->get('HTTP_REFERER');
        $request->getSession()->set('scomdis_admin_syndrome4outbreak/before_edit_url', $referer);
        
        return array(
            'syndrome' => $syndrome,
        );
    }
    
    /**
     * @Route("/register", name="scomdis_admin_syndrome4outbreak_register")
     * @Template()
     */
    public function registerAction(Request $request)
    {
        $syndrome = new Syndrome4Outbreak();
        $form = $this->createForm(new Syndrome4OutbreakRegistrationType(), $syndrome, array(
            'action' => $this->generateUrl('scomdis_admin_syndrome4outbreak_register'),
            'method' => 'POST',
        ));
        
        if ($request->isMethod('POST')) {
            $data = $request->request->get($form->getName());
            $form->submit($data);
            
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:Syndrome4Outbreak');
                if ($repo->isExist($syndrome)) {
                    $request->getSession()->getFlashBag()->add('error', "Syndrome ID is duplicated.");
                } else if (!$repo->isAvailableDispID($syndrome)) {
                    $request->getSession()->getFlashBag()->add('error', "Display ID is already used.");
                } else {
                    $request->getSession()->set('scomdis_admin_syndrome4outbreak/registration', $data);
                    return $this->redirect($this->generateUrl('scomdis_admin_syndrome4outbreak_confirm'));
                }
            }
        }
        
        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/confirm", name="scomdis_admin_syndrome4outbreak_confirm")
     * @Template()
     */
    public function confirmAction(Request $request)
    {
        $syndrome = new Syndrome4Outbreak();
        if (!$this->restoreSyndromeForms($syndrome, array('syndrome4outbreak_registration'))) {
            return $this->redirect($this->generateUrl('scomdis_admin_syndrome4outbreak_registration'));
        }

        if ($request->isMethod('POST')) {
            
            try {

                $manager = $this->getDoctrine()->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:Syndrome4Outbreak');
                $repo->saveSyndrome($syndrome);

                $session = $request->getSession();
                $session->remove('scomdis_admin_syndrome4outbreak/registration');
                $message = "Add Syndrome. ID: " . $syndrome->getId();
                $session->getFlashBag()->add('success', $message);
                return $this->redirect($this->generateUrl('scomdis_admin_syndrome4outbreak'));
                
            } catch (\Exception $e) {
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                return $this->redirect($this->generateUrl('scomdis_admin_syndrome4outbreak_register'));
            }
        }
        
        return array(
            'syndrome' => $syndrome,
        );
    }
    
    /**
     * @Route("/edit/{id}", name="scomdis_admin_syndrome4outbreak_edit", requirements={"id" = "\d+"})
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Syndrome4Outbreak');
        $syndrome = $repo->find($id);
        if (!$syndrome) {
            throw $this->createNotFoundException();
        }
        
        $form = $this->createForm(new Syndrome4OutbreakEditType(), $syndrome, array(
            'action' => $this->generateUrl('scomdis_admin_syndrome4outbreak_edit', array('id' => $id)),
            'method' => 'POST',
        ));
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {              
            try {

                $manager = $this->getDoctrine()->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:Syndrome4Outbreak');

                if ($repo->isAvailableDispID($syndrome)) {
                    $repo->updateSyndrome($syndrome);

                    $session = $request->getSession();
                    $session->remove('scomdis_admin_syndrome4outbreak/registration');

                    return $this->redirect($this->generateUrl('scomdis_admin_syndrome4outbreak_update', array('id' => $syndrome->getId())));
                } else {
                    $request->getSession()->getFlashBag()->add('error', "Display ID is already used.");
                }
            } catch (\Exception $e) {
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
            }
        }

        return array(
            'syndrome' => $syndrome,
            'form' => $form->createView(),
        );
    }
    
    /**
     * @Route("/update/{id}", name="scomdis_admin_syndrome4outbreak_update", requirements={"id" = "\d+"})
     * @Template()
     */
    public function updateAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:Syndrome4Outbreak');
        $syndrome = $repo->find($id);
        if (!$syndrome) {
            throw $this->createNotFoundException();
        }
        
        $pre_url = null;
        $session = $request->getSession();
        if ($session->has('scomdis_admin_syndrome4outbreak/before_edit_url')) {
            $pre_url = $session->get('scomdis_admin_syndrome4outbreak/before_edit_url');
            $session->remove('scomdis_admin_syndrome/before_edit_url');
        }
        
        $session->getFlashBag()->add('success', 'Update complete.');
        
        return array(
            'syndrome' => $syndrome,
            'pre_url' => $pre_url,
        );
    }

    /**
     * @Route ("/delete/{id}", name="scomdis_admin_syndrome4outbreak_delete", requirements={"id" = "\d+"})
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $manager = $this->getDoctrine()->getManager('scomdis');
            $repo = $manager->getRepository('DIISHSComDisBundle:Syndrome4Outbreak');
            $repo->deleteSyndrome($id);
            
            $message = "Complete deleting syndrome. id: $id.";
            $request->getSession()->getFlashBag()->add('success', $message);
            
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
        }
        
        return $this->redirect($this->generateUrl('scomdis_admin_syndrome4outbreak'));
    }
    
    /**
     * Restore Syndrome4Outbreak data
     * 
     * @param Syndrome4Outbreak $syndrome
     * @param array $formKeys
     * @return boolean
     * @throws \InvalidArgumentException 
     */
    private function restoreSyndromeForms(Syndrome4Outbreak $syndrome, array $formKeys) {
        
        $session = $this->getRequest()->getSession();
        
        $factory = $this->get('form.factory');
        $binder = function($type, $data) use($factory, $syndrome) {
            if (isset($data['_token'])) {
                unset($data['_token']);
            }
            $form = $factory->create($type, $syndrome, array('csrf_protection' => false));
            $form->bind($data);
            
            return $form->isValid();
        };
        
        $valid = true;
        foreach ($formKeys as $formKey) {
            switch ($formKey) {
                case 'syndrome4outbreak_registration':
                    $valid = $binder(new Syndrome4OutbreakRegistrationType(),
                                     $session->get('scomdis_admin_syndrome4outbreak/registration'));
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
