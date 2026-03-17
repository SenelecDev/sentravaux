<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    // Prénoms sénégalais réalistes
    protected array $prenomsMasculins = [
        'Moussa', 'Amadou', 'Ibrahima', 'Ousmane', 'Mamadou', 'Abdoulaye', 'Cheikh',
        'Modou', 'Papa', 'Serigne', 'Babacar', 'Aliou', 'El Hadji', 'Djibril',
        'Malick', 'Souleymane', 'Tidiane', 'Mbaye', 'Mor', 'Bassirou',
        'Mouhamadou', 'Alioune', 'Bara', 'Boubacar', 'Daouda', 'Fallou',
        'Habib', 'Lamine', 'Makhtar', 'Saliou', 'Thierno', 'Assane',
    ];

    protected array $prenomsFeminins = [
        'Fatou', 'Aminata', 'Awa', 'Mariama', 'Ndeye', 'Khady', 'Aissatou',
        'Mame', 'Astou', 'Coumba', 'Dieynaba', 'Ndèye Fatou', 'Oumou',
        'Rokhaya', 'Sokhna', 'Yacine', 'Bineta', 'Adja', 'Rama', 'Seynabou',
        'Ngoné', 'Diary', 'Maïmouna', 'Penda', 'Soda', 'Tening',
    ];

    protected array $noms = [
        'DIOP', 'NDIAYE', 'FALL', 'SECK', 'SOW', 'BA', 'DIALLO', 'GUEYE',
        'MBAYE', 'NIANG', 'THIAM', 'SARR', 'WADE', 'DIOUF', 'KANE', 'CISSE',
        'TOURE', 'TRAORE', 'FAYE', 'DIENG', 'LY', 'MBOW', 'TALL', 'SY',
        'CAMARA', 'SENE', 'MBENGUE', 'NDOYE', 'BARRY', 'SAMB', 'SAKHO',
        'KEITA', 'KEBE', 'DIA', 'DRAME', 'DIAGNE', 'BADJI', 'GOMIS',
    ];

    protected array $services = [
        'DSI/DDES', 'DESA', 'Service Dispatching', 'Service Exploitation Postes',
        'Direction Principale Réseaux (DPR)', 'Direction Technique',
        'Direction des Ressources Humaines', 'Direction Financière',
        'Cellule de Coordination des Applications Metiers', 'Service Transport Energie',
        'Service Maintenance Réseau', 'Direction Production',
        'Direction Commerciale', 'Service Planification', 'Direction Juridique',
        'Service Gestion du Personnel', 'Service Comptabilité',
        'Direction Approvisionnement', 'Service Moyens Généraux',
        'Service Formation', 'Service Sécurité', 'Service Qualité',
    ];

    protected array $directions = [
        'Direction Principale Réseaux (DPR)', 'Direction de la Production',
        'Direction Technique', 'Direction des Systèmes d\'Information',
        'Direction des Ressources Humaines', 'Direction Financière et Comptable',
        'Direction Commerciale', 'Direction de la Planification',
        'Direction Juridique', 'Direction de l\'Approvisionnement',
        'Direction Générale', 'Délégation Régionale Nord',
        'Délégation Régionale Sud', 'Délégation Régionale Centre',
    ];

    protected array $postes = [
        'Ingénieur Réseau', 'Technicien Supérieur', 'Chef de Service',
        'Adjoint au Chef de Service', 'Chef de Département', 'Ingénieur d\'Études',
        'Technicien d\'Exploitation', 'Agent de Maintenance', 'Contremaître',
        'Chef de Division', 'Analyste Développeur', 'Administrateur Système',
        'Gestionnaire de Projet', 'Assistant administratif', 'Comptable',
        'Chargé de Communication', 'Responsable RH', 'Auditeur Interne',
    ];

    protected array $departements = [
        'Département Exploitation', 'Département Maintenance', 'Département Études',
        'Département Informatique', 'Département Finance', 'Département RH',
        'Département Commercial', 'Département Planification', 'Département Juridique',
        'Département Approvisionnement', 'Département Qualité',
    ];

    public function definition(): array
    {
        $isFemale = fake()->boolean(40);
        $prenom = $isFemale
            ? fake()->randomElement($this->prenomsFeminins)
            : fake()->randomElement($this->prenomsMasculins);
        $nom = fake()->randomElement($this->noms);

        return [
            'matricule' => $matricule = 'C' . fake()->unique()->numerify('#####'),
            'name' => trim("{$prenom} {$nom}"),
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => strtolower(str_replace(' ', '.', $prenom)) . '.' . strtolower($nom) . '.' . substr($matricule, 1) . '@senelec.sn',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'telephone' => '+221 ' . fake()->randomElement(['77', '78', '76', '70']) . ' ' . fake()->numerify('### ## ##'),
            'service' => fake()->randomElement($this->services),
            'direction' => fake()->randomElement($this->directions),
            'departement' => fake()->randomElement($this->departements),
            'poste' => fake()->randomElement($this->postes),
            'organisation' => 'SENELEC',
            'entreprise' => 'SENELEC',
            'is_active' => fake()->boolean(95),
            'oracle_synced_at' => fake()->boolean(85) ? fake()->dateTimeBetween('-6 months', 'now') : null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->afterCreating(function ($user) {
            $user->assignRole('admin');
        });
    }

    public function user(): static
    {
        return $this->afterCreating(function ($user) {
            $user->assignRole('user');
        });
    }
}
