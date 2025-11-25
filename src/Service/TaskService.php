<?php

namespace App\Service;

use App\Entity\Task;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class TaskService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    
    public function getProgression(Task $task): int
    {    
        $progression = $this->entityManager->getRepository(Task::class)->getTaskProgression($task);

        if ($progression[0]['closing_date']) {
            return 100;
        }

        // 1. Définir les objets DateTime
        $createdDate = new DateTime($progression[0]['created_at']);
        $initialEndDate = new DateTime($progression[0]['initial_end_date']);
        $now = new DateTime();

        // 3. Calculer la durée totale
        $totalInterval = $initialEndDate->getTimestamp() - $createdDate->getTimestamp();

        // 4. Calculer la durée écoulée
        $elapsedInterval = $now->getTimestamp() - $createdDate->getTimestamp();

        // 5. Calculer le pourcentage
        if ($totalInterval <= 0) {
            // Évite la division par zéro si les dates sont identiques ou inversées
            return 0.0;
        }

        $progress = ($elapsedInterval / $totalInterval) * 100;

        // Retourner le pourcentage arrondi à 2 décimales
        return round($progress, 2);
    }
}