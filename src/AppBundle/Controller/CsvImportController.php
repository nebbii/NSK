<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class CsvImportController extends Controller
{
    /**
     * @Route("/csv/index", name="csv_index")
     */
    public function indexCsvAction(Request $request)
    {
        $session = new Session();

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

            // store products in session for edit
            $keys = array_keys($data[0]);
            $session->set('products', $data);
            $session->set('keys', $keys);

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
        $session = new Session();
        
        // put normal attrs and attribute options in array

        // setup form for columns to attributes
        $keys = $session->get('keys');
        $form = $this->createFormBuilder();

        foreach($keys as $key) {
            $form->add($key, ChoiceType::class, array(
                'choices' => array(
                    'bing' => 1,
                    'bing' => 2,
                    'bing' => 3,
                    'bing' => 4,
                    'bing' => 5,
                )
            ));
        }
        $form = $form->getForm();

        $form->handleRequest($request);

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
        return $this->render('AppBundle:CsvImport:edit.html.twig', array(
            'form' => $form->createView(),
        )); 
    }
}
