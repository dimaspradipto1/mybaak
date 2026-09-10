<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\DataTables\UserDataTable;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Hash;
use RealRashid\SweetAlert\Facades\Alert;

use App\Imports\UsersImport;
use App\Exports\UsersTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserDataTable $dataTable)
    {
        $title = 'Pengguna';
        return $dataTable->render('pages.users.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Tambah Pengguna';
        $roles = Role::all();
        return view('pages.users.create', compact('title', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $data = [
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'role_id'        => $request->role_id,
            'is_active'      => $request->is_active ?? 1,
            'remember_token' => Str::random(60),
        ];

        User::create($data);

        Alert::success('success', 'user created successfully')
            ->autoclose(4000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa-solid fa-thumbs-up"></i>');
        return redirect()->route('users.index');
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
        $title = 'Edit Pengguna';
        $roles = Role::all();
        return view('pages.users.edit', compact('user', 'title', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'      => 'required|string|max:255|unique:users,name,' . $user->id,
            'email'     => 'required|email|max:255|unique:users,email,' . $user->id,
            'role_id'   => 'required|exists:roles,id',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required'    => 'Nama Lengkap wajib diisi',
            'name.unique'      => 'Nama pengguna sudah digunakan, tidak boleh duplikat',
            'email.required'   => 'Email wajib diisi',
            'email.unique'     => 'Email sudah terdaftar, tidak boleh duplikat',
            'role_id.required' => 'Role Akses wajib diisi',
        ]);

        $updateData = [
            'name'      => $request->name,
            'email'     => $request->email,
            'role_id'   => $request->role_id,
            'is_active' => $request->is_active ?? 1,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        Alert::success('success', 'data updated successfully')
            ->autoclose(4000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa-solid fa-thumbs-up"></i>');
        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        Alert::success('success', 'data deleted successfully')
            ->autoclose(4000)
            ->toToast()
            ->timerProgressBar()
            ->iconHtml('<i class="fa-solid fa-thumbs-up"></i>');
        return redirect()->route('users.index');
    }

    public function showUpdatePasswordForm($id)
    {
        $users = User::findOrFail($id);
        return view('pages.users.updatePassword', compact('users'));
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|min:6|confirmed',
        ], [
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password minimal 6 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($request->new_password);
        $user->save();

        Alert::success('success', 'Password updated successfully')
            ->autoclose(2000)
            ->toToast();
        return redirect()->route('users.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new UsersImport, $request->file('file'));
            
            Alert::success('Sukses', 'Data Pengguna berhasil diimpor')
                ->autoclose(4000)
                ->toToast();
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Terjadi kesalahan saat impor: ' . $e->getMessage())
                ->autoclose(5000)
                ->toToast();
        }

        return redirect()->back();
    }

    public function exportTemplate()
    {
        return Excel::download(new UsersTemplateExport, 'format_import_pengguna.xlsx');
    }
}
