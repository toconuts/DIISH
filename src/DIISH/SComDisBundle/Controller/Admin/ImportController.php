<?php

namespace DIISH\SComDisBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use DIISH\CommonBundle\Entity\Document;

/**
 * @Route("/admin") 
 * 
 */
class ImportController extends AdminAppController
{
    /**
     * @Route("/import", name="scomdis_admin_import")
     * @Template
     */
    public function importAction(Request $request)
    {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('scomdis_admin_import'))
                ->setMethod('POST')
                ->add('attachment', 'file')
                ->getForm()
        ;
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $file = $form['attachment']->getData();

            $document = new Document();
            $document->setName($file->getClientOriginalName());
            $document->setFile($file);
            $user = $this->get('security.context')->getToken()->getUser();
            $displayname = (method_exists($user, 'getAttribute')) ? 
                $displayname = $user->getAttribute('displayname') :
                $displayname = $user->getUsername();
            $document->setUsername($displayname);

            $manager = $this->getDoctrine()->getManager('common');
            $manager->persist($document);
            $manager->flush();

            // Import
            $service = $this->get('surveillance.dailly_tally_import_service');
            if ($service->import($document)) {
                $message = $service->getImportedRecordsNumber().' Records were successfully imported.';
                $request->getSession()->getFlashBag()->add('success', $message);

                $warnings = $service->getLogWarn();
                foreach ($warnings as $warning) {
                    $request->getSession()->getFlashBag()->add('warn', $warning);
                }

                return $this->redirect($this->generateUrl('scomdis_list'));
            } else {
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                return $this->redirect($this->generateUrl('scomdis_admin_import'));
            }
        }
        
        return array('form' => $form->createView());
    }
}
