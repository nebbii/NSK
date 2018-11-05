<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class CsvImportController extends Controller
{
    /**
     * @Route("/importCsv", name="importCsv")
     */
    public function importCsvAction()
    {
        return $this->render('AppBundle:CsvImport:import_csv.html.twig', array(
            // ...
        ));
    }

}
