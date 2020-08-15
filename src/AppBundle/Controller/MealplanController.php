<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Event;
use AppBundle\Entity\Menu;
use AppBundle\Entity\Mealplan;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class MealplanController extends Controller
{
    /**
     * @Route("/mealplanner", name="mealplanner")
     */
    public function mealplanAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();

		$user = $this->getUser();

        // Just to be sure...
        if (!$user->getMealplan()) {
            $mealplan = new Mealplan();
            $mealplan->setUser($user);
            $user->setMealplan($mealplan);
            $em->persist($mealplan);
            $em->flush();
        }

		date_default_timezone_set('Europe/Brussels');
		$today = new \DateTime('today');

        $isodate = $request->query->get('isodate');
        // @todo add validation for isodate
        if (!$isodate) {
            $isodate = $today->format('Y\WW');
        }

    	// $firstDay = new \DateTimeImmutable('Monday');
        // $firstDay = (new \DateTimeImmutable('today'))->setIsoDate($year, $w, 1);

        $firstDay = new \DateTimeImmutable($isodate);
    	$weekDays = [$firstDay->format('Y-m-d')];
    	for ($i=1; $i<7; $i++) {
    		$nextDay = $firstDay->add(new \DateInterval("P{$i}D"));
    		$weekDays[] = $nextDay->format('Y-m-d');
    	}
        $nextWeek = ($firstDay->add(new \DateInterval("P1W")))->format('Y\WW');
        $previousWeek = ($firstDay->sub(new \DateInterval("P1W")))->format('Y\WW');

    	$q = $request->query->get('q');

    	// Recipes
    	$repo = $em->getRepository('AppBundle:Recept');
    	$query = $repo->createQueryBuilder('r')
    		// ->select('r.id', 'r.titel')
    		->addSelect('i')
    		->leftJoin('r.ingredienten', 'i')
    		->where('r.user = :user')
    		->setParameter('user', $user);

    	if ($q) {
    		$query->andWhere('r.titel LIKE :q OR i.ingredient LIKE :q')
    			->setParameter('q', '%'.$q.'%');
    	}

    	$query->orderBy('r.toegevoegdOp', 'DESC');
    	
    	$recipes = $query->getQuery()->getResult();

    	// Menus
    	$repo = $em->getRepository('AppBundle:Menu');
    	$query = $repo->createQueryBuilder('m')
    		->select('m.id', 'm.naam')
    		->where('m.user = :user')
    		->setParameter('user', $user)
    		->getQuery();
    	$menus = $query->getResult();

        // Events
        $eventData = $this->fetchEvents($user);

    	return $this->render('mealplanner/planner.html.twig', [
    		'today' => $today->format('Y-m-d'),
    		'weekDays' => $weekDays,
    		'recipes' => $recipes,
    		'menus' => $menus,
            'nextWeek' => $nextWeek,
            'previousWeek' => $previousWeek,
            'eventData' => $eventData,
            'token' => $user->getToken(),
    	]);
    }

    public function fetchEvents($user){

        $mealplan = $user->getMealplan();
        $events = $mealplan->getEvents();

        $eventData = [];
        foreach ($events as $event) {
            $recipes = [];
            foreach ($event->getRecepten() as $recipe) {
                $recipes[] = [
                    'eventid' => $event->getId(),
                    'id' => $recipe->getId(),
                    'title' => $recipe->getTitel(),
                ];
            }
            $eventData['days'][$event->getDate()->format('Y-m-d')]['slots'][$event->getTimeSlot()] = $recipes;
        }

        return $eventData;
    }

  //   /**
  //    * @Route("/event/get/{day}/{slot}", name="getEvent")
  //    */
  //   public function getEventAction($day, $slot){

  //   	$em = $this->getDoctrine()->getManager();

		// $user = $this->getUser();
  //       $mealplan = $user->getMealplan();

  //   	$repo = $em->getRepository('AppBundle:Event');
  //   	$query = $repo->createQueryBuilder('e')
  //   		->where('e.mealplan = :mealplan')
  //   		->setParameter('mealplan', $mealplan)
  //   		->andWhere('e.timeSlot = :slot')
  //   		->setParameter('slot', $slot)
  //   		->andWhere('e.date = :day')
  //   		->setParameter('day', $day)
  //   		// ->join('e.recepten', 'r')
  //   		->getQuery();
  //   	// $event= $query->getResult();
  //   	$event = $query->setMaxResults(1)->getOneOrNullResult();

  //   	if ($event) {
  //   		$response = [];
  //   		$recipes = $event->getRecepten();
  //   		foreach ($recipes as $recipe) {
  //   			$response[] = [
  //   				'eventid' => $event->getId(),
  //   				'id' => $recipe->getId(),
  //   				'title' => $recipe->getTitel(),
  //   			];
  //   		}
  //   		return new JsonResponse($response);
  //   	} else {
  //   		return new JsonResponse(NULL);
  //   	}
  //   }

    /**
     * @Route("/playground", name="playground")
     */
    public function playgroundAction(){
    	$em = $this->getDoctrine()->getManager();
        $test = array(
            'days' => array(),
        );
        $serialized = serialize($test);
        dump($serialized); // a:1:{s:4:"days";a:0:{}}

		// date_default_timezone_set('Asia/Tokyo');
        $thisWeek = (new \DateTime('this week'))->format('Y-m-d');
        dump($thisWeek);

        $token = bin2hex(random_bytes(16)); // ea41fda73539e43e8a5fa892440fbede
        // $token = bin2hex(openssl_random_pseudo_bytes(16)); // cf1c19e974a11a36e3d5a635d8620dbd
        dump($token);
    }

    /**
     * @Route("/cal/recipe/add/{id}", name="addRecipeToCal", methods={"POST"})
     */
    public function addRecipeToCalAction($id, Request $request)
    {
    	$em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $mealplan = $user->getMealplan();

    	$day = $request->request->get('day');
    	$slot = $request->request->get('slot');
    	$eventId = $request->request->get('eventId');

    	$recipe = $em->getRepository('AppBundle:Recept')->find($id);

		if (!$recipe) {
			return new JsonResponse(array('message' => 'No recipe found for id ' . $id), 404);
		}

    	date_default_timezone_set('Europe/Brussels');

    	if (!empty($eventId)) {
    		$event = $em->getRepository('AppBundle:Event')->find($eventId);
	    	if ($event->getRecepten()->contains($recipe)) { // recipe cannot be added twice to same event
		        return new JsonResponse(array('message' => 'Recept werd reeds toegevoegd.'), 500);
	    	}
    		$event->addRecepten($recipe);
    	} else {
    		$event = new Event();
	    	$event->setTimeSlot($slot);
	    	$event->setDate(new \DateTime($day));
	    	$event->addRecepten($recipe);
	    	$event->setMealplan($mealplan);

    		$em->persist($event);
    	}

    	$em->flush();

    	return new Response($event->getId());
    }

    /**
     * @Route("/cal/menu/add/{id}", name="addMenuToCal", methods={"POST"})
     */
    public function addMenuToCalAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $menu = $em->getRepository('AppBundle:Menu')->find($id);

        if (!$menu) {
            return new JSONResponse(array('message' => 'No menu found for id ' . $id), 404);
        }
        $data = $menu->getMenuData();

        $user = $this->getUser();
        $mealplan = $user->getMealplan();

        // SAMPLE MENUDATA
        // $data = [
        //     'days' => [
        //         0 => [
        //             'slots' => [
        //                 1 => [142, 144],
        //                 2 => [145],                    ]
        //         ],
        //         1 => [
        //             'slots' => [
        //                 3 => [550],
        //             ]
        //         ],
        //         2 => [
        //             'slots' => [
        //                 3 => [578, 572]
        //             ],
        //         ],
        //     ],
        // ];

        $repo = $em->getRepository('AppBundle:Event');

        if (!empty($request->request->get('startDate'))) {
        	$startDay = \DateTimeImmutable::createFromFormat('d/m/Y', $request->request->get('startDate'));	
        }
        else {
        	$startDay = new \DateTimeImmutable('today');
        }

        foreach ($data['days'] as $i => $day) {
            $current = $startDay->add(new \DateInterval("P{$i}D"));
            $currentDay = $current->format('Y-m-d');
            foreach ($day['slots'] as $slot => $recipes) {              
                // check if there's an existing event for this timeslot
                $query = $repo->createQueryBuilder('e')
                    // ->select('e.id')
                    ->where('e.date = :current')
                    ->setParameter('current', $currentDay)
                    ->andWhere('e.timeSlot = :slot')
                    ->setParameter('slot', $slot)
                    ->andWhere('e.mealplan = :mealplan')
                    ->setParameter('mealplan', $mealplan)
                    ->getQuery();
                $event = $query->setMaxResults(1)->getOneOrNullResult();

                $event = !empty($event) ? $event : new Event();
                $event->setTimeSlot($slot);
                foreach ($recipes as $recipeId) {
                    $recipe = $em->getRepository('AppBundle:Recept')->find($recipeId);
                    if ($recipe) {
                    	if (!$event->getRecepten()->contains($recipe)) { // skip if event already contains recipe
                        	$event->addRecepten($recipe);
                    	}
                    }
                }
                $event->setMealplan($mealplan);
                $event->setDate($current);

                if ($event->hasRecepten()) {
                	$em->persist($event);
            	}
            }
        }

        $em->flush();

        $this->addFlash(
            'notice',
            'Het menu <em>' . $menu->getNaam() . '</em> werd toegevoegd aan de planning.'
        );

        return new JsonResponse(array('message' => 'Menu added to calendar!'), 200);
    }


    /**
     * @Route("/cal/menu/preview/{id}", name="previewMenu")
     */
    public function previewMenuAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $menu = $em->getRepository('AppBundle:Menu')->find($id);

        if (!$menu) {
            return new JsonResponse(array('message' => 'No menu found for id ' . $id), 404);
        }

        $menuData = $menu->getMenuData();

        // Check if recipes still exist and replace ids with titles
        foreach ($menuData['days'] as $i => $day) {
            foreach ($day['slots'] as $j => $recipeIds) {
                foreach ($recipeIds as $k => $recipeId) {
                    $recipe = $em->getRepository('AppBundle:Recept')->find($recipeId);
                    if ($recipe) {
                        $menuData['days'][$i]['slots'][$j][$k] = $recipe->getTitel();
                    }
                }
            }
        }

        return $this->render('menus/previewmenu.html.twig', array(
            'menu' => $menu,
            'menuData' => $menuData,
        ));
    }

    /**
     * @Route("/cal/menu/delete/{id}", name="deleteMenu", methods={"POST"})
     */
    public function deleteMenuAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $menu = $em->getRepository('AppBundle:Menu')->find($id);

        if (!$menu) {
            return new JsonResponse(array('message' => 'No menu found for id ' . $id), 404);
        }

        $em->remove($menu);
        $em->flush();

        $this->addFlash(
            'notice',
            'Het menu <em>' . $menu->getNaam() . '</em> werd verwijderd.'
        );        

        return new JsonResponse(array('message' => 'Menu deleted!'), 200);
    }

    /**
     * @Route("/cal/menu/save", name="saveMenuFromCal", methods={"POST"})
     */
    public function saveMenuAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $mealplan = $user->getMealplan();

        $naam = $request->request->get('name');
        if (!$naam) {
            return new JsonResponse(array('message' => 'Het veld naam is verplicht.'), 400);
        }

		date_default_timezone_set('Europe/Brussels');
        $thisWeek = (new \DateTime('this week'))->format('Y-m-d');

        $repo = $em->getRepository('AppBundle:Event');
        $query = $repo->createQueryBuilder('e')
            ->addSelect('r')
            ->join('e.recepten', 'r')
            ->andWhere('e.mealplan = :mealplan')
            ->setParameter('mealplan', $mealplan)
            ->andWhere('e.date >= :thisWeek')
            ->setParameter('thisWeek', $thisWeek)
            ->orderBy('e.date')
            ->getQuery();
        $events = $query->getResult();

        if (!$events) {
            return new JsonResponse(array('message' => 'Plaats één of meer recepten op de planning en probeer opnieuw'), 400);
        }
        
        $data = [];
        foreach ($events as $event) {
            $ids = [];
            foreach ($event->getRecepten() as $recipe) {
                $ids[] = $recipe->getId();
            }
            $data['days'][$event->getDate()->format('Y-m-d')]['slots'][$event->getTimeSlot()] = $ids;
        }
        $data['days'] = array_values($data['days']); // re-index

        $menu = new Menu();
        $menu->setNaam($naam);
        $menu->setMenuData($data);
        $menu->setUser($user);

        $em->persist($menu);
        $em->flush();

        $this->addFlash(
            'notice',
            'Het menu <em>' . $menu->getNaam() . '</em> werd opgeslagen.'
        );

        return new JsonResponse(array('message' => 'Menu saved!'), 200);
    }

    /**
     * @Route("/cal/clear", name="clearCal", methods={"POST"})
     */
    public function clearCalAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();

		$user = $this->getUser();

        $mealplan = $user->getMealplan();

        if ($mealplan) {
        	$events = $mealplan->getEvents();
        	foreach ($events as $event) {
        		$em->remove($event);
        	}
        	$em->flush();
        }

        $this->addFlash(
            'notice',
            'Planning gewist.'
        );

    	return new JsonResponse(array('message' => 'Events cleared!'), 200);
    }

    /**
     * @Route("/cal/remove/{id}", name="removeFromCal", methods={"POST"})
     */
    public function removeFromCalAction($id, Request $request)
    {
    	$em = $this->getDoctrine()->getManager();

        $recipe = $em->getRepository('AppBundle:Recept')->find($id);
		if (!$recipe) {
			return new JsonResponse(array('message' => 'No recipe found for id ' . $id), 404);
		}

    	$eventid = $request->request->get('eventid');
    	$event = $em->getRepository('AppBundle:Event')->find($eventid);
		if (!$event) {
			return new JsonResponse(array('message' => 'No event found for id ' . $eventid), 404);
		}

		$event->removeRecepten($recipe);
		$response = array('eventRemoved' => false);

		// if no more recipes in event, clear event as well
		if (!$event->hasRecepten()) {
			$em->remove($event);
			$response = array('eventRemoved' => true);
		}

		$em->flush();

    	return new JsonResponse($response, 200);
    }

   /**
    * Gets the events data from the database and populates the iCal object.
    *
    * @Route("/ical-events/{token}", name="getICalEvents")
    */
   public function getICalObject($token)
   {
		// $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneByToken($token);

        if (!$user) {
            throw $this->createNotFoundException(
                'Geen gebruiker gevonden met token '.$token
            );
        }

        $mealplan = $user->getMealplan();
        $events = $mealplan->getEvents();

		define('ICAL_FORMAT', 'Ymd\THis');

		$icalObject = "BEGIN:VCALENDAR
VERSION:2.0
METHOD:PUBLISH
PRODID:-//Mealplanner//Events//NL
X-WR-CALNAME:MealPlanner
X-WR-TIMEZONE:Europe/Brussels\n";

		foreach ($events as $event) {
	    	switch ($event->getTimeSlot()) {
	    		case 1:
	    			$hours = 8;
	    			break;
	    		case 2:
	    			$hours = 12;
	    			break;
	    		case 3:
	    			$hours = 18;
	    	}

	    	$day = $event->getDate()->format('Y-m-d');
	    	$date = new \DateTimeImmutable($day);
	    	$start = $date->add(new \DateInterval("PT{$hours}H"));
	    	$end = $start->add(new \DateInterval("PT1H"));

			$icalObject .=
			"BEGIN:VEVENT
DTSTART:" . $start->format(ICAL_FORMAT) . "
DTEND:" . $end->format(ICAL_FORMAT) . "
DTSTAMP:" . $event->getCreatedAt()->format(ICAL_FORMAT) . "
SUMMARY:" . $event->getDescription() ."
UID:" . $event->getUid() . "
STATUS:CONFIRMED
LAST-MODIFIED:" . $event->getUpdatedAt()->format(ICAL_FORMAT) . "
LOCATION:THUIS
END:VEVENT\n";
		}

		// close calendar
		$icalObject .= "END:VCALENDAR";

		$response = new Response($icalObject, 200);
		$response->headers->set('Content-type', 'text/calendar; charset=utf-8');
		$response->headers->set('Content-Disposition', 'attachment; filename="cal.ics"');

		return $response;
   }
}