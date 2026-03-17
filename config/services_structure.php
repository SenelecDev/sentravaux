<?php

return [
    'services_structure' => [
        'SA' => [
            'name' => 'Service Administratif',
            'unites' => [
                'UAG' => [
                    'name' => 'Unité Affaire Générale',
                    'natures' => [
                        'Travaux ou Réparation Mobilier Bureau' => 'GMMB',
                        'Aménagement Bureau' => 'GMMB',
                        'Réhoussage Mobilier Bureau' => 'GMMB',
                        'Remplacement Serrure ou Canot' => 'GMMB',
                        'Opération de Désencombrement' => 'GMMB',
                        'Gestion téléphone &/ou puce' => 'GTE',
                        'Gestion Eau' => 'GTE',
                        'Autres demandes' => 'Autres'
                    ]
                ],
                'UPNS' => [
                    'name' => 'Unité Pool Néttoiement Sécurité',
                    'natures' => [
                        'Opération Dératisation ou Désinfectisation' => 'GNS',
                        'Renfort sécurité' => 'GNS',
                        'Entretien et nettoiement locaux' => 'GNS',
                        'Désherbage' => 'GNS',
                        'Entretien pool auto' => 'GP Pool',
                        'Demande de moyens logistique' => 'GP Pool',
                        'Autres demandes' => 'Autres'
                    ]
                ],
                'UGBT' => [
                    'name' => 'Unité Gestion Baux & Taxes',
                    'natures' => [
                        'Demande visite locaux' => 'Gp PB & BT',
                        'Traitement baux & taxes' => 'Gp PB & BT',
                        'Traitement locations' => 'Gp PB & BT',
                        'Autres demandes' => 'Autres'
                    ]
                ]
            ]
        ],
        'SEG' => [
            'name' => 'Service Entretien Général',
            'unites' => [
                'UTGC' => [
                    'name' => 'Unité Travaux Génie Civile',
                    'natures' => [
                        'Travaux de climatisation' => 'Equipe Froid',
                        'Menuiserie' => 'Equipe Menuiserie',
                        'Plomberie' => 'Equipe Travaux',
                        'Maçonnerie' => 'Equipe Travaux',
                        'Etancheité' => 'Equipe Travaux',
                        'Autres travaux génie civile' => 'Equipe Travaux'
                    ]
                ],
                'UMR' => [
                    'name' => 'Unité Matériel Roulant',
                    'natures' => [
                        'Remplacement de pneu' => 'CGM',
                        'Remplacement de Batterie' => 'CGM',
                        'Travaux Mécanique' => 'CGM',
                        'Climatisation Auto' => 'CGM',
                        'Travaux de Carosserie' => 'CGM',
                        'Visite technique' => 'CGM',
                        'Entretien Périodique' => 'CGM',
                        'Autres demandes' => 'Autres'
                    ]
                ]
            ]
        ]
    ],

    'nature_to_service_mapping' => [
        // SA - UAG - GMMB
        'Travaux ou Réparation Mobilier Bureau' => 'SA',
        'Aménagement Bureau' => 'SA',
        'Réhoussage Mobilier Bureau' => 'SA',
        'Remplacement Serrure ou Canot' => 'SA',
        'Opération de Désencombrement' => 'SA',
        // SA - UAG - GTE
        'Gestion téléphone &/ou puce' => 'SA',
        'Gestion Eau' => 'SA',
        // SA - UPNS - GNS
        'Opération Dératisation ou Désinfectisation' => 'SA',
        'Renfort sécurité' => 'SA',
        'Entretien et nettoiement locaux' => 'SA',
        'Désherbage' => 'SA',
        // SA - UPNS - GP Pool
        'Entretien pool auto' => 'SA',
        'Demande de moyens logistique' => 'SA',
        // SA - UGBT
        'Demande visite locaux' => 'SA',
        'Traitement baux & taxes' => 'SA',
        'Traitement locations' => 'SA',
        // SEG - UTGC
        'Travaux de climatisation' => 'SEG',
        'Menuiserie' => 'SEG',
        'Plomberie' => 'SEG',
        'Maçonnerie' => 'SEG',
        'Etancheité' => 'SEG',
        'Autres travaux génie civile' => 'SEG',
        // SEG - UMR
        'Remplacement de pneu' => 'SEG',
        'Remplacement de Batterie' => 'SEG',
        'Travaux Mécanique' => 'SEG',
        'Climatisation Auto' => 'SEG',
        'Travaux de Carosserie' => 'SEG',
        'Visite technique' => 'SEG',
        'Entretien Périodique' => 'SEG'
        // Note: 'Autres demandes' est géré dynamiquement par ServiceRedirectionHelper
        // selon l'unité sélectionnée (SA pour UAG/UPNS/UGBT, SEG pour UTGC/UMR)
    ]
];
