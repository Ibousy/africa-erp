<?php

namespace App\Helpers;

class Labels
{
    private static array $labels = [
        // Statuts maintenance
        'planifie'   => 'Planifié',
        'en_cours'   => 'En cours',
        'termine'    => 'Terminé',
        // Statuts production / factures
        'brouillon'  => 'Brouillon',
        'annule'     => 'Annulé',
        'envoye'     => 'Envoyé',
        'paye'       => 'Payé',
        // Statuts machines
        'actif'      => 'Actif',
        'en_panne'   => 'En panne',
        'maintenance' => 'Maintenance',
        // Statuts qualité
        'passe'      => 'Réussi',
        'echoue'     => 'Échoué',
        // Types
        'preventive' => 'Préventive',
        'corrective' => 'Corrective',
        'entree'     => 'Entrée',
        'sortie'     => 'Sortie',
        'devis'      => 'Devis',
        'facture'    => 'Facture',
        // Rôles
        'admin'      => 'Administrateur',
        'manager'    => 'Manager',
        'operateur'  => 'Opérateur',
        // Tâches
        'todo'       => 'À faire',
        'fait'       => 'Fait',
        // Gravité
        'faible'     => 'Faible',
        'moyen'      => 'Moyen',
        'grave'      => 'Grave',
        // MRP
        'ouvert'     => 'Ouvert',
        'confirme'   => 'Confirmé',
        'cloture'    => 'Clôturé',
        // Logistique
        'en_attente' => 'En attente',
        'en_transit' => 'En transit',
        'livre'      => 'Livré',
        'entrant'    => 'Entrant',
        'sortant'    => 'Sortant',
        // RH
        'conge'      => 'En congé',
        'quitte'     => 'Quitté',
        // Comptabilité
        'recette'    => 'Recette',
        'depense'    => 'Dépense',
        // Achats
        'recu'       => 'Reçu',
        // CRM
        'nouveau'     => 'Nouveau',
        'contacte'    => 'Contacté',
        'qualifie'    => 'Qualifié',
        'proposition' => 'Proposition',
        'gagne'       => 'Gagné',
        'perdu'       => 'Perdu',
    ];

    public static function get(string $key): string
    {
        return self::$labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }
}
