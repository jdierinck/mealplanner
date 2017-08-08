<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

class DeptFinder
{
    /**
     * @var EntityManager 
     */
    protected $em;

	public function __construct(EntityManager $entityManager) 
	{
		$this->em = $entityManager;
	}

    public function findDept($str)
    {
		$repo = $this->em->getRepository('AppBundle:Afdeling');
		$afdelingen = $repo->findAll();

	    $matches = array();
		
		foreach ($afdelingen as $afdeling) {

	        if ($afdeling->getName() == 'niet toegewezen') continue;

	        $voedingswaren = explode("\r\n", $afdeling->getVoedingswaren());

	        foreach ($voedingswaren as $v) {
	            if (preg_match('/\b'.$v.'\b/i', $str) === 1) {   
	            	$matches[$v] = $afdeling;
	            }
	        }	
		}

		if (null == $matches) {
			$dept = $repo->findOneByName('niet toegewezen');
		} else {
			// Sort array by length of keys (longest matching string will win)
			uksort($matches, function($a, $b){
				if ($a == $b) {
	        		return 0;
	    		}
	    		return (strlen($a) > strlen($b)) ? -1 : 1;
			});
			// Get first element of $matches
			$dept = reset($matches);
		}

        return $dept;
    }
}