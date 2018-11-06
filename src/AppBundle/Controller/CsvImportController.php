<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CsvImportController extends Controller
{
    /**
     * @Route("/importCsv", name="importCsv")
     * 
     */
    public function importCsvAction(Request $request)
    {
        $form = $this->createFormBuilder()
                ->add('file', FileType::class, [
                    'mapped' => false,
                ])
                ->add('save', SubmitType::class)
                ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            echo "Submitted";
            // get contents from csv file
            $csv = $form['file']->getData();
            $csvData = file_get_contents($csv->getPathName());

            // serialize csv
            $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);

            $data = $serializer->decode($csvData, 'csv');


            echo "<pre>";
            print_r($data);
            echo "</pre>";
            
            // store into object

            // show form for which columns insert into where

            // make new purchase order & store new items 

        }

        return $this->render('AppBundle:CsvImport:import_csv.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}
