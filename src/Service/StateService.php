<?php

namespace App\Service;

use App\Entity\State;
use Doctrine\ORM\EntityManagerInterface;

class StateService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    
    public function getClosingState(): State
    {    
        $state = $this->entityManager->getRepository(State::class)->findOneBy(['isClosingState' => true]);

        if (!$state) {
            $state = $this->entityManager->getRepository(State::class)->findOneBy([], ['weight' => 'DESC']);
        }

        return $state;
    }

    public function removeAllClosingStateBool(?State $currentState = null) {
        $states = $this->entityManager->getRepository(State::class)->findBy(['isClosingState' => true]);
        foreach ($states as $state) {
            if ($currentState && $currentState->getId() === $state->getId()) {
                continue;
            }

            $state->setIsClosingState(false);
            $this->entityManager->persist($state);
        }
        $this->entityManager->flush();
    }

}