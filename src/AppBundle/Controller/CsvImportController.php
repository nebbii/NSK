<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class CsvImportController extends Controller
{
    /**
     * @Route("/importCsv")
     */
    public function importCsvAction()
    {
        return $this->render('AppBundle:CsvImport:import_csv.html.twig', array(
            // ...
        ));
    }

}
