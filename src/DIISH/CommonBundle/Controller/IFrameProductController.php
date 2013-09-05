<?php

namespace DIISH\CommonBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * IFrameProductController.
 * 
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @Route("/products")
 */
class IFrameProductController extends AppController
{
    /**
     * @Route("/{productname}", name="products")
     * @Template
     */
    public function indexAction($productname)
    {
        $productName = null;
        $href = null;
        switch ($productname) {
            case "openmrs":
                $productName = "OpenMRS";
                $href = "http://192.168.118.84:8080/openmrs";
                break;
            case "ddms":
                $productName = "DDMS";
                $href = "http://192.168.118.42:8080/share";
                break;
            case "health_res":
                $productName = "HealthRes";
                $href = "http://192.168.118.57/healthres";
                break;
            case "public_health_res":
                $productName = "Public HealthRes";
                $href = "http://192.168.118.57/publichealthres";
                break;
            case "cmssm":
                $productName = "CMSSM";
                $href = "http://192.168.118.57/cmssm";
                break;
            case "mortality":
                $productName = "Mortality";
                $href = "http://192.168.118.46/reports/mortality";
                break;
            default:
                throw $this->createNotFoundException();
        }
        return array(
            'productName' => $productName,
            'menuName' => $productname,
            'href' => $href,
        );
    }

}
