<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\UserChildren;
use App\Models\UserMaintenance;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use Image;
use chillerlan\QRCode\{QRCode, QROptions};

class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    private function ordinalNumber($num)
    {
        switch($num)
        {
            case 1:  return $num.'st';
            case 2:  return $num.'nd';
            case 3:  return $num.'rd';
            default: return $num.'th';
        }
    }
    public function saveMemberTransaction(Request $request)
    {
        try
        {
            $id = $request->id;
            $admin = Auth::user();
            $now = Carbon::now()->format('Y-m-d H:i:s');
            $addr = DB::table('user_address')->where('user_id', $id)->first();
            $office = 'OSCA '. ucfirst($addr->municipality_name);
            $trans = DB::table('member_card_transactions')->where('user_id', $id)->count();
            $remark = $this->ordinalNumber($trans+1)." Copy";
            $data = ['released_by' => $admin->id, 'name' => 'Senior ID Card', 'office_released' => $office, 'release_date' => $now, 'user_id' => $id, 'remarks' => $remark];
            DB::beginTransaction();
                $ins = DB::table('member_card_transactions')->insert($data);
            DB::commit();
            $msg = 'success';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json(['transaction_status' => $msg]);
    }
    public function uploadImage(Request $request)
    {
        if($request->page == 'vaccine')
        {
            $filename = '';
            try
            {
                $id = $request->id;
                DB::beginTransaction();
                    $user = DB::table('user_vaccination')->where('user_id', $id)->first();
                    $usr = DB::table('users')->select('id_number')->where('id', $id)->first();
                DB::commit();
                if(!empty($user->vaccine_card))
                {
                    $card = public_path('images/vaccine_cards/'.$user->vaccine_card);
                    if(file_exists($card)) {
                         unlink($card);
                    }
                }
                $now = Carbon::now()->format('Y-m-d-H-i-s');
                $filename = $usr->id_number.'-vaccine-card-'.$now.'.png';
                $img = Image::make($request->data)->resize(250, 200);
                $fullPath = public_path('images/vaccine_cards/'.$filename);
                $img->save($fullPath);
                if(!empty($user->vaccine_card))
                {
                    $upd = DB::table('user_vaccination')->where('user_id', $id)->update(['vaccine_card' => $filename]);
                } else {
                    $ins = DB::table('user_vaccination')->insert(['vaccine_card' => $filename, 'user_id' => $id]);
                }
                $msg = 'success';
            } catch (Exception $e)
            {
                DB::rollBack();
                $msg = $e->getMessage();
            }
            return response()->json(['upload_status' => $msg, 'filename' => $filename]);
        }
    }
    public function getDosageLevel(Request $request)
    {
        try {
            $level = DB::table('vaccination_dosage')->select('level')->get();
            $msg = 'success';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'level_status' => $msg,
            'level' => $level,
        ]);
    }
    public function exportData(Request $request)
    {
        $user = Auth::user();
	$mun = $request->mun;
	$mtype = $request->mtype;
        if(!empty($request->munQuery))
        {
            $mtype = "municipality";
            $mncp = DB::table('municipalities')->select('municipality_code_number')->where('municipality_name', $request->munQuery)->first();
            $mun = $mncp->municipality_code_number;
        }
        $muni = DB::table('municipalities')->select('municipality_name')->where('municipality_code_number', $mun)->first();
        $brgy = DB::table('barangays')->select('barangay_name')->where('id', $request->bar)->first();
        if($mtype == 'municipality') {
            $filename = 'users_'.$muni->municipality_name.'.xlsx';
            Excel::store(new UsersExport('municipality', '', $mun, $user), $filename, 'local');
            return response()->json($filename, 200);
        } else if($mtype == "barangay") {
            $filename = 'users_'.$muni->municipality_name.'_'.$brgy->barangay_name.'.xlsx';
            Excel::store(new UsersExport('barangay', $mun, $request->bar, $user), $filename, 'local');
            return response()->json($filename, 200);
        } else {
            Excel::store(new UsersExport('all', '', '', $user), 'members.xlsx', 'local');
            return response()->json('members.xlsx', 200);
        }
    }

    public function getCanvasData(Request $request)
    {
        $photo = $signature = $img = $img2 = $filename = "";
        try
        {
            $id = $request->id;
            DB::beginTransaction();
                $usr = DB::table('users')->select('id_number', 'first_name', 'middle_name', 'last_name', 'created_at')->where('id', $id)->first();
                $user = DB::table('user_qrcodes')->where('user_id', $id)->first();
                $usrAddr = DB::table('user_address')->select('address', 'barangay_name', 'municipality_name', 'province_name')->where('user_id', $id)->first();
                $usrDetail = DB::table('user_details')->select('birth_date', 'gender')->where('user_id', $id)->first();
                $usrPhoto = DB::table('user_photos')->select('filename')->where('user_id', $id)->first();
                $usrSignature = DB::table('user_signatures')->select('filename')->where('user_id', $id)->first();
            DB::commit();
            $id_no = $usr->id_number;
            $qrImg = $id_no.'.png';
            $qrImg2 = $id_no.'_back.png';
            if(!empty($user->qrcode))
            {
                $qrpic = public_path('images/qrcodes/'.$user->qrcode);
                if(file_exists($qrpic))
                {
                     unlink($qrpic);
                }
            }
            if(!empty($user->qrcode_back))
            {
                $qrpicback = public_path('images/qrcodes/'.$user->qrcode_back);
                if(file_exists($qrpicback))
                {
                     unlink($qrpicback);
                }
            }
            if(!empty($user->qrcode))
            {
                $upd = DB::table('user_qrcodes')->where('user_id', $id)->update(['qrcode' => $qrImg, 'qrcode_back' => $qrImg2]);
            } else {
                $ins = DB::table('user_qrcodes')->insert(['qrcode' => $qrImg, 'qrcode_back' => $qrImg2, 'user_id' => $id]);
            }

            $name = ucwords($usr->first_name." ". $usr->middle_name ." ". $usr->last_name);
            $bday = new Carbon($usrDetail->birth_date);
            $bday2 = $bday->format('m/d/Y');
            $issued = new Carbon($usr->created_at);
            $issued2 = $issued->format('m/d/Y');
            $address = $usrAddr->address;
            if(empty($address)) {
                $address = "Brgy. ".$usrAddr->barangay_name.", ".$usrAddr->municipality_name.", ".$usrAddr->province_name;
            }
            $text = $id_no . '|' . $name. '|' . $address. '|'. $bday2. '|' . $issued2. '|'. $usrDetail->gender;
            $img = (new QRCode)->render($text);
            $text2 = $request->host.'/admin/members/?code_number='.$id_no;
            $img2 = (new QRCode)->render($text2);

            if(!empty($usrPhoto->filename))
            {
                $photo = asset('images/profiles/'.$usrPhoto->filename);
            }
            if(!empty($usrSignature->filename))
            {
                $signature = asset('images/signatures/'.$usrSignature->filename);
            }
            $filename = $id_no.".jpg";
            $msg = 'successful';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'print_status' => $msg,
            'name' => $name,
            'id' => $id_no,
            'photo' => $photo,
            'signature' => $signature,
            'address' => ucwords($address),
            'img' => $img,
            'img2' => $img2,
            'filename' => $filename,
            'gender' => $usrDetail->gender,
            'bday' => $bday->format('m-d-Y'),
            'issued' => $issued->format('m-d-Y')
        ]);
    }

    public function vaccineIdCamera(Request $request)
    {
        try
        {
            $id = $request->id;
            DB::beginTransaction();
                $user = DB::table('user_vaccination')->where('user_id', $id)->first();
                $usr = DB::table('users')->select('id_number')->where('id', $id)->first();
            DB::commit();
            if(!empty($user->vaccine_card))
            {
                $card = public_path('images/vaccine_cards/'.$user->vaccine_card);
                if(file_exists($card)) {
                     unlink($card);
                }
            }
            $now = Carbon::now()->format('Y-m-d-H-i-s');
            $filename = $usr->id_number.'-vaccine-card-'.$now.'.png';
            $img = Image::make($request->info)->resize(250, 200);
            $fullPath = public_path('images/vaccine_cards/'.$filename);
            $img->save($fullPath);
            if(!empty($user->vaccine_card))
            {
                $upd = DB::table('user_vaccination')->where('user_id', $id)->update(['vaccine_card' => $filename]);
            } else {
                $ins = DB::table('user_vaccination')->insert(['vaccine_card' => $filename, 'user_id' => $id]);
            }
            $msg = 'successful';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'camera_status' => $msg
        ]);
    }

    public function userCamera(Request $request)
    {
        try
        {
            $id = $request->id;
            DB::beginTransaction();
                $user = DB::table('user_photos')->where('user_id', $id)->first();
                $usr = DB::table('users')->select('id_number')->where('id', $id)->first();
            DB::commit();
            if(!empty($user->filename))
            {
                $sign = public_path('images/profiles/'.$user->filename);
                if(file_exists($sign)) {
                     unlink($sign);
                }
            }
            $cntr = 1;
            if(!empty($user->counter)) {
                $cntr = (int)$user->counter + 1;
            }
            $now = Carbon::now()->format('Y-m-d-H-i-s');
            $filename = $usr->id_number.'-profile-'.$now.'.png';
            $img = Image::make($request->info)->fit(105);
            $fullPath = public_path('images/profiles/'.$filename);
            $img->save($fullPath);
            if(!empty($user->filename))
            {
                $upd = DB::table('user_photos')->where('user_id', $id)->update(['filename' => $filename, 'counter' => $cntr]);
            } else {
                $ins = DB::table('user_photos')->insert(['filename' => $filename, 'counter' => $cntr, 'user_id' => $id]);
            }
            $msg = 'successful';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'camera_status' => $msg
        ]);
    }

    public function userSignature(Request $request)
    {
        try {
            DB::beginTransaction();
                $user = DB::table('user_signatures')->where('user_id', $request->id)->first();
                $usr = DB::table('users')->select('id_number')->where('id', $request->id)->first();
            DB::commit();
            if(!empty($user->filename))
            {
                $sign = public_path('images/signatures/'.$user->filename);
                if(file_exists($sign)) {
                     unlink($sign);
                }
            }
            $cntr = 1;
            if(!empty($user->counter)) {
                $cntr = (int)$user->counter + 1;
            }
            $now = Carbon::now()->format('Y-m-d-H-i-s');
            $filename = $usr->id_number.'-signature-'.$now.'.png';
            $img = Image::make($request->info)->resize(200, 40);
            $fullPath = public_path('images/signatures/'.$filename);
            $img->save($fullPath);
            if(!empty($user->filename))
            {
                $upd = DB::table('user_signatures')->where('user_id', $request->id)->update(['filename' => $filename, 'counter' => $cntr]);
            } else {
                $ins = DB::table('user_signatures')->insert(['filename' => $filename, 'counter' => $cntr, 'user_id' => $request->id]);
            }
            $msg = 'successful';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'signature_status' => $msg
        ]);
    }

    public function saveVaccinationInfo(Request $request)
    {
        try {
            $id = $request->id;
            $data = $request->data;
            $filename = '';
            $user_vac = DB::table('user_vaccination')->where('user_id', $id)->first();
            if(!empty($data))
            {
                $user = DB::table('users')->select('id_number')->where('id', $id)->first();
                $now = Carbon::now()->format('Y-m-d-H-i-s');
                $filename = $user->id_number.'-vaccine-card-'.$now.'.png';
            }
            if(!empty($user_vac))
            {
                $info = ['dose' => $request->dose, 'vaccine' => $request->vaccine, 'vaccine_card' => $filename, 'vaccination_date' => $request->formatted_vdate];
                $card = public_path('images/vaccine_cards/'.$user_vac->vaccine_card);
                if(file_exists($card))
                {
                    unlink($card);
                }
            } else
            {
                $info = ['dose' => $request->dose, 'vaccine' => $request->vaccine, 'vaccine_card' => $filename, 'vaccination_date' => $request->formatted_vdate, 'user_id' => $id];
            }
            DB::beginTransaction();
                if(!empty($user_vac))
                {
                    $upd = DB::table('user_vaccination')->where('user_id', $id)->update($info);
                } else
                {
                    $ins = DB::table('user_vaccination')->insert($info);
                }
            DB::commit();
            if(!empty($data))
            {
                $img = Image::make($data)->resize(250, 180);
                $fullPath = public_path('images/vaccine_cards/'.$filename);
                $img->save($fullPath);
            }
            $msg = 'success';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'vaccine_status' => $msg
        ]);
    }

    public function updateMember(Request $request)
    {
        try {
            $values = [
                'users.first_name' => $request->first_name,
                'users.last_name' => $request->last_name,
                'users.middle_name' => $request->middle_name,
                'users.email' => $request->email,
                'users.extension_name' => $request->extension_name,
                'contact_details.phone' => $request->phone,
                'contact_details.mobile' => $request->mobile,
                'contact_details.contact_person' => $request->contact_person,
                'contact_details.contact_person_number' => $request->contact_person_number,
                'contact_details.messenger' => $request->messenger,
                'user_address.birth_place' => $request->birth_place,
                'user_address.address' => $request->address,
                'user_address.house_no' => $request->house_no,
                'user_address.street' => $request->street,
                'user_details.gender' => $request->gender,
                'user_details.civil_status' => $request->civil_status,
                'user_details.blood_type' => $request->blood_type,
                'user_details.religion' => $request->religion,
                'user_details.education' => $request->education,
                'user_details.employment_status' => $request->employment_status,
                'user_details.member_status' => $request->member_status,
                'user_details.birth_date' => $request->formatted_bday,
                'user_details.language' => $request->language,
                'user_details.ethnic_origin' => $request->ethnic_origin,
                'user_details.able_to_travel' => $request->able_to_travel,
                'user_details.active_in_politics' => $request->active_in_politics,
                'user_benefits.gsis' => $request->gsis,
                'user_benefits.sss' => $request->sss,
                'user_benefits.tin' => $request->tin,
                'user_benefits.philhealth' => $request->philhealth,
                'user_benefits.pension' => $request->pension,
                'user_benefits.association_id' => $request->association_id,
                'user_benefits.other_id' => $request->other_id,
                'user_classification.classification' => $request->member_type,
                'user_illness.sickness' => $request->selected_illness,
                'user_specializations.area' => $request->specialization_area,
                'user_services.service' => $request->user_services,
                'user_companions.companion' => $request->user_companion,
                'user_housings.type' => $request->housing,
                'user_involvements.activity' => $request->user_involvement,
                'user_families.spouse_first_name' => $request->spouse_first_name,
                'user_families.spouse_middle_name' => $request->spouse_middle_name,
                'user_families.spouse_last_name' => $request->spouse_last_name,
                'user_families.spouse_extension_name' => $request->spouse_extension_name,
                'user_families.father_first_name' => $request->father_first_name,
                'user_families.father_middle_name' => $request->father_middle_name,
                'user_families.father_last_name' => $request->father_last_name,
                'user_families.father_extension_name' => $request->father_extension_name,
                'user_families.mother_first_name' => $request->mother_first_name,
                'user_families.mother_middle_name' => $request->mother_middle_name,
                'user_families.mother_last_name' => $request->mother_last_name,
                'user_families.mother_extension_name' => $request->mother_extension_name,
                'user_social_problems.problem' => $request->user_social_problem,
                'user_economic_problems.problem' => $request->user_economic_problem,
                'user_health_issues.problem' => $request->user_health_issue,
                'user_economic_statuses.source_of_income' => $request->user_income_source,
                'user_economic_statuses.assets' => $request->user_assets,
                'user_economic_statuses.income_range' => $request->income_range,
            ];
            DB::beginTransaction();
            $updated = DB::table('users')
                    ->join('user_address', 'users.id', '=', 'user_address.user_id')
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->join('user_benefits', 'users.id', '=', 'user_benefits.user_id')
                    ->join('user_classification', 'users.id', '=', 'user_classification.user_id')
                    ->join('user_illness', 'users.id', '=', 'user_illness.user_id')
                    ->join('contact_details', 'users.id', '=', 'contact_details.user_id')
                    ->join('user_specializations', 'users.id', '=', 'user_specializations.user_id')
                    ->join('user_services', 'users.id', '=', 'user_services.user_id')
                    ->join('user_companions', 'users.id', '=', 'user_companions.user_id')
                    ->join('user_housings', 'users.id', '=', 'user_housings.user_id')
                    ->join('user_involvements', 'users.id', '=', 'user_involvements.user_id')
                    ->join('user_families', 'users.id', '=', 'user_families.user_id')
                    ->join('user_social_problems', 'users.id', '=', 'user_social_problems.user_id')
                    ->join('user_economic_problems', 'users.id', '=', 'user_economic_problems.user_id')
                    ->join('user_health_issues', 'users.id', '=', 'user_health_issues.user_id')
                    ->join('user_economic_statuses', 'users.id', '=', 'user_economic_statuses.user_id')
                    ->where('users.id', $request->id)->update($values);
            DB::commit();
            if(!empty($request->offspring))
            {
                $delChildren = DB::table('user_children')->where('user_id', $request->id)->delete();
                foreach($request->offspring as $child)
                {
                    if(!empty($child['full_name']))
                    {
                        $dep = "";
                        if(isset($child['dependency'])) {
                            $dep = $child['dependency'];
                        }
                        $md = UserChildren::create([
                            'user_id' => $request->id,
                            'full_name' => $child['full_name'],
                            'occupation' => $child['occupation'],
                            'income' => $child['income'],
                            'age' => $child['age'],
                            'dependency' => $dep,
                        ]);
                    }
                }
            }
            if(!empty($request->medicines))
            {
                $delMeds = DB::table('user_maintenances')->where('user_id', $request->id)->delete();
                foreach($request->medicines as $med)
                {
                    if(!empty($med['medicine']))
                    {
                        $dose = $qty = "";
                        if(isset($med['dosage'])) {
                            $dose = $med['dosage'];
                        }
                        if(isset($med['quantity'])) {
                            $qty = $med['quantity'];
                        }
                        $md = UserMaintenance::create([
                            'user_id' => $request->id,
                            'medicine' => $med['medicine'],
                            'dosage' => $dose,
                            'quantity' => $qty,
                        ]);
                    }
                }
            }
            $msg = 'successful';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'update_status' => $msg
        ]);
    }
    public function deleteMember(Request $request)
    {
        try {
            DB::beginTransaction();
                $deleted = User::where('id', $request->id)->delete();
            DB::commit();
            $admin = Auth::user();
            $affected = DB::table('users')->where('id', $request->id)->update(['deleted_by' => $admin->id]);
            $msg = 'success';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'delete_status' => $msg,
        ]);
    }
    public function getMember(Request $request)
    {
        $msg = "";
        $photo = null;
        $transactions = $children = $maintenances = [];
        try
        {
            DB::beginTransaction();
            if($request->type == 'edit')
            {
                $user = DB::table('users')
                    ->join('user_address', 'users.id', '=', 'user_address.user_id')
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->join('user_benefits', 'users.id', '=', 'user_benefits.user_id')
                    ->join('user_classification', 'users.id', '=', 'user_classification.user_id')
                    ->join('user_illness', 'users.id', '=', 'user_illness.user_id')
                    ->join('contact_details', 'users.id', '=', 'contact_details.user_id')
                    ->leftJoin('user_economic_statuses', 'users.id', '=', 'user_economic_statuses.user_id')
                    ->leftJoin('user_companions', 'users.id', '=', 'user_companions.user_id')
                    ->leftJoin('user_economic_problems', 'users.id', '=', 'user_economic_problems.user_id')
                    ->leftJoin('user_families', 'users.id', '=', 'user_families.user_id')
                    ->leftJoin('user_health_issues', 'users.id', '=', 'user_health_issues.user_id')
                    ->leftJoin('user_housings', 'users.id', '=', 'user_housings.user_id')
                    ->leftJoin('user_involvements', 'users.id', '=', 'user_involvements.user_id')
                    ->leftJoin('user_services', 'users.id', '=', 'user_services.user_id')
                    ->leftJoin('user_social_problems', 'users.id', '=', 'user_social_problems.user_id')
                    ->leftJoin('user_specializations', 'users.id', '=', 'user_specializations.user_id')
                    ->select('users.id AS id', 'users.id_number AS id_number', 'users.last_name AS last_name', 'users.first_name AS first_name', 'users.middle_name AS middle_name', 'users.email AS email', 'users.extension_name AS extension_name', DB::raw('DATE_FORMAT(user_details.birth_date, "%m-%d-%Y") as birth_date'), 'contact_details.phone AS phone', 'contact_details.mobile AS mobile', 'contact_details.contact_person AS contact_person', 'contact_details.contact_person_number AS contact_person_number', 'contact_details.messenger AS messenger', 'user_address.birth_place AS birth_place', 'user_address.address AS address', 'user_address.house_no AS house_no', 'user_address.street AS street', 'user_details.gender AS gender', 'user_details.civil_status AS civil_status', 'user_details.blood_type AS blood_type', 'user_details.religion AS religion', 'user_details.education AS education', 'user_details.employment_status AS employment_status', 'user_details.member_status AS member_status', 'user_details.ethnic_origin AS ethnic_origin', 'user_details.language AS language', 'user_details.able_to_travel AS able_to_travel', 'user_details.active_in_politics AS active_in_politics', 'user_benefits.gsis AS gsis', 'user_benefits.sss AS sss', 'user_benefits.tin AS tin', 'user_benefits.philhealth AS philhealth', 'user_benefits.pension AS pension', 'user_benefits.association_id AS association_id', 'user_benefits.other_id AS other_id', 'user_classification.classification AS classification', 'user_illness.sickness AS sickness', 'user_economic_statuses.source_of_income AS source_of_income', 'user_economic_statuses.assets AS assets', 'user_economic_statuses.income_range AS income_range', 'user_companions.companion AS companion', 'user_economic_problems.problem AS economic_problem', 'user_families.spouse_first_name AS spouse_first_name', 'user_families.spouse_middle_name AS spouse_middle_name', 'user_families.spouse_last_name AS spouse_last_name', 'user_families.spouse_extension_name AS spouse_extension_name', 'user_families.father_first_name AS father_first_name', 'user_families.father_middle_name AS father_middle_name', 'user_families.father_last_name AS father_last_name', 'user_families.father_extension_name AS father_extension_name', 'user_families.mother_first_name AS mother_first_name', 'user_families.mother_middle_name AS mother_middle_name', 'user_families.mother_last_name AS mother_last_name', 'user_families.mother_extension_name AS mother_extension_name', 'user_health_issues.problem AS health_issue', 'user_housings.type AS type', 'user_involvements.activity AS activity', 'user_services.service AS service', 'user_social_problems.problem AS social_problem', 'user_specializations.area AS area')
                    ->where('users.id', $request->id)->first();
                $children = DB::table('user_children')->where('user_id', $request->id)->get();
                $maintenances = DB::table('user_maintenances')->where('user_id', $request->id)->get();
            } else if($request->type == 'info')
            {
                $user = DB::table('users')
                    ->join('user_address', 'users.id', '=', 'user_address.user_id')
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->join('user_benefits', 'users.id', '=', 'user_benefits.user_id')
                    ->join('user_classification', 'users.id', '=', 'user_classification.user_id')
                    ->join('user_illness', 'users.id', '=', 'user_illness.user_id')
                    ->join('contact_details', 'users.id', '=', 'contact_details.user_id')
                    ->leftJoin('user_photos', 'users.id', '=', 'user_photos.user_id')
                    ->leftJoin('user_vaccination', 'users.id', '=', 'user_vaccination.user_id')
                    ->leftJoin('user_economic_statuses', 'users.id', '=', 'user_economic_statuses.user_id')
                    ->leftJoin('user_companions', 'users.id', '=', 'user_companions.user_id')
                    ->leftJoin('user_economic_problems', 'users.id', '=', 'user_economic_problems.user_id')
                    ->leftJoin('user_families', 'users.id', '=', 'user_families.user_id')
                    ->leftJoin('user_health_issues', 'users.id', '=', 'user_health_issues.user_id')
                    ->leftJoin('user_housings', 'users.id', '=', 'user_housings.user_id')
                    ->leftJoin('user_involvements', 'users.id', '=', 'user_involvements.user_id')
                    ->leftJoin('user_services', 'users.id', '=', 'user_services.user_id')
                    ->leftJoin('user_social_problems', 'users.id', '=', 'user_social_problems.user_id')
                    ->leftJoin('user_specializations', 'users.id', '=', 'user_specializations.user_id')
                    ->select('users.id AS id', 'users.id_number AS id_number', 'users.last_name AS last_name', 'users.first_name AS first_name', 'users.middle_name AS middle_name', 'users.email AS email', 'users.extension_name AS extension_name', DB::raw('DATE_FORMAT(user_details.birth_date, "%M %d, %Y") as birth_date'), 'contact_details.phone AS phone', 'contact_details.mobile AS mobile', 'contact_details.contact_person AS contact_person', 'contact_details.contact_person_number AS contact_person_number', 'contact_details.messenger AS messenger', 'user_address.birth_place AS birth_place', 'user_address.address AS address', 'user_details.gender AS gender', 'user_details.civil_status AS civil_status', 'user_details.blood_type AS blood_type', 'user_details.religion AS religion', 'user_details.education AS education', 'user_details.employment_status AS employment_status', 'user_details.member_status AS member_status', 'user_details.ethnic_origin AS ethnic_origin', 'user_details.language AS language', 'user_details.able_to_travel AS able_to_travel', 'user_details.active_in_politics AS active_in_politics', 'user_benefits.gsis AS gsis', 'user_benefits.sss AS sss', 'user_benefits.tin AS tin', 'user_benefits.philhealth AS philhealth', 'user_benefits.pension AS pension', 'user_benefits.association_id AS association_id', 'user_benefits.other_id AS other_id', 'user_classification.classification AS classification', 'user_illness.sickness AS sickness', 'user_vaccination.dose AS dose', 'user_vaccination.vaccine AS vaccine', DB::raw('DATE_FORMAT(user_vaccination.vaccination_date, "%M %d, %Y") as vaccination_date'), 'user_photos.filename AS photo', 'user_economic_statuses.source_of_income AS source_of_income', 'user_economic_statuses.assets AS assets', 'user_economic_statuses.income_range AS income_range', 'user_companions.companion AS companion', 'user_economic_problems.problem AS economic_problem', 'user_families.spouse_first_name AS spouse_first_name', 'user_families.spouse_middle_name AS spouse_middle_name', 'user_families.spouse_last_name AS spouse_last_name', 'user_families.spouse_extension_name AS spouse_extension_name', 'user_families.father_first_name AS father_first_name', 'user_families.father_middle_name AS father_middle_name', 'user_families.father_last_name AS father_last_name', 'user_families.father_extension_name AS father_extension_name', 'user_families.mother_first_name AS mother_first_name', 'user_families.mother_middle_name AS mother_middle_name', 'user_families.mother_last_name AS mother_last_name', 'user_families.mother_extension_name AS mother_extension_name', 'user_health_issues.problem AS health_issue', 'user_housings.type AS type', 'user_involvements.activity AS activity', 'user_services.service AS service', 'user_social_problems.problem AS social_problem', 'user_specializations.area AS area')
                    ->selectRaw("TIMESTAMPDIFF(YEAR, DATE(user_details.birth_date), current_date) AS age")
                    ->where('users.id_number', $request->id)->first();
                    $children = DB::table('user_children')->where('user_id', $user->id)->get();
                    $maintenances = DB::table('user_maintenances')->where('user_id', $user->id)->get();
                    $transactions = DB::table('member_card_transactions')
                                ->select('name', 'office_released', 'remarks', DB::raw('DATE_FORMAT(release_date, "%M %d, %Y") as release_date'))
                                ->where('user_id', $user->id)->get();
            }
            DB::commit();
            if(!empty($user->photo))
            {
                $photo = asset('images/profiles/'.$user->photo);
            }
            $msg = 'success';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'user_status' => $msg,
            'user' => $user,
            'children' => $children,
            'maintenances' => $maintenances,
            'photo' => $photo,
            'transactions' => $transactions
        ]);
    }
    public function getSpecificMunicipalities($mun)
    {
        $mun = DB::table('municipalities')->select('municipality_code_number', 'municipality_name')->orderBy('municipality_name')->get();

        return response()->json([
            'status' => 'success',
            'mun' => $mun
        ]);
    }

    private function getMunicipalityArray($mun)
    {
        $arr_mun = [];
        if($mun != "none") {
            if(Str::contains($mun, ',')) {
                $arr_mun = explode(',', $mun);
            } else {
                $arr_mun[] = $mun;
            }
        }
        return $arr_mun;
    }

    public function getMembers(Request $request)
    {
        $user = Auth::user();
        $municipality = empty($user->address->municipality) ? 'none' : $user->address->municipality;
        $role = $user->roles->pluck('name')[0];
        $municipalities = DB::table('municipalities')->select('municipality_code_number', 'municipality_name')->orderBy('municipality_name')->get();
        $members = [];
        if($request->mtype == 'all') {
            if($role == "admin" || $role == "site lead") {
                $members = DB::table('users')
                    ->join('user_address', 'users.id', '=', 'user_address.user_id')
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->select('users.id AS id', 'users.id_number AS id_number', 'users.last_name AS last_name', 'users.first_name AS first_name', 'user_details.birth_date as birth_date', 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name')
                    ->selectRaw("TIMESTAMPDIFF(YEAR, DATE(user_details.birth_date), current_date) AS age")
                    ->whereNotNull('users.id_number')->whereNull('users.deleted_at')->orderByDesc('users.created_at')->get();
            } else if($role == "team lead" or $role == "encoder") {
                $arr_mun = $this->getMunicipalityArray($municipality);
                $municipalities = DB::table('municipalities')->select('municipality_code_number', 'municipality_name')->whereIn('municipality_code_number', $arr_mun)->orderBy('municipality_name')->get();
                $members = DB::table('users')
                    ->join('user_address', 'users.id', '=', 'user_address.user_id')
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->select('users.id AS id', 'users.id_number AS id_number', 'users.last_name AS last_name', 'users.first_name AS first_name', 'user_details.birth_date as birth_date', 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name')
                    ->selectRaw("TIMESTAMPDIFF(YEAR, DATE(user_details.birth_date), current_date) AS age")
                    ->whereIn('user_address.municipality', $arr_mun)->whereNotNull('users.id_number')->whereNull('users.deleted_at')->orderByDesc('users.created_at')->get();
            }
        } else if($request->mtype == 'municipality') {
            if($request->stype != 'str') {
                $members = DB::table('users')
                ->join('user_address', 'users.id', '=', 'user_address.user_id')
                ->join('user_details', 'users.id', '=', 'user_details.user_id')
                ->select('users.id AS id', 'users.id_number AS id_number', 'users.last_name AS last_name', 'users.first_name AS first_name', 'user_details.birth_date as birth_date', 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name')
                ->selectRaw("TIMESTAMPDIFF(YEAR, DATE(user_details.birth_date), current_date) AS age")
                ->where('user_address.municipality', $request->mun)->whereNotNull('users.id_number')->whereNull('users.deleted_at')->orderByDesc('users.created_at')->get();
            } else {
                $mncp = DB::table('municipalities')->select('municipality_code_number')->where('municipality_name', $request->mun)->first();
                $members = DB::table('users')
                ->join('user_address', 'users.id', '=', 'user_address.user_id')
                ->join('user_details', 'users.id', '=', 'user_details.user_id')
                ->select('users.id AS id', 'users.id_number AS id_number', 'users.last_name AS last_name', 'users.first_name AS first_name', 'user_details.birth_date as birth_date', 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name')
                ->selectRaw("TIMESTAMPDIFF(YEAR, DATE(user_details.birth_date), current_date) AS age")
                ->where('user_address.municipality', $mncp->municipality_code_number)->whereNotNull('users.id_number')->whereNull('users.deleted_at')->orderByDesc('users.created_at')->get();
            }
            if($role == "team lead" or $role == "encoder") {
                $arr_mun = $this->getMunicipalityArray($municipality);
                $municipalities = DB::table('municipalities')->select('municipality_code_number', 'municipality_name')->whereIn('municipality_code_number', $arr_mun)->orderBy('municipality_name')->get();
            }
        } else if($request->mtype == 'barangay') {
            $members = DB::table('users')
                ->join('user_address', 'users.id', '=', 'user_address.user_id')
                ->join('user_details', 'users.id', '=', 'user_details.user_id')
                ->select('users.id AS id', 'users.id_number AS id_number', 'users.last_name AS last_name', 'users.first_name AS first_name', 'user_details.birth_date as birth_date', 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name')
                ->selectRaw("TIMESTAMPDIFF(YEAR, DATE(user_details.birth_date), current_date) AS age")
                ->where('user_address.municipality', $request->mun)->where('user_address.barangay', $request->bar)->whereNotNull('users.id_number')->whereNull('users.deleted_at')->orderByDesc('users.created_at')->get();
            if($role == "team lead" or $role == "encoder") {
                $arr_mun = $this->getMunicipalityArray($municipality);
                $municipalities = DB::table('municipalities')->select('municipality_code_number', 'municipality_name')->whereIn('municipality_code_number', $arr_mun)->orderBy('municipality_name')->get();
            }
        }
        return response()->json([
            'member_status' => 'success',
            'members' => $members,
            'municipality' => $municipality,
            'selectedMunicipalities' => $municipalities
        ]);
    }
}

