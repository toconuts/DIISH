<?php

namespace DIISH\SComDisBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use DIISH\SComDisBundle\Entity\SurveillanceTrendCriteria;
use DIISH\SComDisBundle\Entity\SurveillancePredictionCriteria;
use DIISH\SComDisBundle\Entity\SurveillanceCoefficientCriteria;

use DIISH\SComDisBundle\Form\SurveillanceTrendCriteriaType;
use DIISH\SComDisBundle\Form\SurveillancePredictionCriteriaType;
use DIISH\SComDisBundle\Form\SurveillanceCoefficientCriteriaType;

/**
 * ChartController for SComDis site.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @Route("/scomdis/chart")
 */
class ChartController extends AppController
{
    /**
     * @Route("/trend", name="scomdis_chart_trend")
     * @Template
     */
    public function trendAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $syndromeRepository = $manager->getRepository('DIISHSComDisBundle:Syndrome4Surveillance');
        $syndromes = $syndromeRepository->findAll();
        $sentinelSiteRepository = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
        $sentinelSites = $sentinelSiteRepository->findAll();
        $criteria = new SurveillanceTrendCriteria($syndromes, $sentinelSites);
        
        $form = $this->createForm(new SurveillanceTrendCriteriaType(), $criteria, array(
            'action' => $this->generateUrl('scomdis_chart_trend'),
            'method' => 'POST',
        ));
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {

            // Get chart data from service
            $service = $this->get('surveillance.chart_service');
            $lineChart = $service->createTrendChart($criteria);

            return $this->render('DIISHSComDisBundle:Chart:chart.html.twig', array(
                'lineChart' => $lineChart,
                'chartType' => 'trend',
            ));
        }
        
        return array(
            'yearChoices' => $criteria->getYearChoices(),
            'syndromes' => $syndromes,
            'sentinelSites' => $sentinelSites,
            'form' => $form->createView(),
        );
    }
    
    /**
     * @Route("/prediction", name="scomdis_chart_prediction")
     * @Template
     */
    public function predictionAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $syndromeRepositry = $manager->getRepository('DIISHSComDisBundle:Syndrome4Surveillance');
        $syndromes = $syndromeRepositry->findAll();
        $sentinelSiteRepository = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
        $sentinelSites = $sentinelSiteRepository->findAll();
        $criteria = new SurveillancePredictionCriteria($syndromes, $sentinelSites);
        
        $form = $this->createForm(new SurveillancePredictionCriteriaType(), $criteria);
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {

            // Get chart data from service
            $service = $this->get('surveillance.chart_service');
            $lineChart = $service->createPredictionChart($criteria);

            return $this->render('DIISHSComDisBundle:Chart:chart.html.twig', array(
                'lineChart' => $lineChart,
                'chartType' => 'detection',
            ));                
        }
        
        return array(
            'yearChoices' => $criteria->getYearChoices(),
            'syndromes' => $syndromes,
            'sentinelSites' => $sentinelSites,
            'form' => $form->createView(),
        );
    }
    
    /**
     * @Route("/coefficient", name="scomdis_chart_coefficient")
     * @Template
     */
    public function coefficientAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager('scomdis');
        $syndromeRepositry = $manager->getRepository('DIISHSComDisBundle:Syndrome4Surveillance');
        $syndromes = $syndromeRepositry->findAll();
        
        $criteria = new SurveillanceCoefficientCriteria($syndromes);
        
        $form = $this->createForm(new SurveillanceCoefficientCriteriaType(), $criteria, array(
            'action' => $this->generateUrl('scomdis_chart_coefficient'),
            'method' => 'POST',
        ));
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {

            // Get epidemic phase object from service.
            $service = $this->get('surveillance.epidemic_phase_service');
            $epidemicPhase = $service->createSeasonalCoefficient($criteria);
            $messages = $epidemicPhase->getMessages();
            if (count($messages) > 0) {
                $request->getSession()->getFlashBag()->add('warn', "Warnings: " . implode(', ', $messages));
            }

            return $this->render('DIISHSComDisBundle:Chart:seasonalCoefficient.html.twig', array(
                'epidemicPhase' => $epidemicPhase,
                'chartType' => 'coefficient',
            ));

        }
        
        return array(
            'yearChoices' => $criteria->getYearChoices(),
            'syndromes' => $syndromes,
            'form' => $form->createView(),
        );
    }

}
