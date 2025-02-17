<?php

namespace App\Service;

use App\Entity\Antenna;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class GetAntennaeService
{
    private readonly \Memcached $memcache;
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $em,
    ) {
        $this->memcache = new \Memcached();
        /** @var string $memcachedHost */
        $this->memcache->addServer('memcached', 11211);

    }
    public function getAntennae(): Response
    {
        try {
            $result = $this->httpClient->request(
                'GET',
                'https://data.geo.admin.ch/ch.bakom.standorte-mobilfunkanlagen/standorte-mobilfunkanlagen/standorte-mobilfunkanlagen_2056.json'
            )->toArray()['features'];
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            return new Response($e->getMessage(), 500);
        }
        if (!$this->memcache->fetchAll()) {
            $antennae = $this->em->getRepository(Antenna::class)->findAll();
            foreach ($antennae as $antenna) {
                $this->memcache->set('station_'.$antenna->getOperator().'_'.$antenna->getStation(), $antenna, 3600);
            }
        }

        foreach ($result as $res) {

            try {
                $operatorStation = explode(' ', $res['properties']['station']);
                if (array_key_exists(1, $operatorStation)) {
                    $station           = $operatorStation[1];
                    $operator          = $operatorStation[0];
                    $technologies      = explode(',', explode(' ', $res['properties']['techno_en'])[1]);
                    $installationLimit = explode(' ', $res['properties']['agw_en']);
                    $datafileDate      = explode(' ', $res['properties']['bewilligung_en']);
                    if (array_key_exists(3, $datafileDate) && $datafileDate[3] !== 'low') {
                        $datafileDate = $datafileDate[3];
                    } else {
                        $datafileDate = '1970-01-01';
                    }
                    if (array_key_exists(3, $installationLimit) && $installationLimit[3] !== '-') {
                        $installationLimit = $installationLimit[3];
                    } else {
                        $installationLimit = 0;
                    }
                    $cachedResult = $this->memcache->get('station_'.$operator.'_'.$station);
                    $existingAntenna = $cachedResult ?: $this->em->getRepository(Antenna::class)->findOneBy(['station' => $station]);
                    if (null === $existingAntenna) {
                        $antenna = new Antenna();
                        $antenna->setStation($station);
                        $antenna->setOperator($operator);
                        $antenna->setType($res['properties']['typ_en']);
                        $antenna->setPower($res['properties']['power_en']);
                        foreach ($technologies as $technology) {
                            if ($technology === '3G') {
                                $antenna->setSupport3G(true);
                            } elseif ($technology === '4G') {
                                $antenna->setSupport4G(true);
                            } elseif ($technology === '5G') {
                                $antenna->setSupport5G(true);
                            } else {
                                $antenna->setSupport3G(false);
                                $antenna->setSupport4G(false);
                                $antenna->setSupport5G(false);
                            }
                        }
                        $antenna->setAdaptive($res['properties']['adaptiv_en']);
                        $antenna->setDatafileDate(new \DateTime($datafileDate));
                        $antenna->setInstallationLimit($installationLimit);
                        $coordinates = $this->httpClient->request(
                            'GET',
                            'https://geodesy.geo.admin.ch/reframe/navref?format=json&easting='.explode(',', $res['properties']['koord'])[0].'&northing='.explode(',', $res['properties']['koord'])[1].'&altitude=NaN&input=lv95&output=etrf93-ed'
                        )->toArray();
                        $antenna->setLatitude($coordinates['northing']);
                        $antenna->setLongitude($coordinates['easting']);
                        $antenna->setCreatedAt(new \DateTime('now'));
                        $antenna->setUpdatedAt(new \DateTime('now'));
                        $this->em->persist($antenna);
                        $this->em->flush();
                    } elseif ($existingAntenna->getUpdatedAt() < new \DateTime($datafileDate)) {
                        $existingAntenna->setType($res['properties']['typ_en']);
                        $existingAntenna->setPower($res['properties']['power_en']);
                        foreach($technologies as $technology) {
                            if ($technology === '3G') {
                                $existingAntenna->setSupport3G(true);
                            } elseif ($technology === '4G') {
                                $existingAntenna->setSupport4G(true);
                            } elseif ($technology === '5G') {
                                $existingAntenna->setSupport5G(true);
                            } else {
                                $existingAntenna->setSupport3G(false);
                                $existingAntenna->setSupport4G(false);
                                $existingAntenna->setSupport5G(false);
                            }
                        }
                        $existingAntenna->setAdaptive($res['properties']['adaptiv_en']);
                        $existingAntenna->setDatafileDate(new \DateTime($datafileDate));
                        $existingAntenna->setInstallationLimit($installationLimit);
                        $existingAntenna->setUpdatedAt(new \DateTime($datafileDate));
                        $this->em->persist($existingAntenna);
                        $this->em->flush();
                    } else {
                        continue;
                    }
                } else {
                    continue;
                }
            } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface|\DateMalformedStringException $e) {
                return new Response($e->getMessage(), 500);
            }
        }
        return new Response('Antennae persisted', 200);
    }
}
