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

use Symfony\Component\HttpFoundation\Session\Session;

class CsvImportController extends Controller
{
    /**
     * @Route("/csv/index", name="csv_index")
     */
    public function indexCsvAction(Request $request)
    {
        $session = new Session();
        $session->start();

        $form = $this->createFormBuilder()
                ->add('file', FileType::class, [
                    'mapped' => false,
                ])
                ->add('save', SubmitType::class)
                ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) 
        {
            // process contents from csv
            $csv = $form['file']->getData();
            $csvData = file_get_contents($csv->getPathName());

            $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
            $data = $serializer->decode($csvData, 'csv');

            echo "<pre>";print_r($data[0]);echo "</pre>";

            // store products in session for edit

            // if products are found, show form for assigning attributes
            return $this->redirectToRoute('csv_edit');            

        } else {
            return $this->render('AppBundle:CsvImport:index.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }

    /**
     * @Route("/csv/edit", name="csv_edit")
     */
    public function editCsvAction(Request $request)
    {
        // setup form for columns to attributes

        // save settings, send to process function
        /*
        if(count($data) > 0)
        {
            echo "Products found";
            // store into object
            foreach($data as $product) {

            }

            // make new purchase order & store new items 

        }
        */
        return $this->render('AppBundle:CsvImport:edit.html.twig', array()); 
    }
}
