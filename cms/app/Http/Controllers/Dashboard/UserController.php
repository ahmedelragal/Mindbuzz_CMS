<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $users = User::where('is_student', 0)->whereNot('role', 1)->whereNot('role', 2)->whereNot('role', 3)->paginate(10);
        // $users = User::role(['Admin', 'school'])->paginate(10);

        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $schools = School::all();
        return view('dashboard.users.create', compact('roles', 'schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'phone' => 'required|numeric',
            'roles' => 'required|array',
            'school_id' => [
                'nullable',
                'exists:schools,id',
                function ($attribute, $value, $fail) use ($request) {
                    $roles = $request->roles ?? []; // Ensure $roles is always an array

                    if (in_array('school', $roles) || in_array('Culture-Cordinator', $roles) || in_array('PracticalLife-Cordinator', $roles) || in_array('Math-Cordinator', $roles) || in_array('Arabic-Cordinator', $roles) || in_array('Phonics-Cordinator', $roles)) {
                        if (empty($value)) {
                            $fail('The school field is required when assigning the School Admin or Cordinator role.');
                        }
                    }
                }

            ],
        ]);
        if (in_array('Admin', $request->roles ?? []) && in_array('school', $request->roles ?? [])) {
            return redirect()->back()->with('error', 'The Super Admin and School Admin roles cannot be assigned to the same user.');
        }
        if ((in_array('Phonics-Cordinator', $request->roles ?? []) || in_array('Arabic-Cordinator', $request->roles ?? []) || in_array('Math-Cordinator', $request->roles ?? []) || in_array('PracticalLife-Cordinator', $request->roles ?? []) || in_array('Culture-Cordinator', $request->roles ?? [])) && (in_array('school', $request->roles ?? []) || in_array('Admin', $request->roles ?? []))) {
            return redirect()->back()->with('error', 'A coordinator cannot be a school admin or super admin');
        }
        // dd($request->all());

        // $user = User::create([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'phone' => $request->phone,
        //     'role' => 6,
        //     'password' => Hash::make($request->password)
        // ]);

        if (in_array('Phonics-Cordinator', $request->roles ?? []) || in_array('Arabic-Cordinator', $request->roles ?? []) || in_array('Math-Cordinator', $request->roles ?? []) || in_array('PracticalLife-Cordinator', $request->roles ?? []) || in_array('Culture-Cordinator', $request->roles ?? [])) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
                'phone' => $request->phone,
                'school_id' => $request->school_id,
                'role' => 6,
            ]);
            $user->syncRoles($request->roles);
            $user->assignRole('Cordinator');
        } else if (in_array('school', $request->roles ?? [])) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'school_id' => $request->school_id,
                'role' => 6,
            ]);
            $user->roles()->detach();
            $user->assignRole('school');
        } else if (in_array('Admin', $request->roles ?? [])) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'school_id' => null,
                'role' => 6,
            ]);
            $user->roles()->detach();
            $user->assignRole('Admin');
        }
        // $user->syncRoles($request->roles);
        // dd($user->hasRole('Teacher'));
        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $schools = School::all();
        $userRoles = $user->roles->pluck('name')->toArray();

        return view('dashboard.users.edit', compact('user', 'roles', 'userRoles', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|numeric',
            'roles' => 'required|array',
            'school_id' => [
                'nullable',
                'exists:schools,id',
                function ($attribute, $value, $fail) use ($request) {
                    $roles = $request->roles ?? []; // Ensure $roles is always an array
                    if (in_array('school', $roles) || in_array('Culture-Cordinator', $roles) || in_array('PracticalLife-Cordinator', $roles) || in_array('Math-Cordinator', $roles) || in_array('Arabic-Cordinator', $roles) || in_array('Phonics-Cordinator', $roles)) {
                        if (empty($value)) {
                            $fail('The school field is required when assigning the School Admin or Cordinator role.');
                        }
                    }
                }
            ],
        ]);

        if (in_array('Admin', $request->roles ?? []) && in_array('school', $request->roles ?? [])) {
            return redirect()->back()->with('error', 'The Super Admin and School Admin roles cannot be assigned to the same user.');
        }
        if ((in_array('Phonics-Cordinator', $request->roles ?? []) || in_array('Arabic-Cordinator', $request->roles ?? []) || in_array('Math-Cordinator', $request->roles ?? []) || in_array('PracticalLife-Cordinator', $request->roles ?? []) || in_array('Culture-Cordinator', $request->roles ?? [])) && (in_array('school', $request->roles ?? []) || in_array('Admin', $request->roles ?? []))) {
            return redirect()->back()->with('error', 'A coordinator cannot be a school admin or super admin');
        }
        if (in_array('Phonics-Cordinator', $request->roles ?? []) || in_array('Arabic-Cordinator', $request->roles ?? []) || in_array('Math-Cordinator', $request->roles ?? []) || in_array('PracticalLife-Cordinator', $request->roles ?? []) || in_array('Culture-Cordinator', $request->roles ?? [])) {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
                'phone' => $request->phone,
                'school_id' => $request->school_id
            ]);
            $user->syncRoles($request->roles);
            $user->assignRole('Cordinator');
        } else if (in_array('school', $request->roles ?? [])) {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
                'phone' => $request->phone,
                'school_id' => $request->school_id
            ]);
            $user->roles()->detach();
            $user->assignRole('school');
        } else if (in_array('Admin', $request->roles ?? [])) {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
                'phone' => $request->phone,
                'school_id' => null
            ]);
            $user->roles()->detach();
            $user->assignRole('Admin');
        }
        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }


    public function massDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if ($ids) {
            User::whereIn('id', $ids)->delete();
            $redirectUrl = session('users_previous_url', route('users.index'));
            return redirect($redirectUrl)->with('success', 'Users deleted successfully.');
        }
        return redirect()->route('users.index')->with('error', 'No Users selected.');
    }
    public function makeAdmin($userId)
    {
        $user = User::find($userId);
        $redirectUrl = session('users_previous_url', route('users.index'));

        if ($user->hasRole('Admin')) {
            return redirect($redirectUrl)->with('error', 'User is Already A Super Admin');
        }

        if ($user->hasRole('school')) {
            $user->removeRole('school');
            $user->school_id = null;
            $user->save();
        }
        $user->assignRole('Admin');
        return redirect($redirectUrl)->with('success', 'User is Now A Super Admin');
    }
    public function removeAdmin($userId)
    {
        $user = User::find($userId);
        $redirectUrl = session('users_previous_url', route('users.index'));
        if ($user->hasRole('Admin')) {
            $user->removeRole('Admin');
            return redirect($redirectUrl)->with('success', 'Admin Role Removed from User');
        } else {
            return redirect($redirectUrl)->with('error', 'User is Not An Admin');
        }
    }
    public function makeSchoolAdmin(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'school_id' => 'required|exists:schools,id',
        ]);
        $user = User::find($request->user_id);
        $redirectUrl = session('users_previous_url', route('users.index'));
        $school = School::find($request->school_id);
        if ($user->hasRole('Admin')) {
            return redirect($redirectUrl)->with('error', 'Please Remove Super Admin Role First');
        }
        if ($user->hasRole('school')) {
            return redirect($redirectUrl)->with('error', 'User is Already A School Admin for ' . $school->name);
        } else {
            $user->assignRole('school');
            $user->school_id = $request->school_id;
            $user->save();
            return redirect($redirectUrl)->with('success', 'User is Now A School Admin For ' . $school->name);
        }
    }
    public function removeSchoolAdmin($userId)
    {
        $user = User::find($userId);
        $redirectUrl = session('users_previous_url', route('users.index'));
        if ($user->hasRole('school')) {
            $user->removeRole('school');
            $user->school_id = null;
            $user->save();
            return redirect($redirectUrl)->with('success', 'School Admin Role Removed from User');
        } else {
            return redirect($redirectUrl)->with('error', 'User is Not A School Admin');
        }
    }
}
