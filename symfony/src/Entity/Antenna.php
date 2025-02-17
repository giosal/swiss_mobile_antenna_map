<?php

namespace App\Entity;

use App\Repository\AntennaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: AntennaRepository::class)]
class Antenna
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $station = '';

    #[ORM\Column(length: 255)]
    private string $operator = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $power = null;

    #[ORM\Column(name: 'support_3g', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $support3G = false;

    #[ORM\Column(name: 'support_4g', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $support4G = false;

    #[ORM\Column(name: 'support_5g', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $support5G = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adaptive = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datafileDate = null;

    #[ORM\Column(nullable: true)]
    private ?float $installationLimit = null;

    #[ORM\Column]
    private float $latitude = 0.0;

    #[ORM\Column]
    private float $longitude = 0.0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStation(): string
    {
        return $this->station;
    }

    public function setStation(string $station): static
    {
        $this->station = $station;

        return $this;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPower(): ?string
    {
        return $this->power;
    }

    public function setPower(?string $power): static
    {
        $this->power = $power;

        return $this;
    }

    public function isSupport3G(): bool
    {
        return $this->support3G;
    }

    public function setSupport3G(bool $support3G): static
    {
        $this->support3G = $support3G;

        return $this;
    }

    public function isSupport4G(): bool
    {
        return $this->support4G;
    }

    public function setSupport4G(bool $support4G): static
    {
        $this->support4G = $support4G;

        return $this;
    }

    public function isSupport5G(): bool
    {
        return $this->support5G;
    }

    public function setSupport5G(bool $support5G): static
    {
        $this->support5G = $support5G;

        return $this;
    }

    public function getAdaptive(): ?string
    {
        return $this->adaptive;
    }

    public function setAdaptive(?string $adaptive): static
    {
        $this->adaptive = $adaptive;

        return $this;
    }

    public function getDatafileDate(): ?\DateTimeInterface
    {
        return $this->datafileDate;
    }

    public function setDatafileDate(?\DateTimeInterface $datafileDate): static
    {
        $this->datafileDate = $datafileDate;

        return $this;
    }

    public function getInstallationLimit(): ?float
    {
        return $this->installationLimit;
    }

    public function setInstallationLimit(?float $installationLimit): static
    {
        $this->installationLimit = $installationLimit;

        return $this;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }
}
