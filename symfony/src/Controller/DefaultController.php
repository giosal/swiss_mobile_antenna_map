<?php

namespace App\Controller;

use App\Form\AntennaType;
use App\Repository\AntennaRepository;
use App\Service\GetAntennaeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Map\InfoWindow;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;

final class DefaultController extends AbstractController{
    #[Route('/', name: 'app_default')]
    public function index(Request $request, GetAntennaeService $getAntennaeService, AntennaRepository $antennaRepository): Response
    {

        $form = $this->createForm(AntennaType::class);

        $antennae = $antennaRepository->findAll();
        $map = (new Map());
        $map
            ->center(new Point(46.7985, 8.2318))
            ->zoom(11)
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $antennae = $antennaRepository->findBy([
               'operator' => $form->get('operator')->getData(),
               'station' => $form->get('station')->getData(),
               'type' => $form->get('type')->getData(),
               'power' => $form->get('power')->getData(),
               'support3G' => $form->get('support3G')->getData(),
               'support4G' => $form->get('support4G')->getData(),
               'support5G' => $form->get('support5G')->getData(),
               'adaptive' => $form->get('adaptive')->getData(),
                'installationLimit' => $form->get('installationLimit')->getData(),
                'latitude' => $form->get('latitude')->getData(),
                'longitude' => $form->get('longitude')->getData(),
            ]);

            foreach ($antennae as $antenna) {
                $map
                    ->addMarker(new Marker(
                        position: new Point($antenna->getLatitude(), $antenna->getLongitude()),
                        title: $antenna->getOperator().' '.$antenna->getStation(),
                        infoWindow: new InfoWindow(
                            headerContent: '<strong>'.$antenna->getOperator().' '.$antenna->getStation().'</strong>',
                            content: 'Last updated: '.$antenna->getDatafileDate()->format('d-m-Y')
                        )
                    ))
                    ;
            }

            return $this->render('default/index.html.twig', [
                'controller_name' => 'DefaultController',
                'antennae'        => $antennae,
                'form'            => $form->createView(),
                'map'             => $map,
            ]);
        }

        foreach ($antennae as $antenna) {
            $map
                ->addMarker(new Marker(
                    position: new Point($antenna->getLatitude(), $antenna->getLongitude()),
                    title: $antenna->getOperator().' '.$antenna->getStation(),
                    infoWindow: new InfoWindow(
                        headerContent: '<strong>'.$antenna->getOperator().' '.$antenna->getStation().'</strong>',
                        content: 'Last updated: '.$antenna->getDatafileDate()->format('d-m-Y')
                    )
                ))
            ;
        }

        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'antennae'        => $antennae,
            'form'            => $form->createView(),
            'map'             => $map,
        ]);
    }

    #[Route('/fetch', name: 'app_fetch')]
    public function fetch(GetAntennaeService $getAntennaeService): Response
    {
        $getAntennaeService->getAntennae();
        return new Response('Antennae updated successfully', Response::HTTP_OK);
    }
}
