<?php

namespace DIISH\SComDisBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use DIISH\SComDisBundle\Entity\District;
use DIISH\SComDisBundle\Form\Admin\District\DistrictRegistrationType;
use DIISH\SComDisBundle\Form\Admin\District\DistrictEditType;

/**
 * DistrictController for SComDis Admin site.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @Route("/admin/district")
 */
class DistrictController extends AdminAppController
{
    /**
     * @Route("/", name="scomdis_admin_district")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:District');
        $query = $repo->createQueryBuilder('r')->orderBy('r.id', 'ASC')->getQuery();
        
        $pagenator = $this->get('knp_paginator');
        $pagination = $pagenator->paginate($query, $request->query->get('page', 1), 20);
      
        return array(
            'pagination' => $pagination,
        );
    }
    
    /**
     * @Route("/{id}", name="scomdis_admin_district_show", requirements={"id" = "\d+"})
     * @Template()
     */
    public function showAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:District');
        $district = $repo->find($id);
        if (!$district) {
            throw $this->createNotFoundException();
        }
        
        $referer = $request->server->get('HTTP_REFERER');
        $request->getSession()->set('scomdis_admin_district/before_edit_url', $referer);
        
        return array(
            'district' => $district,
        );
    }
    
    /**
     * @Route("/register", name="scomdis_admin_district_register")
     * @Template()
     */
    public function registerAction(Request $request)
    {
        $district = new District();
        $form = $this->createForm(new DistrictRegistrationType(), $district, array(
            'action' => $this->generateUrl('scomdis_admin_district_register'),
            'method' => 'POST',
        ));
        
        if ('POST' === $request->getMethod()) {
            $data = $request->request->get($form->getName());
            $form->bind($data);
            if ($form->isValid()) {
                
                $manager = $this->getDoctrine()->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:District');
                if (!$repo->isExist($district)) {
                    $request->getSession()->set('scomdis_admin_district/registration', $data);
                    return $this->redirect($this->generateUrl('scomdis_admin_district_confirm'));
                } else {
                    $request->getSession()->getFlashBag()->add('error', "District ID is duplicated.");
                }
            }
        }
        
        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/confirm", name="scomdis_admin_district_confirm")
     * @Template()
     */
    public function confirmAction(Request $request)
    {
        $district = new District();
        if (!$this->restoreDistrictForms($district, array('district_registration'))) {
            return $this->redirect($this->generateUrl('scomdis_admin_district_registration'));
        }

        if ('POST' === $request->getMethod()) {
            
            try {

                $manager = $this->getDoctrine()->getManager('scomdis');
                $repo = $manager->getRepository('DIISHSComDisBundle:District');
                $repo->saveDistrict($district);

                $session = $request->getSession();
                $session->remove('scomdis_admin_district/registration');
                $message = "Add District. ID: " . $district->getId();
                $session->getFlashBag()->add('success', $message);
                
                return $this->redirect($this->generateUrl('scomdis_admin_district'));
                
            } catch (\InvalidArgumentException $e) {
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                return $this->redirect($this->generateUrl('scomdis_admin_district_register'));
            }
        }
        
        return array(
            'district' => $district,
        );
    }
    
    /**
     * @Route("/edit/{id}", name="scomdis_admin_district_edit", requirements={"id" = "\d+"})
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:District');
        $district = $repo->find($id);
        if (!$district) {
            throw $this->createNotFoundException();
        }
        
        $form = $this->createForm(new DistrictEditType(), $district, array(
            'action' => $this->generateUrl('scomdis_admin_district_edit', array('id' => $id)),
            'method' => 'POST',
        ));
        
        if ('POST' === $request->getMethod()) {
            $data = $request->request->get($form->getName());
            $form->bind($data);
            if ($form->isValid()) {                
                try {
                    
                    $manager = $this->getDoctrine()->getManager('scomdis');
                    $repo = $manager->getRepository('DIISHSComDisBundle:District');
                    $repo->updateDistrict($district);

                    $session = $request->getSession();
                    $session->remove('scomdis_admin_district/registration');
                    
                    return $this->redirect($this->generateUrl('scomdis_admin_district_update', array('id' => $district->getId())));

                } catch (\InvalidArgumentException $e) {
                    $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                }
            }
        }

        return array(
            'district' => $district,
            'form' => $form->createView(),
        );
    }
    
    /**
     * @Route("/update/{id}", name="scomdis_admin_district_update", requirements={"id" = "\d+"})
     * @Template()
     */
    public function updateAction(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $repo = $manager->getRepository('DIISHSComDisBundle:District');
        $district = $repo->find($id);
        if (!$district) {
            throw $this->createNotFoundException();
        }
        
        $pre_url = null;
        $session = $request->getSession();
        if ($session->has('scomdis_admin_district/before_edit_url')) {
            $pre_url = $session->get('scomdis_admin_district/before_edit_url');
            $session->remove('scomdis_admin_district/before_edit_url');
        }
        $session->getFlashBag()->add('success', 'Update complete.');
        
        return array(
            'district' => $district,
            'pre_url' => $pre_url,
        );
    }

    /**
     * @Route ("/delete/{id}", name="scomdis_admin_district_delete", requirements={"id" = "\d+"})
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $manager = $this->getDoctrine()->getManager('scomdis');
            $repo = $manager->getRepository('DIISHSComDisBundle:District');
            $repo->deleteDistrict($id);
            
            $message = "Complete deleting district. id: $id.";
            $request->getSession()->getFlashBag()->add('success', $message);
            
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());
        }
        
        return $this->redirect($this->generateUrl('scomdis_admin_district'));
    }
    
    /**
     * Restore District data
     * @param District $district
     * @param array $formKeys
     * @return boolean
     * @throws \InvalidArgumentException 
     */
    private function restoreDistrictForms(District $district, array $formKeys) {
        
        $session = $this->getRequest()->getSession();
        
        $factory = $this->get('form.factory');
        $binder = function($type, $data) use($factory, $district) {
            if (isset($data['_token'])) {
                unset($data['_token']);
            }
            $form = $factory->create($type, $district, array('csrf_protection' => false));
            $form->bind($data);
            
            return $form->isValid();
        };
        
        $valid = true;
        foreach ($formKeys as $formKey) {
            switch ($formKey) {
                case 'district_registration':
                    $valid = $binder(new DistrictRegistrationType(),
                                     $session->get('scomdis_admin_district/registration'));
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
