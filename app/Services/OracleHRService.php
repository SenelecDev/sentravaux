<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OracleHRService
{
    protected string $connection = 'oracle';

    /**
     * Get employee by matricule from Oracle HR using SENELEC's optimized query
     * Uses XXSLC_UTILITIES_PKG package for hierarchy retrieval
     */
    public function getEmployeeByMatricule(string $matricule): ?array
    {
        $cacheKey = "oracle_employee_{$matricule}";

        return Cache::remember($cacheKey, now()->addHours(1), function() use ($matricule) {
            try {
                $sql = "
                    SELECT DISTINCT
                        pop.person_id AS person_id,
                        pop.employee_number AS matricule,
                        pop.first_name AS prenom,
                        pop.last_name AS nom,
                        pop.email_address AS email,
                        org.name AS organisation,
                        org.organization_id AS organization_id,
                        org.attribute4 AS centre_responsabilite,
                        org.type AS org_type,
                        HR_GENERAL.DECODE_POSITION_LATEST_NAME(paaf.POSITION_ID) AS poste,
                        pj.name AS fonction,
                        pg.name AS grade_fonction,
                        paaf.ASS_ATTRIBUTE11 AS date_promo_gf,
                        psp.spinal_point AS niveau_remuneration,
                        paaf.ASS_ATTRIBUTE12 AS date_promo_nr,
                        DECODE(ptu.person_type_id, 46, 'Cadre', 'Maitrise') AS college,
                        loc.address_line_1 AS lieu,
                        loc.location_code AS localisation,
                        paaf.assignment_id AS assignment_id,
                        paaf.grade_id AS grade_id,
                        
                        -- Direction Générale (DG)
                        XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DG') AS direction_generale,
                        (SELECT org2.organization_id FROM hr_all_organization_units org2 
                         WHERE org2.name = XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DG') 
                         AND org2.type = 'DG' AND ROWNUM = 1) AS direction_generale_id,
                        (SELECT org2.attribute4 FROM hr_all_organization_units org2 
                         WHERE org2.name = XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DG') 
                         AND org2.type = 'DG' AND ROWNUM = 1) AS direction_generale_cr,
                        
                        -- Direction Principale (DIRP)
                        XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DIRP') AS direction_principale,
                        (SELECT org2.organization_id FROM hr_all_organization_units org2 
                         WHERE org2.name = XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DIRP') 
                         AND org2.type = 'DIRP' AND ROWNUM = 1) AS direction_principale_id,
                        (SELECT org2.attribute4 FROM hr_all_organization_units org2 
                         WHERE org2.name = XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DIRP') 
                         AND org2.type = 'DIRP' AND ROWNUM = 1) AS direction_principale_cr,
                        
                        -- Direction (DIR)
                        XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DIR') AS direction,
                        (SELECT org2.organization_id FROM hr_all_organization_units org2 
                         WHERE org2.name = XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DIR') 
                         AND org2.type = 'DIR' AND ROWNUM = 1) AS direction_id,
                        (SELECT org2.attribute4 FROM hr_all_organization_units org2 
                         WHERE org2.name = XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DIR') 
                         AND org2.type = 'DIR' AND ROWNUM = 1) AS direction_cr,
                        
                        -- Délégation (DEL)
                        XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DEL') AS delegation,
                        (SELECT org2.organization_id FROM hr_all_organization_units org2 
                         WHERE org2.name = XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DEL') 
                         AND org2.type = 'DEL' AND ROWNUM = 1) AS delegation_id,
                        (SELECT org2.attribute4 FROM hr_all_organization_units org2 
                         WHERE org2.name = XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DEL') 
                         AND org2.type = 'DEL' AND ROWNUM = 1) AS delegation_cr,
                        
                        -- Département (DEP)
                        XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DEP') AS departement,
                        (SELECT org2.organization_id FROM hr_all_organization_units org2 
                         WHERE org2.name = XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DEP') 
                         AND org2.type = 'DEP' AND ROWNUM = 1) AS departement_id,
                        (SELECT org2.attribute4 FROM hr_all_organization_units org2 
                         WHERE org2.name = XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DEP') 
                         AND org2.type = 'DEP' AND ROWNUM = 1) AS departement_cr,
                        
                        -- Service (SER)
                        XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'SER') AS service,
                        (SELECT org2.organization_id FROM hr_all_organization_units org2 
                         WHERE org2.name = XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'SER') 
                         AND org2.type = 'SER' AND ROWNUM = 1) AS service_id,
                        (SELECT org2.attribute4 FROM hr_all_organization_units org2 
                         WHERE org2.name = XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'SER') 
                         AND org2.type = 'SER' AND ROWNUM = 1) AS service_cr

                    FROM
                        per_periods_of_service pos,
                        per_all_people_f pop,
                        pay_people_groups ppg,
                        per_all_assignments_f paaf,
                        per_grades pg,
                        per_spinal_point_steps_f psps,
                        per_spinal_point_placements_f pspp,
                        per_spinal_points psp,
                        per_jobs pj,
                        PER_PERSON_TYPE_USAGES_x ptu,
                        hr_organization_units org,
                        hr_locations loc
                    WHERE
                        pop.person_id = paaf.person_id
                        AND pg.grade_id(+) = paaf.grade_id
                        AND pj.job_id(+) = paaf.job_id
                        AND pop.person_id = ptu.person_id
                        AND ppg.people_group_id(+) = paaf.people_group_id
                        AND org.organization_id = paaf.organization_id
                        AND paaf.assignment_status_type_id IN ('1', '46', '45', '43', '2', '44', '5')
                        AND loc.location_id(+) = paaf.location_id
                        AND paaf.assignment_id = pspp.assignment_id(+)
                        AND psp.spinal_point_id(+) = psps.spinal_point_id
                        AND psps.step_id(+) = pspp.step_id
                        AND pop.employee_number = :matricule
                        AND SYSDATE BETWEEN pop.effective_start_date AND pop.effective_end_date
                        AND SYSDATE BETWEEN paaf.effective_start_date AND paaf.effective_end_date
                        AND (pspp.assignment_id IS NULL OR SYSDATE BETWEEN pspp.effective_start_date AND pspp.effective_end_date)
                        AND (psps.step_id IS NULL OR SYSDATE BETWEEN psps.effective_start_date AND psps.effective_end_date)
                    ORDER BY pop.employee_number
                ";

                $results = DB::connection($this->connection)->select($sql, ['matricule' => $matricule]);

                if (empty($results)) {
                    return null;
                }

                $employee = $results[0];

                return [
                    'person_id' => $employee->person_id,
                    'matricule' => $employee->matricule,
                    'nom' => $employee->nom,
                    'prenom' => $employee->prenom,
                    'email' => $employee->email,
                    'fonction' => $employee->fonction,
                    'poste' => $employee->poste,
                    'grade_fonction' => $employee->grade_fonction,
                    'grade_id' => $employee->grade_id,
                    'niveau_remuneration' => $employee->niveau_remuneration,
                    'college' => $employee->college,
                    'date_promo_gf' => $employee->date_promo_gf,
                    'date_promo_nr' => $employee->date_promo_nr,
                    'organisation' => $employee->organisation,
                    'organisation_type' => $employee->org_type,
                    'organization_id' => $employee->organization_id,
                    'centre_responsabilite' => $employee->centre_responsabilite,
                    'localisation' => $employee->localisation,
                    'lieu' => $employee->lieu,
                    'assignment_id' => $employee->assignment_id,
                    // Hierarchy - using XXSLC_UTILITIES_PKG
                    'direction_generale_id' => $employee->direction_generale_id,
                    'direction_generale' => $employee->direction_generale,
                    'direction_generale_cr' => $employee->direction_generale_cr,
                    'direction_principale_id' => $employee->direction_principale_id,
                    'direction_principale' => $employee->direction_principale,
                    'direction_principale_cr' => $employee->direction_principale_cr,
                    'direction_id' => $employee->direction_id,
                    'direction' => $employee->direction,
                    'direction_cr' => $employee->direction_cr,
                    'delegation_id' => $employee->delegation_id,
                    'delegation' => $employee->delegation,
                    'delegation_cr' => $employee->delegation_cr,
                    'departement_id' => $employee->departement_id,
                    'departement' => $employee->departement,
                    'departement_cr' => $employee->departement_cr,
                    'service_id' => $employee->service_id,
                    'service' => $employee->service,
                    'service_cr' => $employee->service_cr,
                ];

            } catch (\Exception $e) {
                Log::error('Oracle HR Service Error: ' . $e->getMessage());
                // Fallback to simple query if XXSLC_UTILITIES_PKG is not available
                return $this->getEmployeeByMatriculeSimple($matricule);
            }
        });
    }

    /**
     * Fallback method - Simple query without XXSLC_UTILITIES_PKG
     */
    protected function getEmployeeByMatriculeSimple(string $matricule): ?array
    {
        try {
            $employee = DB::connection($this->connection)
                ->table('APPS.PER_ALL_PEOPLE_F as papf')
                ->join('APPS.PER_ALL_ASSIGNMENTS_F as paaf', function($join) {
                    $join->on('papf.PERSON_ID', '=', 'paaf.PERSON_ID')
                        ->whereRaw('SYSDATE BETWEEN paaf.EFFECTIVE_START_DATE AND paaf.EFFECTIVE_END_DATE')
                        ->where('paaf.PRIMARY_FLAG', 'Y');
                })
                ->leftJoin('APPS.HR_ALL_ORGANIZATION_UNITS as haou', 'paaf.ORGANIZATION_ID', '=', 'haou.ORGANIZATION_ID')
                ->leftJoin('APPS.PER_JOBS as pj', 'paaf.JOB_ID', '=', 'pj.JOB_ID')
                ->leftJoin('APPS.PER_GRADES as pg', 'paaf.GRADE_ID', '=', 'pg.GRADE_ID')
                ->leftJoin('APPS.PER_SPINAL_POINT_PLACEMENTS_F as pspp', function($join) {
                    $join->on('paaf.ASSIGNMENT_ID', '=', 'pspp.ASSIGNMENT_ID')
                        ->whereRaw('SYSDATE BETWEEN pspp.EFFECTIVE_START_DATE AND pspp.EFFECTIVE_END_DATE');
                })
                ->leftJoin('APPS.PER_SPINAL_POINT_STEPS_F as psps', function($join) {
                    $join->on('pspp.STEP_ID', '=', 'psps.STEP_ID')
                        ->whereRaw('SYSDATE BETWEEN psps.EFFECTIVE_START_DATE AND psps.EFFECTIVE_END_DATE');
                })
                ->leftJoin('APPS.PER_SPINAL_POINTS as psp', 'psps.SPINAL_POINT_ID', '=', 'psp.SPINAL_POINT_ID')
                ->leftJoin('APPS.HR_LOCATIONS_ALL as hla', 'paaf.LOCATION_ID', '=', 'hla.LOCATION_ID')
                ->where('papf.EMPLOYEE_NUMBER', $matricule)
                ->whereRaw('SYSDATE BETWEEN papf.EFFECTIVE_START_DATE AND papf.EFFECTIVE_END_DATE')
                ->select([
                    'papf.PERSON_ID as person_id',
                    'papf.EMPLOYEE_NUMBER as matricule',
                    'papf.LAST_NAME as nom',
                    'papf.FIRST_NAME as prenom',
                    'papf.EMAIL_ADDRESS as email',
                    'pj.NAME as fonction',
                    'pg.NAME as grade_fonction',
                    'haou.NAME as organisation',
                    'haou.TYPE as org_type',
                    'haou.ATTRIBUTE4 as centre_responsabilite',
                    'hla.LOCATION_CODE as localisation',
                    'hla.ADDRESS_LINE_1 as lieu',
                    'psp.SPINAL_POINT as niveau_remuneration',
                    'paaf.ASSIGNMENT_ID as assignment_id',
                    'paaf.ORGANIZATION_ID as organization_id',
                    'paaf.GRADE_ID as grade_id',
                ])
                ->first();

            if (!$employee) {
                return null;
            }

            // Get hierarchy using loop method
            $hierarchy = $this->getOrganizationHierarchy($employee->organization_id);

            return [
                'person_id' => $employee->person_id,
                'matricule' => $employee->matricule,
                'nom' => $employee->nom,
                'prenom' => $employee->prenom,
                'email' => $employee->email,
                'fonction' => $employee->fonction,
                'poste' => $employee->fonction,
                'grade_fonction' => $employee->grade_fonction,
                'grade_id' => $employee->grade_id,
                'niveau_remuneration' => $employee->niveau_remuneration,
                'college' => null,
                'date_promo_gf' => null,
                'date_promo_nr' => null,
                'organisation' => $employee->organisation,
                'organisation_type' => $employee->org_type,
                'organization_id' => $employee->organization_id,
                'centre_responsabilite' => $employee->centre_responsabilite,
                'localisation' => $employee->localisation,
                'lieu' => $employee->lieu,
                'assignment_id' => $employee->assignment_id,
                'direction_generale_id' => $hierarchy['dg']['id'] ?? null,
                'direction_generale' => $hierarchy['dg']['name'] ?? null,
                'direction_principale_id' => $hierarchy['dirp']['id'] ?? null,
                'direction_principale' => $hierarchy['dirp']['name'] ?? null,
                'direction_id' => $hierarchy['dir']['id'] ?? null,
                'direction' => $hierarchy['dir']['name'] ?? null,
                'delegation_id' => $hierarchy['del']['id'] ?? null,
                'delegation' => $hierarchy['del']['name'] ?? null,
                'departement_id' => $hierarchy['dep']['id'] ?? null,
                'departement' => $hierarchy['dep']['name'] ?? null,
                'service_id' => $hierarchy['ser']['id'] ?? null,
                'service' => $hierarchy['ser']['name'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Oracle HR Service Error (Simple): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get organization hierarchy by climbing up the tree (fallback method)
     */
    protected function getOrganizationHierarchy($orgId): array
    {
        $hierarchy = [
            'dg' => null,
            'dirp' => null,
            'dir' => null,
            'dep' => null,
            'ser' => null,
            'del' => null,
        ];

        if (!$orgId) {
            return $hierarchy;
        }

        $visited = [];
        $currentId = $orgId;
        $maxDepth = 10;

        while ($currentId && $maxDepth > 0) {
            if (in_array($currentId, $visited)) {
                break;
            }
            $visited[] = $currentId;
            $maxDepth--;

            $org = DB::connection($this->connection)
                ->table('APPS.HR_ALL_ORGANIZATION_UNITS')
                ->where('ORGANIZATION_ID', $currentId)
                ->select('ORGANIZATION_ID', 'NAME', 'TYPE')
                ->first();

            if (!$org) {
                break;
            }

            $type = strtolower($org->type ?? '');
            $data = ['id' => $org->organization_id, 'name' => $org->name];

            switch ($type) {
                case 'dg':
                    $hierarchy['dg'] = $data;
                    break;
                case 'dirp':
                    $hierarchy['dirp'] = $data;
                    break;
                case 'dir':
                    $hierarchy['dir'] = $data;
                    break;
                case 'dep':
                    $hierarchy['dep'] = $data;
                    break;
                case 'ser':
                    $hierarchy['ser'] = $data;
                    break;
                case 'del':
                    $hierarchy['del'] = $data;
                    break;
            }

            $parent = DB::connection($this->connection)
                ->table('APPS.PER_ORG_STRUCTURE_ELEMENTS')
                ->where('ORGANIZATION_ID_CHILD', $currentId)
                ->select('ORGANIZATION_ID_PARENT')
                ->first();

            $currentId = $parent ? $parent->organization_id_parent : null;
        }

        return $hierarchy;
    }

    /**
     * Get all employees from Oracle HR with full details using XXSLC_UTILITIES_PKG
     * Compatible avec Oracle 11g (utilise ROWNUM au lieu de OFFSET/FETCH)
     * Utilise la même structure que getEmployeeByMatricule qui fonctionne
     */
    public function getAllEmployees(int $limit = 1000, int $offset = 0): array
    {
        try {
            // Oracle 11g compatible: utiliser ROWNUM pour la pagination
            // Structure: SELECT * FROM (SELECT avec ROWNUM) WHERE rn BETWEEN offset AND maxRow
            // On utilise une sous-requête avec DISTINCT d'abord pour éviter les duplicatas
            $maxRow = $offset + $limit;
            
            $sql = "
                SELECT * FROM (
                    SELECT DISTINCT
                        pop.person_id AS person_id,
                        pop.employee_number AS matricule,
                        pop.first_name AS prenom,
                        pop.last_name AS nom,
                        pop.email_address AS email,
                        org.name AS organisation,
                        org.organization_id AS organization_id,
                        org.attribute4 AS centre_responsabilite,
                        org.type AS org_type,
                        HR_GENERAL.DECODE_POSITION_LATEST_NAME(paaf.POSITION_ID) AS poste,
                        pj.name AS fonction,
                        pg.name AS grade_fonction,
                        paaf.ASS_ATTRIBUTE11 AS date_promo_gf,
                        psp.spinal_point AS niveau_remuneration,
                        paaf.ASS_ATTRIBUTE12 AS date_promo_nr,
                        DECODE(ptu.person_type_id, 46, 'Cadre', 'Maitrise') AS college,
                        loc.address_line_1 AS lieu,
                        loc.location_code AS localisation,
                        paaf.assignment_id AS assignment_id,
                        paaf.grade_id AS grade_id,
                        
                        -- Hierarchy using XXSLC_UTILITIES_PKG
                        XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DG') AS direction_generale,
                        XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DIRP') AS direction_principale,
                        XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DIR') AS direction,
                        XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DEL') AS delegation,
                        XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'DEP') AS departement,
                        XXSLC_UTILITIES_PKG.get_org_by_type(paaf.ORGANIZATION_ID, 'SER') AS service
                    FROM
                        per_periods_of_service pos,
                        per_all_people_f pop,
                        pay_people_groups ppg,
                        per_all_assignments_f paaf,
                        per_grades pg,
                        per_spinal_point_steps_f psps,
                        per_spinal_point_placements_f pspp,
                        per_spinal_points psp,
                        per_jobs pj,
                        PER_PERSON_TYPE_USAGES_x ptu,
                        hr_organization_units org,
                        hr_locations loc
                    WHERE
                        pop.person_id = paaf.person_id
                        AND pop.person_id = pos.person_id
                        AND pos.actual_termination_date IS NULL
                        AND pg.grade_id(+) = paaf.grade_id
                        AND pj.job_id(+) = paaf.job_id
                        AND pop.person_id = ptu.person_id
                        AND ppg.people_group_id(+) = paaf.people_group_id
                        AND org.organization_id = paaf.organization_id
                        AND paaf.assignment_status_type_id IN ('1', '46', '45', '43', '2', '44', '5')
                        AND loc.location_id(+) = paaf.location_id
                        AND paaf.assignment_id = pspp.assignment_id(+)
                        AND psp.spinal_point_id(+) = psps.spinal_point_id
                        AND psps.step_id(+) = pspp.step_id
                        AND pop.employee_number IS NOT NULL
                        AND SYSDATE BETWEEN pop.effective_start_date AND pop.effective_end_date
                        AND SYSDATE BETWEEN paaf.effective_start_date AND paaf.effective_end_date
                        AND (pspp.assignment_id IS NULL OR SYSDATE BETWEEN pspp.effective_start_date AND pspp.effective_end_date)
                        AND (psps.step_id IS NULL OR SYSDATE BETWEEN psps.effective_start_date AND psps.effective_end_date)
                    ORDER BY pop.employee_number
                )
                WHERE ROWNUM <= :limit
            ";

            $employees = DB::connection($this->connection)
                ->select($sql, ['limit' => $limit]);

            return collect($employees)->map(function($emp) {
                return [
                    'person_id' => $emp->person_id,
                    'matricule' => $emp->matricule,
                    'nom' => $emp->nom,
                    'prenom' => $emp->prenom,
                    'email' => $emp->email,
                    'fonction' => $emp->fonction,
                    'poste' => $emp->poste,
                    'grade_fonction' => $emp->grade_fonction,
                    'niveau_remuneration' => $emp->niveau_remuneration,
                    'college' => $emp->college,
                    'date_promo_gf' => $emp->date_promo_gf,
                    'date_promo_nr' => $emp->date_promo_nr,
                    'organisation' => $emp->organisation,
                    'organisation_type' => $emp->org_type,
                    'organization_id' => $emp->organization_id,
                    'centre_responsabilite' => $emp->centre_responsabilite,
                    'localisation' => $emp->localisation,
                    'lieu' => $emp->lieu,
                    'assignment_id' => $emp->assignment_id,
                    'grade_id' => $emp->grade_id,
                    // Hierarchy
                    'direction_generale' => $emp->direction_generale,
                    'direction_principale' => $emp->direction_principale,
                    'direction' => $emp->direction,
                    'delegation' => $emp->delegation,
                    'departement' => $emp->departement,
                    'service' => $emp->service,
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::warning('Oracle HR getAllEmployees requête complète échouée, utilisation du fallback: ' . $e->getMessage());
            // Fallback to simple query
            return $this->getAllEmployeesSimple($limit, $offset);
        }
    }

    /**
     * Fallback: Get all employees with simple query (without XXSLC_UTILITIES_PKG)
     */
    protected function getAllEmployeesSimple(int $limit = 1000, int $offset = 0): array
    {
        try {
            $employees = DB::connection($this->connection)
                ->table('APPS.PER_ALL_PEOPLE_F as papf')
                ->join('APPS.PER_ALL_ASSIGNMENTS_F as paaf', function($join) {
                    $join->on('papf.PERSON_ID', '=', 'paaf.PERSON_ID')
                        ->whereRaw('SYSDATE BETWEEN paaf.EFFECTIVE_START_DATE AND paaf.EFFECTIVE_END_DATE')
                        ->where('paaf.PRIMARY_FLAG', 'Y')
                        ->where('paaf.ASSIGNMENT_TYPE', 'E');
                })
                ->leftJoin('APPS.HR_ALL_ORGANIZATION_UNITS as haou', 'paaf.ORGANIZATION_ID', '=', 'haou.ORGANIZATION_ID')
                ->leftJoin('APPS.PER_JOBS as pj', 'paaf.JOB_ID', '=', 'pj.JOB_ID')
                ->whereRaw('SYSDATE BETWEEN papf.EFFECTIVE_START_DATE AND papf.EFFECTIVE_END_DATE')
                ->whereNotNull('papf.EMPLOYEE_NUMBER')
                ->select([
                    'papf.PERSON_ID as person_id',
                    'papf.EMPLOYEE_NUMBER as matricule',
                    'papf.LAST_NAME as nom',
                    'papf.FIRST_NAME as prenom',
                    'papf.EMAIL_ADDRESS as email',
                    'pj.NAME as fonction',
                    'haou.NAME as organisation',
                ])
                ->offset($offset)
                ->limit($limit)
                ->get();

            return $employees->map(function($emp) {
                $orgParts = $this->parseOrganization($emp->organisation);
                return [
                    'person_id' => $emp->person_id,
                    'matricule' => $emp->matricule,
                    'nom' => $emp->nom,
                    'prenom' => $emp->prenom,
                    'email' => $emp->email,
                    'fonction' => $emp->fonction,
                    'direction' => $orgParts['direction'],
                    'departement' => $orgParts['departement'],
                    'service' => $orgParts['service'],
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('Oracle HR Service Error (getAllEmployeesSimple): ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search employees by name or matricule
     */
    public function searchEmployees(string $query, int $limit = 20): array
    {
        try {
            $employees = DB::connection($this->connection)
                ->table('APPS.PER_ALL_PEOPLE_F as papf')
                ->join('APPS.PER_ALL_ASSIGNMENTS_F as paaf', function($join) {
                    $join->on('papf.PERSON_ID', '=', 'paaf.PERSON_ID')
                        ->whereRaw('SYSDATE BETWEEN paaf.EFFECTIVE_START_DATE AND paaf.EFFECTIVE_END_DATE')
                        ->where('paaf.PRIMARY_FLAG', 'Y');
                })
                ->leftJoin('APPS.PER_JOBS as pj', 'paaf.JOB_ID', '=', 'pj.JOB_ID')
                ->whereRaw('SYSDATE BETWEEN papf.EFFECTIVE_START_DATE AND papf.EFFECTIVE_END_DATE')
                ->where(function($q) use ($query) {
                    // Oracle: UPPER pour insensibilité à la casse
                    $pattern = '%' . $query . '%';
                    $q->whereRaw('UPPER(papf.EMPLOYEE_NUMBER) LIKE UPPER(?)', [$pattern])
                      ->orWhereRaw('UPPER(papf.LAST_NAME) LIKE UPPER(?)', [$pattern])
                      ->orWhereRaw('UPPER(papf.FIRST_NAME) LIKE UPPER(?)', [$pattern]);
                })
                ->select([
                    'papf.PERSON_ID as person_id',
                    'papf.EMPLOYEE_NUMBER as matricule',
                    'papf.LAST_NAME as nom',
                    'papf.FIRST_NAME as prenom',
                    'pj.NAME as fonction',
                ])
                ->limit($limit)
                ->get();

            return $employees->map(function($emp) {
                return [
                    'person_id' => $emp->person_id,
                    'matricule' => $emp->matricule,
                    'nom' => $emp->nom,
                    'prenom' => $emp->prenom,
                    'full_name' => trim($emp->prenom . ' ' . $emp->nom),
                    'fonction' => $emp->fonction,
                    'initials' => strtoupper(substr($emp->prenom, 0, 1) . substr($emp->nom, 0, 1)),
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('Oracle HR Service Error (searchEmployees): ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get organizations list
     */
    public function getOrganizations(): array
    {
        return Cache::remember('oracle_organizations', now()->addHours(24), function() {
            try {
                return DB::connection($this->connection)
                    ->table('APPS.HR_ALL_ORGANIZATION_UNITS')
                    ->whereNull('DATE_TO')
                    ->orWhere('DATE_TO', '>', now())
                    ->orderBy('NAME')
                    ->pluck('NAME', 'ORGANIZATION_ID')
                    ->toArray();
            } catch (\Exception $e) {
                Log::error('Oracle HR Service Error (getOrganizations): ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Parse organization name to extract direction/departement/service
     * SENELEC organization names are typically structured hierarchically
     */
    protected function parseOrganization(?string $orgName): array
    {
        $result = [
            'direction' => null,
            'departement' => null,
            'service' => null,
        ];

        if (!$orgName) {
            return $result;
        }

        // SENELEC org names can be separated by dots, dashes or other delimiters
        // Example: "DRH.DEPARTEMENT FORMATION.SERVICE EVALUATION"
        $parts = preg_split('/[.\-\/]/', $orgName);
        $parts = array_map('trim', array_filter($parts));

        if (count($parts) >= 1) {
            $result['direction'] = $parts[0];
        }
        if (count($parts) >= 2) {
            $result['departement'] = $parts[1];
        }
        if (count($parts) >= 3) {
            $result['service'] = $parts[2];
        }

        return $result;
    }

    /**
     * Sync employee data from Oracle to local database
     * Updates all employee information including hierarchy
     */
    public function syncEmployee(string $matricule): bool
    {
        $oracleData = $this->getEmployeeByMatricule($matricule);

        if (!$oracleData) {
            Log::warning("Oracle Sync: No data found for matricule {$matricule}");
            return false;
        }

        try {
            $user = \App\Models\User::where('matricule', $matricule)->first();

            if (!$user) {
                Log::warning("Oracle Sync: User not found in local DB for matricule {$matricule}");
                return false;
            }

            $user->update([
                // Basic info
                'oracle_person_id' => $oracleData['person_id'] ?? null,
                'nom' => $oracleData['nom'] ?? $user->nom,
                'prenom' => $oracleData['prenom'] ?? $user->prenom,
                'email' => $oracleData['email'] ?? $user->email,
                
                // Organization
                'organisation' => $oracleData['organisation'] ?? null,
                'oracle_org_id' => $oracleData['organization_id'] ?? null,
                'centre_responsabilite' => $oracleData['centre_responsabilite'] ?? null,
                
                // Position info
                'poste' => $oracleData['poste'] ?? null,
                'fonction_oracle' => $oracleData['fonction'] ?? null,
                'grade_fonction' => $oracleData['grade_fonction'] ?? null,
                'niveau_remuneration' => $oracleData['niveau_remuneration'] ?? null,
                'college' => $oracleData['college'] ?? null,
                
                // Hierarchy - Direction Générale
                'direction_generale_id' => $oracleData['direction_generale_id'] ?? null,
                'direction_generale' => $oracleData['direction_generale'] ?? null,
                
                // Hierarchy - Direction Principale
                'direction_principale_id' => $oracleData['direction_principale_id'] ?? null,
                'direction_principale' => $oracleData['direction_principale'] ?? null,
                
                // Hierarchy - Direction
                'direction_id' => $oracleData['direction_id'] ?? null,
                'direction' => $oracleData['direction'] ?? null,
                
                // Hierarchy - Délégation
                'delegation_id' => $oracleData['delegation_id'] ?? null,
                'delegation' => $oracleData['delegation'] ?? null,
                
                // Hierarchy - Département
                'departement_id' => $oracleData['departement_id'] ?? null,
                'departement' => $oracleData['departement'] ?? null,
                
                // Hierarchy - Service
                'service_id' => $oracleData['service_id'] ?? null,
                'service' => $oracleData['service'] ?? null,
                
                // Location
                'localisation' => $oracleData['localisation'] ?? $oracleData['lieu'] ?? null,
                
                // Sync timestamp
                'oracle_synced_at' => now(),
            ]);

            // Clear cache
            Cache::forget("oracle_employee_{$matricule}");

            Log::info("Oracle Sync: Successfully synced employee {$matricule} ({$oracleData['prenom']} {$oracleData['nom']})");

            return true;

        } catch (\Exception $e) {
            Log::error('Oracle Sync Error for ' . $matricule . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync multiple employees from Oracle to local database
     */
    public function syncEmployees(array $matricules): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($matricules as $matricule) {
            if ($this->syncEmployee($matricule)) {
                $results['success'][] = $matricule;
            } else {
                $results['failed'][] = $matricule;
            }
        }

        return $results;
    }

    /**
     * Sync all local users from Oracle
     */
    public function syncAllUsers(): array
    {
        $users = \App\Models\User::whereNotNull('matricule')
            ->where('matricule', '!=', '')
            ->pluck('matricule')
            ->toArray();

        return $this->syncEmployees($users);
    }

    /**
     * Check if Oracle connection is available
     */
    public function isAvailable(): bool
    {
        try {
            DB::connection($this->connection)->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // =========================================================================
    // REFERENCE DATA: Organizations & Locations
    // =========================================================================

    /**
     * Get all organizations of a given type from Oracle
     * Types: DG, DIRP, DIR, DEL, DEP, SER
     */
    public function getOrganizationsByType(string $type): array
    {
        try {
            $results = DB::connection($this->connection)
                ->table('APPS.HR_ALL_ORGANIZATION_UNITS')
                ->where('TYPE', strtoupper($type))
                ->where(function ($q) {
                    $q->whereNull('DATE_TO')
                      ->orWhereRaw('DATE_TO > SYSDATE');
                })
                ->select('ORGANIZATION_ID', 'NAME', 'ATTRIBUTE4', 'TYPE')
                ->orderBy('NAME')
                ->get();

            return $results->map(function ($org) {
                return [
                    'oracle_org_id' => $org->organization_id,
                    'libelle' => $org->name,
                    'centre_responsabilite' => $org->attribute4,
                    'type' => $org->type,
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error("Oracle HR: getOrganizationsByType({$type}) error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all active locations (sites) from Oracle
     */
    public function getLocations(): array
    {
        try {
            $results = DB::connection($this->connection)
                ->table('APPS.HR_LOCATIONS_ALL')
                ->whereNull('INACTIVE_DATE')
                ->select('LOCATION_ID', 'LOCATION_CODE', 'ADDRESS_LINE_1', 'TOWN_OR_CITY', 'REGION_1')
                ->orderBy('LOCATION_CODE')
                ->get();

            return $results->map(function ($loc) {
                return [
                    'oracle_location_id' => $loc->location_id,
                    'libelle' => $loc->location_code,
                    'code' => $loc->location_code,
                    'adresse' => $loc->address_line_1,
                    'ville' => $loc->town_or_city,
                    'region' => $loc->region_1,
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('Oracle HR: getLocations() error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all reference data at once (services, directions, sites, etc.)
     */
    public function getAllReferenceData(): array
    {
        return [
            'services' => $this->getOrganizationsByType('SER'),
            'directions_generales' => $this->getOrganizationsByType('DG'),
            'directions_principales' => $this->getOrganizationsByType('DIRP'),
            'directions' => $this->getOrganizationsByType('DIR'),
            'departements' => $this->getOrganizationsByType('DEP'),
            'delegations' => $this->getOrganizationsByType('DEL'),
            'sites' => $this->getLocations(),
        ];
    }
}
