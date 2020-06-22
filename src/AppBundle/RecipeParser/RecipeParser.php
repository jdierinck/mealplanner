<?php

namespace AppBundle\RecipeParser;

use Goutte\Client;

class RecipeParser {

	public static function parse ($url) {

		$client = new Client();
		$client->followRedirects();
     	$guzzleClient = new \GuzzleHttp\Client(array('curl' => array(CURLOPT_SSL_VERIFYHOST => false, CURLOPT_SSL_VERIFYPEER => false), 'cookies' => true, 'allow_redirects' => true));
     	$client->setClient($guzzleClient);
		$crawler = $client->request('GET', $url);
		// dump($crawler);die();

		$metadata = array();
		// Extract JSON-LD first
		$crawler->filterXPath('//script[@type="application/ld+json"]')
	        ->each(function($node) use (&$metadata) {
	        	// dump($node->text());
				$text = Text::cleanupHtml($node->text());
				switch (gettype(json_decode($text, false))) {
					case 'object':
						$json = json_decode($text, true);
						// dump($json);
			        	if (isset($json['@graph'])) {
			        		foreach ($json['@graph'] as $graph){
			        			if ($graph['@type'] == 'Recipe') {
			        				$metadata[] = $graph;
			        			}
			        		}
			        	}
			        	elseif (isset($json['@type']) && $json['@type'] == 'Recipe') {
			        		$metadata[] = $json;
			        	}
			        	break;
			        case 'array':
			        	foreach (json_decode($text, true) as $json) {
			        		// dump($json);
				        	if (isset($json['@graph'])) {
				        		foreach ($json['@graph'] as $graph){
				        			if ($graph['@type'] == 'Recipe') {
				        				$metadata[] = $graph;
				        			}
				        		}
				        	}
				        	elseif (isset($json['@type']) && $json['@type'] == 'Recipe') {
				        		$metadata[] = $json;
				        	}		
			        	}
			        	break;		
				}
	    });
		// dump($metadata);
	    if (count($metadata) > 0) {
	    	// dump($metadata[0]);
	    	$recipe = self::prepareData($metadata[0], $url);
	    	return $recipe;
		}

	  	// Search for Microdata/RDFa secondly
	    else {
	    	$microdata = array();

			$crawler->filterXPath("//*[contains(@itemtype, '//schema.org/Recipe') or contains(@itemtype, '//www.schema.org/Recipe')]//*[@itemprop]")
        		->each(function($node) use (&$microdata){
            		$ret = self::getNodeStructuredData($node, 'microdata');           
            		$microdata[$ret['property']][] = $ret['value'];
            		// dump($node->text());
        	});
			// dump($microdata);

			if (empty($microdata)) {
				$crawler->filterXPath("//*[contains(@vocab, '//schema.org') and @typeof='Recipe']//*[@property]")
        			->each(function($node) use (&$microdata){
            			$ret = self::getNodeStructuredData($node, 'rdfa');           
            			$microdata[$ret['property']][] = $ret['value'];
            			// dump($node->text());
        			});
				//dump($microdata);
			}
		}

    	if (!empty($microdata)) {
    		// Normalize data
    		foreach ($microdata as $key => $value) {
    			if (in_array($key, ['ingredients', 'recipeIngredient', 'recipeInstructions', 'image', 'recipeCategory', 'recipeCuisine', 'recipeYield', 'keywords'])) continue;
    			$microdata[$key] = $value[0];
    		}
    		// dump($microdata);
	    	$recipe = self::prepareData($microdata, $url);
	    	return $recipe;
    	}

		throw new NoRecipeDataException();
	}

	public function getNodeStructuredData($node, $type = 'microdata') {
	    $node_name = $node->nodeName();
	    // if ($node_name == 'link' || $node_name == 'a') {
	    //     $value = $node->attr('href');
	    // } elseif ($node_name == 'img') {
	    if ($node_name == 'img') {
	        $value = $node->attr('src'); // @todo: this is sometimes a relative link
	    } elseif ($node_name == 'meta') {
	        $value = $node->attr('content');			    
	    } elseif ($node_name == 'time') {
	        $value = $node->attr('datetime');
	    } elseif (!empty($node->attr('content'))) {
	        $value = $node->attr('content');		        
	    } else {
	        $value = trim($node->text());
	    }
	    if ($type == 'microdata'){
	        $property = current($node->extract(array('itemprop')));
	    } elseif ($type == 'rdfa'){
	        $property = current($node->extract(array('property')));
	    }
	    return array(
	        'property' => $property,
	        'value' => $value,
	    );
	}

	public function prepareData($recipe, $url) {
		// Add url
		if (!isset($recipe['url'])) $recipe['url'] = $url;

		// Remove non-existing image(s)
		$client = new \GuzzleHttp\Client();

		if (isset($recipe['image'])) {
			if (is_array($recipe['image'])) {
				if (isset($recipe['image']['@type'])) {
					if (!self::fileExists($client, $recipe['image']['url'])) {
						unset($recipe['image']);
					}
				}
				else {
					foreach ($recipe['image'] as $key => $url) {
						if (!self::fileExists($client, $url)) {
							unset($recipe['image'][$key]);
						}						
					}
					if (!empty($recipe['image'])) {
						$recipe['image'] = array_values($recipe['image']); // re-index
					} else {
						unset($recipe['image']);
					}
				}
			}
			else {
				if (!self::fileExists($client, $recipe['image'])) {
					unset($recipe['image']);
				}
			}
		}
		return ($recipe);
	}

	public function fileExists($client, $url){
		$resourceExists = false;
 
		// $ch = curl_init($url);
		// curl_setopt($ch, CURLOPT_NOBODY, true);
		// curl_exec($ch);
		// $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		// curl_close($ch);

		try {
			$response = $client->request('HEAD', $url); // use HEAD request to prevent downloading the whole image
			$statusCode = $response->getStatusCode();

			//200 = OK
			if($statusCode == '200'){
			    $resourceExists = true;
			}
			// dump($statusCode);
		}
		catch (\Exception $e) {

		}
 
		return $resourceExists;
	}	

}