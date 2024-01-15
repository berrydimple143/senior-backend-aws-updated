<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Str;
use DB;

class UsersExport implements FromCollection, WithHeadings
{
    public $type;
    public $typeVal;
    public $munVal;
    public $user;

    public function __construct($type, $mun, $value, $user) {
        $this->type = $type;
        $this->munVal = $mun;
        $this->typeVal = $value;
        $this->user = $user;
    }
    public function collection()
    {
        $usr = $this->user;
        $role = $usr->roles->pluck('name')[0];
        $mun = empty($usr->address->municipality) ? 'none' : $usr->address->municipality;
        $arr_mun = [];
        if($mun != "none") {
            if(Str::contains($mun, ',')) {
                $arr_mun = explode(',', $mun);
            } else {
                $arr_mun[] = $mun;
            }
        }

        if($this->type == "all") {
            if($role == "admin" or $role == "site lead")
            {
                return DB::table('users')
                    ->leftJoin('user_address', 'users.id', '=', 'user_address.user_id')
                    ->leftJoin('user_details', 'users.id', '=', 'user_details.user_id')
                    ->leftJoin('contact_details', 'users.id', '=', 'contact_details.user_id')
                    ->leftJoin('user_benefits', 'users.id', '=', 'user_benefits.user_id')
                    ->leftJoin('user_specializations', 'users.id', '=', 'user_specializations.user_id')
                    ->leftJoin('user_services', 'users.id', '=', 'user_services.user_id')
                    ->leftJoin('user_companions', 'users.id', '=', 'user_companions.user_id')
                    ->leftJoin('user_housings', 'users.id', '=', 'user_housings.user_id')
                    ->leftJoin('user_involvements', 'users.id', '=', 'user_involvements.user_id')
                    ->leftJoin('user_classification', 'users.id', '=', 'user_classification.user_id')
                    ->leftJoin('user_illness', 'users.id', '=', 'user_illness.user_id')
                    ->leftJoin('user_economic_statuses', 'users.id', '=', 'user_economic_statuses.user_id')
                    ->leftJoin('user_social_problems', 'users.id', '=', 'user_social_problems.user_id')
                    ->leftJoin('user_economic_problems', 'users.id', '=', 'user_economic_problems.user_id')
                    ->leftJoin('user_health_issues', 'users.id', '=', 'user_health_issues.user_id')
                    ->leftJoin('user_families', 'users.id', '=', 'user_families.user_id')
                    ->select('users.first_name AS first_name', 'users.middle_name AS middle_name', 'users.last_name AS last_name', 'users.extension_name AS extension_name', 'users.id_number AS id_number', 'user_details.gender AS gender', DB::raw("TIMESTAMPDIFF(YEAR, DATE(user_details.birth_date), current_date) AS age"), DB::raw('DATE_FORMAT(user_details.birth_date, "%b %d, %Y") as birth_date'), 'user_address.birth_place AS birth_place', 'user_address.address AS address', 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name', 'user_address.district_no AS district_no', 'users.email AS email', 'contact_details.phone AS phone', 'contact_details.mobile AS mobile', 'contact_details.messenger AS messenger', 'user_details.religion AS religion', 'user_details.blood_type AS blood_type', 'user_details.education AS education', 'user_details.employment_status AS employment_status', 'user_details.civil_status AS civil_status', 'user_details.member_status AS member_status', 'user_details.ethnic_origin AS ethnic_origin', 'user_details.language AS language', 'user_details.able_to_travel AS able_to_travel', 'user_details.active_in_politics AS active_in_politics', 'user_benefits.gsis AS gsis', 'user_benefits.sss AS sss', 'user_benefits.tin AS tin', 'user_benefits.philhealth AS philhealth', 'user_benefits.pension AS pension', 'user_benefits.association_id AS association_id', 'user_benefits.other_id AS other_id', 'contact_details.contact_person AS contact_person', 'contact_details.contact_person_number AS contact_person_number', 'user_specializations.area AS area', 'user_services.service AS service', 'user_companions.companion AS companion', 'user_housings.type AS type', 'user_involvements.activity AS activity', 'user_classification.classification AS classification', 'user_illness.sickness AS sickness', 'user_economic_statuses.source_of_income AS source_of_income', 'user_economic_statuses.assets AS assets', 'user_economic_statuses.income_range AS income_range', 'user_social_problems.problem AS social_problem', 'user_economic_problems.problem AS economic_problem', 'user_health_issues.problem AS health_issue', 'user_families.spouse_first_name AS spouse_first_name', 'user_families.spouse_middle_name AS spouse_middle_name', 'user_families.spouse_last_name AS spouse_last_name', 'user_families.spouse_extension_name AS spouse_extension_name', 'user_families.father_first_name AS father_first_name', 'user_families.father_middle_name AS father_middle_name', 'user_families.father_last_name AS father_last_name', 'user_families.father_extension_name AS father_extension_name', 'user_families.mother_first_name AS mother_first_name', 'user_families.mother_middle_name AS mother_middle_name', 'user_families.mother_last_name AS mother_last_name', 'user_families.mother_extension_name AS mother_extension_name', 'users.status AS status', 'users.created_at AS created_at', 'users.updated_at AS updated_at')
                    ->whereNotNull('users.id_number')->whereNull('users.deleted_at')->get();
            } else
            {
                return DB::table('users')
                    ->leftJoin('user_address', 'users.id', '=', 'user_address.user_id')
                    ->leftJoin('user_details', 'users.id', '=', 'user_details.user_id')
                    ->leftJoin('contact_details', 'users.id', '=', 'contact_details.user_id')
                    ->leftJoin('user_benefits', 'users.id', '=', 'user_benefits.user_id')
                    ->leftJoin('user_specializations', 'users.id', '=', 'user_specializations.user_id')
                    ->leftJoin('user_services', 'users.id', '=', 'user_services.user_id')
                    ->leftJoin('user_companions', 'users.id', '=', 'user_companions.user_id')
                    ->leftJoin('user_housings', 'users.id', '=', 'user_housings.user_id')
                    ->leftJoin('user_involvements', 'users.id', '=', 'user_involvements.user_id')
                    ->leftJoin('user_classification', 'users.id', '=', 'user_classification.user_id')
                    ->leftJoin('user_illness', 'users.id', '=', 'user_illness.user_id')
                    ->leftJoin('user_economic_statuses', 'users.id', '=', 'user_economic_statuses.user_id')
                    ->leftJoin('user_social_problems', 'users.id', '=', 'user_social_problems.user_id')
                    ->leftJoin('user_economic_problems', 'users.id', '=', 'user_economic_problems.user_id')
                    ->leftJoin('user_health_issues', 'users.id', '=', 'user_health_issues.user_id')
                    ->leftJoin('user_families', 'users.id', '=', 'user_families.user_id')
                    ->select('users.first_name AS first_name', 'users.middle_name AS middle_name', 'users.last_name AS last_name', 'users.extension_name AS extension_name', 'users.id_number AS id_number', 'user_details.gender AS gender', DB::raw("TIMESTAMPDIFF(YEAR, DATE(user_details.birth_date), current_date) AS age"), DB::raw('DATE_FORMAT(user_details.birth_date, "%b %d, %Y") as birth_date'), 'user_address.birth_place AS birth_place', 'user_address.address AS address', 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name', 'user_address.district_no AS district_no', 'users.email AS email', 'contact_details.phone AS phone', 'contact_details.mobile AS mobile', 'contact_details.messenger AS messenger', 'user_details.religion AS religion', 'user_details.blood_type AS blood_type', 'user_details.education AS education', 'user_details.employment_status AS employment_status', 'user_details.civil_status AS civil_status', 'user_details.member_status AS member_status', 'user_details.ethnic_origin AS ethnic_origin', 'user_details.language AS language', 'user_details.able_to_travel AS able_to_travel', 'user_details.active_in_politics AS active_in_politics', 'user_benefits.gsis AS gsis', 'user_benefits.sss AS sss', 'user_benefits.tin AS tin', 'user_benefits.philhealth AS philhealth', 'user_benefits.pension AS pension', 'user_benefits.association_id AS association_id', 'user_benefits.other_id AS other_id', 'contact_details.contact_person AS contact_person', 'contact_details.contact_person_number AS contact_person_number', 'user_specializations.area AS area', 'user_services.service AS service', 'user_companions.companion AS companion', 'user_housings.type AS type', 'user_involvements.activity AS activity', 'user_classification.classification AS classification', 'user_illness.sickness AS sickness', 'user_economic_statuses.source_of_income AS source_of_income', 'user_economic_statuses.assets AS assets', 'user_economic_statuses.income_range AS income_range', 'user_social_problems.problem AS social_problem', 'user_economic_problems.problem AS economic_problem', 'user_health_issues.problem AS health_issue', 'user_families.spouse_first_name AS spouse_first_name', 'user_families.spouse_middle_name AS spouse_middle_name', 'user_families.spouse_last_name AS spouse_last_name', 'user_families.spouse_extension_name AS spouse_extension_name', 'user_families.father_first_name AS father_first_name', 'user_families.father_middle_name AS father_middle_name', 'user_families.father_last_name AS father_last_name', 'user_families.father_extension_name AS father_extension_name', 'user_families.mother_first_name AS mother_first_name', 'user_families.mother_middle_name AS mother_middle_name', 'user_families.mother_last_name AS mother_last_name', 'user_families.mother_extension_name AS mother_extension_name', 'users.status AS status', 'users.created_at AS created_at', 'users.updated_at AS updated_at')
                    ->whereIn('user_address.municipality', $arr_mun)
                    ->whereNotNull('users.id_number')->whereNull('users.deleted_at')->get();
            }
        } else if($this->type == "municipality") {
            if($role == "admin" or $role == "site lead")
            {
            return DB::table('users')
                    ->leftJoin('user_address', 'users.id', '=', 'user_address.user_id')
                    ->leftJoin('user_details', 'users.id', '=', 'user_details.user_id')
                    ->leftJoin('contact_details', 'users.id', '=', 'contact_details.user_id')
                    ->leftJoin('user_benefits', 'users.id', '=', 'user_benefits.user_id')
                    ->leftJoin('user_specializations', 'users.id', '=', 'user_specializations.user_id')
                    ->leftJoin('user_services', 'users.id', '=', 'user_services.user_id')
                    ->leftJoin('user_companions', 'users.id', '=', 'user_companions.user_id')
                    ->leftJoin('user_housings', 'users.id', '=', 'user_housings.user_id')
                    ->leftJoin('user_involvements', 'users.id', '=', 'user_involvements.user_id')
                    ->leftJoin('user_classification', 'users.id', '=', 'user_classification.user_id')
                    ->leftJoin('user_illness', 'users.id', '=', 'user_illness.user_id')
                    ->leftJoin('user_economic_statuses', 'users.id', '=', 'user_economic_statuses.user_id')
                    ->leftJoin('user_social_problems', 'users.id', '=', 'user_social_problems.user_id')
                    ->leftJoin('user_economic_problems', 'users.id', '=', 'user_economic_problems.user_id')
                    ->leftJoin('user_health_issues', 'users.id', '=', 'user_health_issues.user_id')
                    ->leftJoin('user_families', 'users.id', '=', 'user_families.user_id')
                    ->select('users.first_name AS first_name', 'users.middle_name AS middle_name', 'users.last_name AS last_name', 'users.extension_name AS extension_name', 'users.id_number AS id_number', 'user_details.gender AS gender', DB::raw("TIMESTAMPDIFF(YEAR, DATE(user_details.birth_date), current_date) AS age"), DB::raw('DATE_FORMAT(user_details.birth_date, "%b %d, %Y") as birth_date'), 'user_address.birth_place AS birth_place', 'user_address.address AS address', 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name', 'user_address.district_no AS district_no', 'users.email AS email', 'contact_details.phone AS phone', 'contact_details.mobile AS mobile', 'contact_details.messenger AS messenger', 'user_details.religion AS religion', 'user_details.blood_type AS blood_type', 'user_details.education AS education', 'user_details.employment_status AS employment_status', 'user_details.civil_status AS civil_status', 'user_details.member_status AS member_status', 'user_details.ethnic_origin AS ethnic_origin', 'user_details.language AS language', 'user_details.able_to_travel AS able_to_travel', 'user_details.active_in_politics AS active_in_politics', 'user_benefits.gsis AS gsis', 'user_benefits.sss AS sss', 'user_benefits.tin AS tin', 'user_benefits.philhealth AS philhealth', 'user_benefits.pension AS pension', 'user_benefits.association_id AS association_id', 'user_benefits.other_id AS other_id', 'contact_details.contact_person AS contact_person', 'contact_details.contact_person_number AS contact_person_number', 'user_specializations.area AS area', 'user_services.service AS service', 'user_companions.companion AS companion', 'user_housings.type AS type', 'user_involvements.activity AS activity', 'user_classification.classification AS classification', 'user_illness.sickness AS sickness', 'user_economic_statuses.source_of_income AS source_of_income', 'user_economic_statuses.assets AS assets', 'user_economic_statuses.income_range AS income_range', 'user_social_problems.problem AS social_problem', 'user_economic_problems.problem AS economic_problem', 'user_health_issues.problem AS health_issue', 'user_families.spouse_first_name AS spouse_first_name', 'user_families.spouse_middle_name AS spouse_middle_name', 'user_families.spouse_last_name AS spouse_last_name', 'user_families.spouse_extension_name AS spouse_extension_name', 'user_families.father_first_name AS father_first_name', 'user_families.father_middle_name AS father_middle_name', 'user_families.father_last_name AS father_last_name', 'user_families.father_extension_name AS father_extension_name', 'user_families.mother_first_name AS mother_first_name', 'user_families.mother_middle_name AS mother_middle_name', 'user_families.mother_last_name AS mother_last_name', 'user_families.mother_extension_name AS mother_extension_name', 'users.status AS status', 'users.created_at AS created_at', 'users.updated_at AS updated_at')
                    ->where('user_address.municipality', $this->typeVal)->whereNotNull('users.id_number')->whereNull('users.deleted_at')->get();
            } else
            {
                return DB::table('users')
                    ->leftJoin('user_address', 'users.id', '=', 'user_address.user_id')
                    ->leftJoin('user_details', 'users.id', '=', 'user_details.user_id')
                    ->leftJoin('contact_details', 'users.id', '=', 'contact_details.user_id')
                    ->leftJoin('user_benefits', 'users.id', '=', 'user_benefits.user_id')
                    ->leftJoin('user_specializations', 'users.id', '=', 'user_specializations.user_id')
                    ->leftJoin('user_services', 'users.id', '=', 'user_services.user_id')
                    ->leftJoin('user_companions', 'users.id', '=', 'user_companions.user_id')
                    ->leftJoin('user_housings', 'users.id', '=', 'user_housings.user_id')
                    ->leftJoin('user_involvements', 'users.id', '=', 'user_involvements.user_id')
                    ->leftJoin('user_classification', 'users.id', '=', 'user_classification.user_id')
                    ->leftJoin('user_illness', 'users.id', '=', 'user_illness.user_id')
                    ->leftJoin('user_economic_statuses', 'users.id', '=', 'user_economic_statuses.user_id')
                    ->leftJoin('user_social_problems', 'users.id', '=', 'user_social_problems.user_id')
                    ->leftJoin('user_economic_problems', 'users.id', '=', 'user_economic_problems.user_id')
                    ->leftJoin('user_health_issues', 'users.id', '=', 'user_health_issues.user_id')
                    ->leftJoin('user_families', 'users.id', '=', 'user_families.user_id')
                    ->select('users.first_name AS first_name', 'users.middle_name AS middle_name', 'users.last_name AS last_name', 'users.extension_name AS extension_name', 'users.id_number AS id_number', 'user_details.gender AS gender', DB::raw("TIMESTAMPDIFF(YEAR, DATE(user_details.birth_date), current_date) AS age"), DB::raw('DATE_FORMAT(user_details.birth_date, "%b %d, %Y") as birth_date'), 'user_address.birth_place AS birth_place', 'user_address.address AS address', 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name', 'user_address.district_no AS district_no', 'users.email AS email', 'contact_details.phone AS phone', 'contact_details.mobile AS mobile', 'contact_details.messenger AS messenger', 'user_details.religion AS religion', 'user_details.blood_type AS blood_type', 'user_details.education AS education', 'user_details.employment_status AS employment_status', 'user_details.civil_status AS civil_status', 'user_details.member_status AS member_status', 'user_details.ethnic_origin AS ethnic_origin', 'user_details.language AS language', 'user_details.able_to_travel AS able_to_travel', 'user_details.active_in_politics AS active_in_politics', 'user_benefits.gsis AS gsis', 'user_benefits.sss AS sss', 'user_benefits.tin AS tin', 'user_benefits.philhealth AS philhealth', 'user_benefits.pension AS pension', 'user_benefits.association_id AS association_id', 'user_benefits.other_id AS other_id', 'contact_details.contact_person AS contact_person', 'contact_details.contact_person_number AS contact_person_number', 'user_specializations.area AS area', 'user_services.service AS service', 'user_companions.companion AS companion', 'user_housings.type AS type', 'user_involvements.activity AS activity', 'user_classification.classification AS classification', 'user_illness.sickness AS sickness', 'user_economic_statuses.source_of_income AS source_of_income', 'user_economic_statuses.assets AS assets', 'user_economic_statuses.income_range AS income_range', 'user_social_problems.problem AS social_problem', 'user_economic_problems.problem AS economic_problem', 'user_health_issues.problem AS health_issue', 'user_families.spouse_first_name AS spouse_first_name', 'user_families.spouse_middle_name AS spouse_middle_name', 'user_families.spouse_last_name AS spouse_last_name', 'user_families.spouse_extension_name AS spouse_extension_name', 'user_families.father_first_name AS father_first_name', 'user_families.father_middle_name AS father_middle_name', 'user_families.father_last_name AS father_last_name', 'user_families.father_extension_name AS father_extension_name', 'user_families.mother_first_name AS mother_first_name', 'user_families.mother_middle_name AS mother_middle_name', 'user_families.mother_last_name AS mother_last_name', 'user_families.mother_extension_name AS mother_extension_name', 'users.status AS status', 'users.created_at AS created_at', 'users.updated_at AS updated_at')
                    ->where('user_address.municipality', $this->typeVal)
                    ->whereIn('user_address.municipality', $arr_mun)
                    ->whereNotNull('users.id_number')->whereNull('users.deleted_at')->get();
            }
        } else {
            if($role == "admin" or $role == "site lead")
            {
            return DB::table('users')
                    ->leftJoin('user_address', 'users.id', '=', 'user_address.user_id')
                    ->leftJoin('user_details', 'users.id', '=', 'user_details.user_id')
                    ->leftJoin('contact_details', 'users.id', '=', 'contact_details.user_id')
                    ->leftJoin('user_benefits', 'users.id', '=', 'user_benefits.user_id')
                    ->leftJoin('user_specializations', 'users.id', '=', 'user_specializations.user_id')
                    ->leftJoin('user_services', 'users.id', '=', 'user_services.user_id')
                    ->leftJoin('user_companions', 'users.id', '=', 'user_companions.user_id')
                    ->leftJoin('user_housings', 'users.id', '=', 'user_housings.user_id')
                    ->leftJoin('user_involvements', 'users.id', '=', 'user_involvements.user_id')
                    ->leftJoin('user_classification', 'users.id', '=', 'user_classification.user_id')
                    ->leftJoin('user_illness', 'users.id', '=', 'user_illness.user_id')
                    ->leftJoin('user_economic_statuses', 'users.id', '=', 'user_economic_statuses.user_id')
                    ->leftJoin('user_social_problems', 'users.id', '=', 'user_social_problems.user_id')
                    ->leftJoin('user_economic_problems', 'users.id', '=', 'user_economic_problems.user_id')
                    ->leftJoin('user_health_issues', 'users.id', '=', 'user_health_issues.user_id')
                    ->leftJoin('user_families', 'users.id', '=', 'user_families.user_id')
                    ->select('users.first_name AS first_name', 'users.middle_name AS middle_name', 'users.last_name AS last_name', 'users.extension_name AS extension_name', 'users.id_number AS id_number', 'user_details.gender AS gender', DB::raw("TIMESTAMPDIFF(YEAR, DATE(user_details.birth_date), current_date) AS age"), DB::raw('DATE_FORMAT(user_details.birth_date, "%b %d, %Y") as birth_date'), 'user_address.birth_place AS birth_place', 'user_address.address AS address', 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name', 'user_address.district_no AS district_no', 'users.email AS email', 'contact_details.phone AS phone', 'contact_details.mobile AS mobile', 'contact_details.messenger AS messenger', 'user_details.religion AS religion', 'user_details.blood_type AS blood_type', 'user_details.education AS education', 'user_details.employment_status AS employment_status', 'user_details.civil_status AS civil_status', 'user_details.member_status AS member_status', 'user_details.ethnic_origin AS ethnic_origin', 'user_details.language AS language', 'user_details.able_to_travel AS able_to_travel', 'user_details.active_in_politics AS active_in_politics', 'user_benefits.gsis AS gsis', 'user_benefits.sss AS sss', 'user_benefits.tin AS tin', 'user_benefits.philhealth AS philhealth', 'user_benefits.pension AS pension', 'user_benefits.association_id AS association_id', 'user_benefits.other_id AS other_id', 'contact_details.contact_person AS contact_person', 'contact_details.contact_person_number AS contact_person_number', 'user_specializations.area AS area', 'user_services.service AS service', 'user_companions.companion AS companion', 'user_housings.type AS type', 'user_involvements.activity AS activity', 'user_classification.classification AS classification', 'user_illness.sickness AS sickness', 'user_economic_statuses.source_of_income AS source_of_income', 'user_economic_statuses.assets AS assets', 'user_economic_statuses.income_range AS income_range', 'user_social_problems.problem AS social_problem', 'user_economic_problems.problem AS economic_problem', 'user_health_issues.problem AS health_issue', 'user_families.spouse_first_name AS spouse_first_name', 'user_families.spouse_middle_name AS spouse_middle_name', 'user_families.spouse_last_name AS spouse_last_name', 'user_families.spouse_extension_name AS spouse_extension_name', 'user_families.father_first_name AS father_first_name', 'user_families.father_middle_name AS father_middle_name', 'user_families.father_last_name AS father_last_name', 'user_families.father_extension_name AS father_extension_name', 'user_families.mother_first_name AS mother_first_name', 'user_families.mother_middle_name AS mother_middle_name', 'user_families.mother_last_name AS mother_last_name', 'user_families.mother_extension_name AS mother_extension_name', 'users.status AS status', 'users.created_at AS created_at', 'users.updated_at AS updated_at')
                    ->where('user_address.municipality', $this->munVal)->where('user_address.barangay', $this->typeVal)->whereNotNull('users.id_number')->whereNull('users.deleted_at')->get();
            } else
            {
                return DB::table('users')
                    ->leftJoin('user_address', 'users.id', '=', 'user_address.user_id')
                    ->leftJoin('user_details', 'users.id', '=', 'user_details.user_id')
                    ->leftJoin('contact_details', 'users.id', '=', 'contact_details.user_id')
                    ->leftJoin('user_benefits', 'users.id', '=', 'user_benefits.user_id')
                    ->leftJoin('user_specializations', 'users.id', '=', 'user_specializations.user_id')
                    ->leftJoin('user_services', 'users.id', '=', 'user_services.user_id')
                    ->leftJoin('user_companions', 'users.id', '=', 'user_companions.user_id')
                    ->leftJoin('user_housings', 'users.id', '=', 'user_housings.user_id')
                    ->leftJoin('user_involvements', 'users.id', '=', 'user_involvements.user_id')
                    ->leftJoin('user_classification', 'users.id', '=', 'user_classification.user_id')
                    ->leftJoin('user_illness', 'users.id', '=', 'user_illness.user_id')
                    ->leftJoin('user_economic_statuses', 'users.id', '=', 'user_economic_statuses.user_id')
                    ->leftJoin('user_social_problems', 'users.id', '=', 'user_social_problems.user_id')
                    ->leftJoin('user_economic_problems', 'users.id', '=', 'user_economic_problems.user_id')
                    ->leftJoin('user_health_issues', 'users.id', '=', 'user_health_issues.user_id')
                    ->leftJoin('user_families', 'users.id', '=', 'user_families.user_id')
                    ->select('users.first_name AS first_name', 'users.middle_name AS middle_name', 'users.last_name AS last_name', 'users.extension_name AS extension_name', 'users.id_number AS id_number', 'user_details.gender AS gender', DB::raw("TIMESTAMPDIFF(YEAR, DATE(user_details.birth_date), current_date) AS age"), DB::raw('DATE_FORMAT(user_details.birth_date, "%b %d, %Y") as birth_date'), 'user_address.birth_place AS birth_place', 'user_address.address AS address', 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name', 'user_address.district_no AS district_no', 'users.email AS email', 'contact_details.phone AS phone', 'contact_details.mobile AS mobile', 'contact_details.messenger AS messenger', 'user_details.religion AS religion', 'user_details.blood_type AS blood_type', 'user_details.education AS education', 'user_details.employment_status AS employment_status', 'user_details.civil_status AS civil_status', 'user_details.member_status AS member_status', 'user_details.ethnic_origin AS ethnic_origin', 'user_details.language AS language', 'user_details.able_to_travel AS able_to_travel', 'user_details.active_in_politics AS active_in_politics', 'user_benefits.gsis AS gsis', 'user_benefits.sss AS sss', 'user_benefits.tin AS tin', 'user_benefits.philhealth AS philhealth', 'user_benefits.pension AS pension', 'user_benefits.association_id AS association_id', 'user_benefits.other_id AS other_id', 'contact_details.contact_person AS contact_person', 'contact_details.contact_person_number AS contact_person_number', 'user_specializations.area AS area', 'user_services.service AS service', 'user_companions.companion AS companion', 'user_housings.type AS type', 'user_involvements.activity AS activity', 'user_classification.classification AS classification', 'user_illness.sickness AS sickness', 'user_economic_statuses.source_of_income AS source_of_income', 'user_economic_statuses.assets AS assets', 'user_economic_statuses.income_range AS income_range', 'user_social_problems.problem AS social_problem', 'user_economic_problems.problem AS economic_problem', 'user_health_issues.problem AS health_issue', 'user_families.spouse_first_name AS spouse_first_name', 'user_families.spouse_middle_name AS spouse_middle_name', 'user_families.spouse_last_name AS spouse_last_name', 'user_families.spouse_extension_name AS spouse_extension_name', 'user_families.father_first_name AS father_first_name', 'user_families.father_middle_name AS father_middle_name', 'user_families.father_last_name AS father_last_name', 'user_families.father_extension_name AS father_extension_name', 'user_families.mother_first_name AS mother_first_name', 'user_families.mother_middle_name AS mother_middle_name', 'user_families.mother_last_name AS mother_last_name', 'user_families.mother_extension_name AS mother_extension_name', 'users.status AS status', 'users.created_at AS created_at', 'users.updated_at AS updated_at')
                    ->where('user_address.municipality', $this->munVal)
                    ->whereIn('user_address.municipality', $arr_mun)
                    ->where('user_address.barangay', $this->typeVal)->whereNotNull('users.id_number')->whereNull('users.deleted_at')->get();
            }
        }
    }
    public function headings(): array
    {
        return [
            'First Name',
            'Middle Name',
            'Last Name',
            'Extension Name',
            'ID Number',
            'Gender',
            'Age',
            'Date of Birth',
            'Place of Birth',
            'Address',
            'Province',
            'Municipality',
            'Barangay',
            'District No.',
            'Email',
            'Telephone',
            'Mobile',
            'Messenger',
            'Religion',
            'Blood Type',
            'Education',
            'Employment Status',
            'Civil Status',
            'Member Status',
            'Ethnic Origin',
            'Language',
            'Capability To Travel',
            'Active in Politics',
            'GSIS',
            'SSS',
            'TIN',
            'Philhealth',
            'Pension',
            "SC Association/Org ID No.",
            'Other ID',
            'Contact Person In Case of Emergency',
            "Contact Person's Number In Case of Emergency",
            'Area of Specialization',
            "Community Service/Others",
            "Living/residing with",
            'Housing',
            'Involvement in Community',
            'Classification',
            'Common Illness',
            'Source of Income',
            'Assets and Properties',
            'Monthly Income',
            'Social Problems',
            'Economic Problems',
            'Health Issues',
            "Spouse's First Name",
            "Spouse's Middle Name",
            "Spouse's Last Name",
            "Spouse's Extension Name",
            "Father's First Name",
            "Father's Middle Name",
            "Father's Last Name",
            "Father's Extension Name",
            "Mother's First Name",
            "Mother's Middle Name",
            "Mother's Last Name",
            "Mother's Extension Name",
            'Status',
            'Date Created',
            'Date Updated',
        ];
    }
}

