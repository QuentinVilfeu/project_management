<?php

namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class RoleHierarchyService
{
    private $allRoles;

    public function __construct(array $securityRoles, private Security $security)
    {
        // Récupère la liste brute des rôles définis dans security.yaml
        $this->allRoles = $securityRoles;
    }

    /**
     * Retourne une liste de tous les rôles "parent" définis.
     * Exclut les rôles hérités et les rôles techniques.
     */
    public function getDefinedRoles(): array
    {
        // On ne veut que les rôles qui sont des clés parentes dans la hiérarchie
        $definedRoles = array_keys($this->allRoles);
        
        // Exclure les rôles techniques de Symfony
        $definedRoles = array_filter($definedRoles, function ($role) {
            return !in_array($role, ['ROLE_ALLOWED_TO_SWITCH']);
        });

        // Formater pour le formulaire (Label => Value)
        $rolesChoices = [];
        foreach ($definedRoles as $role) {
            $rolesChoices[$role] = $role;
        }

        return $rolesChoices;
    }

    public function getAllowedRoles(): array
    {
        $allowedRoles = [];

        // Itération sur tous les rôles définis
        foreach ($this->allRoles as $roleKey => $roleValue) {
            // Si l'utilisateur connecté a le droit d'accorder ce rôle ($roleKey)
            if ($this->security->isGranted($roleKey)) {
                // Le rôle peut être ajouté car il est au niveau de l'utilisateur ou en dessous
                $allowedRoles[$roleKey] = $roleKey;

                foreach ($roleValue as $inheritedRole) {
                    $allowedRoles[$inheritedRole] = $inheritedRole;
                }

                break;
            }
        }

        // Formater pour le formulaire (Label => Value)
        return $allowedRoles;
    }
}