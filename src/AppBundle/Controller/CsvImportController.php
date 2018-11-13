<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use AppBundle\Entity\Product;
use AppBundle\Entity\Attribute;
use AppBundle\Entity\AttributeOption;


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
            // clear any old sessions
            $session->remove('products'); 
            $session->remove('keys'); 
            $session->remove('cols'); 

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
        
        // setup form for columns to attributes
        $keys = $session->get('keys');
        $form = $this->createFormBuilder();

        // put attributes into arrays
        $attributes = array();
        $aRepo = $this->getDoctrine()
            ->getRepository(Attribute::class)
            ->findAll();

        foreach($aRepo as $a) {
            $attributes[$a->getName()] = $a->getId();
        }

        foreach($keys as $key) {
            $form->add($key, ChoiceType::class, array(
                'choices' => array(
                    'n/a' => null,
                    'Main Attributes' => array(
                        'SKU' => 'sku',
                        'Name' => 'name',
                        'Type' => 'type',
                        'Description' => 'description',
                        'Price' => 'price'
                    ),
                    'Product Specific' => $attributes,
                )
            ));
        }
        $form->add('Save as New Order', SubmitType::class, array());
        $form = $form->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) 
        {
            $result = $form->getData();
            $data = [];

            // clean array
            $columns = $this->removeBlankArrEntries($result);
            
            echo "<pre>";print_r($columns); echo "</pre>";
            
            $session->set('cols', $columns);

            return $this->redirectToRoute('csv_edit_dropdowns');
        }
        
        return $this->render('AppBundle:CsvImport:edit.html.twig', array(
            'form' => $form->createView(),
        )); 
    }
    
    /** 
     * @Route("/csv/edit/dropdowns", name="csv_edit_dropdowns")
     */
    public function editDropdownAction(Request $request) 
    {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();

        echo "<pre>"; print_r($session->get('products')[0]); echo "</pre>";
        echo "<pre>"; print_r($session->get('keys')); echo "</pre>";
        echo "<pre>"; print_r($session->get('cols')); echo "</pre>";

        $form = $this->createFormBuilder();

        $attributeRepository = $this->getDoctrine()
            ->getRepository(Attribute::class);

        // collect product specific attributes 
        foreach($session->get('cols') as $a) 
        {
            $is_attr = is_numeric($aid); 

            // check for dropdown or attribute
            if($is_attr) {
                $attr = $attributeOption->findById($a['value']);
                $type = $attr->getType();
            }

            switch($type) {
                case 1: // Selectbox
                    $choices = $this->prepareDropdown($a['value']);
                    $form->add($a['value'], ChoiceType::class, array(
                        'choices' => $option, 
                    ));
                    break;
                case 0: // Text
                default:
                    $form->add($a['value'], TextType::class, array(
                    ));   
            }
            
            $dropdown = $this->prepareDropdown($a['value']);

            // place available attributes in array
            /*
            $optionAll = $attributeOption->findByAttribute($a['value']);
            $option = [];

            // give every unique value a dropdown with above options
            foreach($optionAll as $o) 
            {
                $option[$o->getName()] = $o->getId();
            }

            switch($a->getType()) {
                case 1: // Selectbox
                    $form->add($a['value'], ChoiceType::class, array(
                        'choices' => $option, 
                    ));
                    break;
                case 0: // Text
                default:
                    $form->add($a['value'], TextType::class, array(
                    ));   
            }
            */
        }

        $form = $form->getForm();

        return $this->render('AppBundle:CsvImport:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }
  
    /**
     * @Route("/csv/process", name="csv_process")
     */
    public function processCsvAction(Request $request)
    {
        $session = new Session();

        if(!is_array($session->get('result'))) {

        }
    }

    /** 
     * Find blank array entries and remove them
     */
    public function removeBlankArrEntries($array) 
    {
        foreach($array as $k => $v) 
        {
            if(empty($v)) {
                unset($array[$k]);
            } else {
                $array[$k] = ['key' => $k, 'value' => $v];
            }
        }
        
        return $array;
    }

    /** 
     * Get options for dropdown dependent on basic or advanced attribute
     * 
     * Takes attribute id (numerical or string), returns proper options
     */ 
    public function prepareDropdown($aid) 
    {
        $repo = $this->getDoctrine()->getRepository(AttributeOption::class);
        $options = $repo->findByAttribute($aid);
        $array = [];

        $is_attr = is_numeric($aid);

        switch($is_attr) 
        {
            // if basic (false), load assigned table
            case false:
                
                break;

            // if specific, load attributes from table
            case true:
                
                break;
            default:
        }

        $result = array(
            'choices' => $array,
            'type'    =>
        );
        
        return $array;
    }

    /**
     * Convert serialized CSV array to new objects
     */
    public function placeIntoObjects($objects, $columns)
    {
        $em = $this->getDoctrine()->getManager();

    }
}
