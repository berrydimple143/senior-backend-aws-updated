<?php

namespace App\Http\Controllers;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RegisterAdminRequest;
use App\Models\UserMaintenance;
use App\Models\UserEconomicStatus;
use App\Models\UserHealthIssue;
use App\Models\UserEconomicProblem;
use App\Models\UserSocialProblem;
use App\Models\UserChildren;
use App\Models\UserFamily;
use App\Models\UserHousing;
use App\Models\UserInvolvement;
use App\Models\UserSpecialization;
use App\Models\UserCompanion;
use App\Models\UserService;
use App\Models\Address;
use App\Models\Benefit;
use App\Models\Classification;
use App\Models\Contact;
use App\Models\Detail;
use App\Models\Sickness;
use App\Models\User;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Image;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','registerAdmin']]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials, ['exp' => Carbon::now()->addDays(1)->timestamp]);
        if (!$token) {
            return response()->json([
                'login_status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        $role = $user->roles->pluck('name')[0];
        return response()->json([
            'login_status' => 'success',
            'user_id' => $user->id,
            'user_first_name' => $user->first_name,
            'user_status' => $user->status,
            'role' => $role,
            'token' => $token
        ]);
    }

    public function registerAdmin(Request $request)
    {
        try
        {
            DB::beginTransaction();
                $user = User::create([
                    'first_name' => ucwords($request->first_name),
                    'last_name' => ucwords($request->last_name),
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);
            DB::commit();
            $role = Role::where('name','admin')->first();
            $user->assignRole($role);
            $token = Auth::login($user);
            return response()->json([
                'admin_status' => 'success',
                'message' => 'Admin user created successfully.',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        } catch (Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'admin_status' => $e->getMessage()
            ]);
        }
    }
    private function checkDuplicate($request)
    {
        $stat = 'not found';
        $user = DB::table('users')
            ->where('first_name', $request->first_name)
            ->where('last_name', $request->last_name)
            ->first();
        if($user)
        {
            $addrCount = DB::table('user_address')->where('user_id', $user->id)->where('address', $request->address)->count();
            $bdayCount = DB::table('user_details')->where('user_id', $user->id)->where('birth_date', $request->formatted_bday)->count();
            if($addrCount > 0 && $bdayCount > 0)
            {
                $stat = 'found';
            }
        }
        return $stat;
    }
    public function register(Request $request)
    {
	$msg = $rtmsg = '';
        if($this->checkDuplicate($request) == 'found')
        {
            $msg = 'duplicate';
        } else
        {
        try
        {
            DB::beginTransaction();
                $user = User::create([
                    'id_number' => $request->id_number,
                    'first_name' => ucwords($request->first_name),
                    'last_name' => ucwords($request->last_name),
                    'middle_name' => ucwords($request->middle_name),
                    'email' => $request->email,
                    'extension_name' => ucwords($request->extension_name),
                ]);
            DB::commit();
            $contact = Contact::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'contact_person' => $request->contact_person,
                'contact_person_number' => $request->contact_person_number,
                'messenger' => $request->messenger,
            ]);
            $address = Address::create([
                'user_id' => $user->id,
                'province' => '044',
                'province_name' => $request->province,
                'municipality' => $request->municipality,
                'municipality_name' => $request->municipality_name,
                'barangay' => $request->barangay,
                'barangay_name' => $request->barangay_name,
                'address' => $request->address,
                'birth_place' => $request->birth_place,
                'district_no' => $request->district_no,
                'house_no' => $request->house_no,
                'street' => $request->street,
            ]);
            $benefit = Benefit::create([
                'user_id' => $user->id,
                'gsis' => $request->gsis,
                'sss' => $request->sss,
                'tin' => $request->tin,
                'philhealth' => $request->philhealth,
                'pension' => $request->pension,
                'association_id' => $request->association_id,
                'other_id' => $request->other_id,
            ]);
            $illness = Sickness::create([
                'user_id' => $user->id,
                'sickness' => $request->selected_illness,
            ]);
            $classification = Classification::create([
                'user_id' => $user->id,
                'classification' => $request->member_type,
            ]);
            $specialization = UserSpecialization::create([
                'user_id' => $user->id,
                'area' => $request->specialization_area,
            ]);
            $userService = UserService::create([
                'user_id' => $user->id,
                'service' => $request->user_services,
            ]);
            $userCompanion = UserCompanion::create([
                'user_id' => $user->id,
                'companion' => $request->user_companion,
            ]);
            $userHousing = UserHousing::create([
                'user_id' => $user->id,
                'type' => $request->housing,
            ]);
            $userInvolvement = UserInvolvement::create([
                'user_id' => $user->id,
                'activity' => $request->user_involvement,
            ]);
            $family = UserFamily::create([
                'user_id' => $user->id,
                'spouse_first_name' => $request->spouse_first_name,
                'spouse_middle_name' => $request->spouse_middle_name,
                'spouse_last_name' => $request->spouse_last_name,
                'spouse_extension_name' => $request->spouse_extension_name,
                'father_first_name' => $request->father_first_name,
                'father_middle_name' => $request->father_middle_name,
                'father_last_name' => $request->father_last_name,
                'father_extension_name' => $request->father_extension_name,
                'mother_first_name' => $request->mother_first_name,
                'mother_middle_name' => $request->mother_middle_name,
                'mother_last_name' => $request->mother_last_name,
                'mother_extension_name' => $request->mother_extension_name,
            ]);
            $userSocialProblem = UserSocialProblem::create([
                'user_id' => $user->id,
                'problem' => $request->user_social_problem,
            ]);
            $userEconProblem = UserEconomicProblem::create([
                'user_id' => $user->id,
                'problem' => $request->user_economic_problem,
            ]);
            $userHealth = UserHealthIssue::create([
                'user_id' => $user->id,
                'problem' => $request->user_health_issue,
            ]);
            $userEconProfile = UserEconomicStatus::create([
                'user_id' => $user->id,
                'source_of_income' => $request->user_income_source,
                'assets' => $request->user_assets,
                'income_range' => $request->income_range,
            ]);
            $filename = null;
            if(!empty($request->data))
            {
                $now = Carbon::now()->format('Y-m-d-H-i-s');
                $filename = $request->id_number.'-vaccine-card-'.$now.'.png';
                $img = Image::make($request->data)->resize(250, 180);
                $fullPath = public_path('images/id_cards/'.$filename);
                $img->save($fullPath);
            }
            $mStatus = $request->member_status;
            if(empty($mStatus))
            {
               $mStatus = "Active";
            }
            $detail = Detail::create([
                'user_id' => $user->id,
                'birth_date' => $request->formatted_bday,
                'religion' => $request->religion,
                'blood_type' => $request->blood_type,
                'education' => $request->education,
                'employment_status' => $request->employment_status,
                'member_status' => $mStatus,
                'civil_status' => $request->civil_status,
                'gender' => $request->gender,
                'identification' => $filename,
                'language' => $request->language,
                'ethnic_origin' => $request->ethnic_origin,
                'able_to_travel' => $request->able_to_travel,
                'active_in_politics' => $request->active_in_politics,
            ]);
            if(!empty($request->offspring))
            {
                foreach($request->offspring as $child)
		        {
        		    if(!empty($child['full_name']))
                    {
                        $dep = "";
                        if(isset($child['dependency'])) {
                            $dep = $child['dependency'];
                        }
                        $md = UserChildren::create([
                            'user_id' => $user->id,
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
                            'user_id' => $user->id,
                            'medicine' => $med['medicine'],
                            'dosage' => $dose,
                            'quantity' => $qty,
    		            ]);
        		    }
                }
            }
            $msg = 'success';
            $rtmsg = 'User registered successfully.';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = 'failed';
            $rtmsg = $e->getMessage();
	    }
	}
        return response()->json([
            'reg_status' => $msg,
            'regMsg' => $rtmsg
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

}
