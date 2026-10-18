<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\InviteUserRequest;
use App\Models\User;
use App\Services\InvitationService;
use App\Services\SecurityAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(Request $request) { Gate::authorize('viewAny', User::class); if(!$request->has('per_page')) $request->merge(['per_page'=>data_get($request->user()->preferences,'page_size',10)]); $filters = $request->validate(['q'=>['nullable','string','max:100'],'status'=>['nullable',\Illuminate\Validation\Rule::in(['invited','pending_2fa','active','inactive'])],'role'=>['nullable',\Illuminate\Validation\Rule::in(array_map(fn($role)=>$role->value,UserRole::cases()))],'per_page'=>['nullable',\Illuminate\Validation\Rule::in([10,20,50])]]); $query=User::query(); if($filters['q']??null) $query->where(fn($q)=>$q->where('email','like','%'.trim($filters['q']).'%')->orWhere('name','like','%'.trim($filters['q']).'%')); foreach(['status','role'] as $field) if($filters[$field]??null) $query->where($field,$filters[$field]); $users=$query->orderByDesc('created_at')->paginate((int)($filters['per_page']??10))->withQueryString(); $rolesForFilter=UserRole::cases(); return view('admin.users.index',compact('users','filters','rolesForFilter')); }
    public function create() { Gate::authorize('create', User::class); return view('admin.users.create',['roles'=>UserRole::cases()]); }
    public function store(InviteUserRequest $request, InvitationService $invitations): RedirectResponse { $user = $invitations->invite($request->user(), $request->validated('email'), $request->validated('role')); app(SecurityAudit::class)->record('account_invited','success',$request->user(),$request,null,['role'=>$user->role->value],$user); return redirect()->route('admin.users.index')->with('status','Akun terdaftar. Pengguna dapat meminta tautan aktivasi.'); }
    public function unlock(Request $request, User $user): RedirectResponse { Gate::authorize('update',$user); $user->forceFill(['failed_login_attempts'=>0,'last_failed_login_at'=>null,'locked_until'=>null,'auth_version'=>$user->auth_version+1])->save(); app(SecurityAudit::class)->record('login_unlocked','success',$request->user(),$request,null,['subject_user_id'=>$user->id]); return back()->with('status','Akun dibuka dan sesi lama dicabut.'); }
    public function status(Request $request, User $user): RedirectResponse { Gate::authorize('update',$user); $status = UserStatus::from((string)$request->validate(['status'=>['required',\Illuminate\Validation\Rule::in(array_map(fn($item)=>$item->value,UserStatus::cases()))]])['status']); if ($user->role === UserRole::SuperAdmin && $user->isActive() && $status !== UserStatus::Active && User::query()->where('role',UserRole::SuperAdmin)->where('status',UserStatus::Active)->count() < 2) return back()->withErrors(['status'=>'Super Administrator aktif terakhir tidak dapat dinonaktifkan.']); $user->forceFill(['status'=>$status,'auth_version'=>$user->auth_version+1])->save(); app(SecurityAudit::class)->record('account_status_changed','success',$request->user(),$request,null,['subject_user_id'=>$user->id,'status'=>$status->value]); return back()->with('status','Status akun diperbarui.'); }
}
