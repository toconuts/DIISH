<?php

namespace DIISH\SComDisBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use DIISH\SComDisBundle\Entity\SurveillanceCoefficientCriteria;

use DIISH\SComDisBundle\Form\SurveillanceCoefficientCriteriaType;

/**
 * @Route("/socomdis/map") 
 * 
 */
class MapController extends AppController
{
    /**
     * @Route("/epidemic_phase", name="scomdis_map_epidemic_phase")
     * @Template()
     */
    public function epidemicPhaseAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $syndromeRepositry = $manager->getRepository('DIISHSComDisBundle:Syndrome4Surveillance');
        $syndromes = $syndromeRepositry->findAll();
        
        $criteria = new SurveillanceCoefficientCriteria($syndromes);
        $form = $this->createForm(new SurveillanceCoefficientCriteriaType(), $criteria);
        
        if ($request->getMethod() === 'POST') {
            $data = $request->request->get($form->getName());
            $form->bind($data);
            if ($form->isValid()) {
                
                // Get epidemic phase object from service.
                $service = $this->get('surveillance.epidemic_phase_service');
                $epidemicPhase = $service->createEpidemicPhase($criteria);
                $messages = $epidemicPhase->getMessages();
                if (count($messages) > 0) {
                    $request->getSession()->getFlashBag()->add('warn', "Warnings: " . implode(', ', $messages));
                }
                
                return $this->render('DIISHSComDisBundle:Map:map_epidemic_phase.html.twig', array(
                    'epidemicPhase' => $epidemicPhase,
                ));
            }
        }
        
        return array(
            'yearChoices' => $criteria->getYearChoices(),
            'syndromes' => $syndromes,
            'form' => $form->createView(),
        );
    }
}
