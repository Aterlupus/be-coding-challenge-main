<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class LogsImport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $uuid;

    #[ORM\Column(type: 'string')]
    private string $filepath;

    #[ORM\OneToMany(targetEntity: LogsEntry::class, mappedBy: 'logsImport', orphanRemoval: true)]
    private Collection $logsEntries;


    public function __construct()
    {
        $this->logsEntries = new ArrayCollection;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getFilepath(): string
    {
        return $this->filepath;
    }

    public function setFilepath($filepath): void
    {
        $this->filepath = $filepath;
    }

    public function getLogsEntries(): Collection
    {
        return $this->logsEntries;
    }

    public function getLogsEntriesCount(): int
    {
        return $this->getLogsEntries()->count();
    }

    public function setLogsEntries(Collection $logsEntries): void
    {
        $this->logsEntries = $logsEntries;
    }
}
