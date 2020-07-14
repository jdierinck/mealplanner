<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
	/**
	 * Export recipes as CSV
	 *
	 * @Route("/recepten/exportcsv/{all}", name="exportcsv", defaults={"all":"false"}, requirements={"all":"true|false"})
	 */	
	public function generateCsvAction($all, Request $request)
	{
		$result_ids = $request->getSession()->get('results');
		$user = $this->getUser();

	    $response = new StreamedResponse();
	    $response->setCallback(function() use($result_ids, $all, $user){
	        $handle = fopen('php://output', 'w+');

	        // Add the header of the CSV file
	        fputcsv($handle, array(
	        	'Titel', 
	        	'Bron', 
	        	'Bereidingstijd', 
	        	'Ingrediënten',
	        	'Bereidingswijze',
	        	'Gerecht',
	        	'Keuken',
	        	'Hoofdingredient',
	        	// 'Tags',
	        	'Kostprijs',
	        	'Hoeveelheid',
	        	'Eenheid',
	        	'Rating'
	        	),
	        	';'
	        );
	        
	        // Query data from database
		    $repository = $this->getDoctrine()
	    		->getRepository('AppBundle:Recept');

	    	if ($all == 'false') {
		    	$results = $repository->findBy(
		    		array('id'=>$result_ids)
		    	);
	    	} else {
	    		$results = $repository->findBy(array('user' => $user->getId()));
	    	}

	    	foreach ($results as $result) {

            	$ingredienten = '';
            	foreach ($result->getIngredienten() as $ingr) {
            		$hoeveelheid = $ingr->getHoeveelheid();
            		$eenheid = $ingr->getEenheid();
            		$ingredient = $ingr->getIngredient();
            		
            		if (!null == $hoeveelheid && !null == $eenheid) {
            			$ingredienten .= 0 + $hoeveelheid.' '.$eenheid.' '.$ingredient."\n";
            		}
            		elseif (!null == $hoeveelheid && null == $eenheid) {
            			$ingredienten .= 0 + $hoeveelheid.' '.$ingredient."\n";
            		}
            		else {
            			$ingredienten .= $ingredient."\n";
            		}
            	}

		        // Add the data queried from database
		            fputcsv(
		                $handle, // The file pointer
		                array(
		                	$result->getTitel(), 
		                	$result->getBron(), 
		                	$result->getBereidingstijd(),
		                	$ingredienten,
		                	$result->getBereidingswijze(),
		                	null === $result->getGerecht() ? '' : $result->getGerecht()->getName(),
		                	null === $result->getKeuken() ? '' : $result->getKeuken()->getName(),
		                	null === $result->getHoofdingredient() ? '' : $result->getHoofdingredient()->getName(),
		                	// $result->getTags(),
		                	$result->getKostprijs(),
		                	$result->getYield(),
		                	$result->getYieldType()->getUnitPlural(),
		                	$result->getRating(),
		                ),  // The fields
		                ';' // The delimiter
		            );
	    	}

	        fclose($handle);
	    });

	    $response->setStatusCode(200);
	    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
	    $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

	    return $response;
	}
}