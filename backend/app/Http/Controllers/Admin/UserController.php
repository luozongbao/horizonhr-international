<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateAdminRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Jobs\SendEnterpriseActivatedJob;
use App\Models\Admin;
use App\Models\Application;
use App\Models\Interview;
use App\Models\Resume;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/users
    // Paginated list with optional filters: role, status, search, enterprise_status
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = User::query()
            ->with(['student', 'enterprise', 'admin'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('role')) {
            $query->where('role', $request->query('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('enterprise_status')) {
            $query->where('enterprise_status', $request->query('enterprise_status'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->query('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', $search)
                  ->orWhereHas('student', fn ($s) => $s->where('name', 'like', $search))
                  ->orWhereHas('enterprise', fn ($e) => $e->where('company_name', 'like', $search))
                  ->orWhereHas('admin', fn ($a) => $a->where('name', 'like', $search));
            });
        }

        $paginated = $query->paginate($perPage);

        $items = $paginated->getCollection()->map(fn (User $user) => [
            'id'             => $user->id,
            'email'          => $user->email,
            'role'           => $user->role,
            'status'         => $user->status,
            'email_verified' => $user->email_verified,
            'created_at'     => $user->created_at,
            'last_login_at'  => $user->last_login_at,
            'display_name'   => match ($user->role) {
                'student'    => $user->student?->name,
                'enterprise' => $user->enterprise?->company_name,
                'admin'      => $user->admin?->name,
                default      => null,
            },
            'enterprise_status' => $user->enterprise_status,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/users/{id}
    // Full user detail with role profile + stats
    // ─────────────────────────────────────────────────────────────────────────

    public function show(int $id): JsonResponse
    {
        $user = User::with(['student', 'enterprise', 'admin'])->find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'User not found.'],
            ], 404);
        }

        $profile = match ($user->role) {
            'student'    => $user->student,
            'enterprise' => $user->enterprise,
            'admin'      => $user->admin,
            default      => null,
        };

        $stats = [];
        if ($user->role === 'student' && $user->student) {
            $studentId = $user->student->id;
            $stats = [
                'resumes_count'      => Resume::where('student_id', $studentId)->count(),
                'applications_count' => Application::where('student_id', $studentId)->count(),
                'interviews_count'   => Interview::where('student_id', $studentId)->count(),
            ];
        } elseif ($user->role === 'enterprise' && $user->enterprise) {
            $enterpriseId = $user->enterprise->id;
            $stats = [
                'jobs_count'         => $user->enterprise->jobs()->count(),
                'applications_count' => Application::whereHas('job', fn ($q) => $q->where('enterprise_id', $enterpriseId))->count(),
                'interviews_count'   => Interview::where('enterprise_id', $enterpriseId)->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                => $user->id,
                'email'             => $user->email,
                'role'              => $user->role,
                'status'            => $user->status,
                'enterprise_status' => $user->enterprise_status,
                'email_verified'    => $user->email_verified,
                'prefer_lang'       => $user->prefer_lang,
                'last_login_at'     => $user->last_login_at,
                'last_login_ip'     => $user->last_login_ip,
                'created_at'        => $user->created_at,
                'profile'           => $profile,
                'stats'             => $stats,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUT /api/users/{id}/status
    // Change user status with transition validation
    // ─────────────────────────────────────────────────────────────────────────

    public function updateStatus(UpdateUserStatusRequest $request, int $id): JsonResponse
    {
        $admin = $request->user();

        if ($admin->id === $id) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'FORBIDDEN', 'message' => 'You cannot change your own status.'],
            ], 403);
        }

        $user = User::find($id);
        if (! $user) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'User not found.'],
            ], 404);
        }

        $newStatus  = $request->status;
        $oldStatus  = $user->status;

        // Deleted accounts cannot be restored
        if ($oldStatus === 'deleted') {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'INVALID_TRANSITION', 'message' => 'Deleted accounts cannot be restored.'],
            ], 422);
        }

        // Validate allowed transitions
        $allowed = [
            'pending'   => ['active', 'deleted'],
            'active'    => ['suspended', 'deleted'],
            'suspended' => ['active', 'deleted'],
        ];

        if (! in_array($newStatus, $allowed[$oldStatus] ?? [])) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INVALID_TRANSITION',
                    'message' => "Cannot transition from '{$oldStatus}' to '{$newStatus}'.",
                ],
            ], 422);
        }

        $user->update(['status' => $newStatus]);

        // Revoke all Sanctum tokens when suspending or deleting
        if (in_array($newStatus, ['suspended', 'deleted'])) {
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'data'    => ['id' => $user->id, 'status' => $user->status],
            'message' => "User status updated to '{$newStatus}'.",
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUT /api/users/{id}/activate-enterprise
    // Approve a pending enterprise account
    // ─────────────────────────────────────────────────────────────────────────

    public function activateEnterprise(int $id, Request $request): JsonResponse
    {
        $user = User::with('enterprise')->find($id);

        if (! $user || $user->role !== 'enterprise') {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'Enterprise user not found.'],
            ], 404);
        }

        if ($user->enterprise_status !== 'pending') {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INVALID_STATE',
                    'message' => 'Enterprise account is not in pending state.',
                ],
            ], 422);
        }

        $user->update([
            'enterprise_status' => 'enterprise_verified',
            'status'            => 'active',
        ]);

        dispatch(new SendEnterpriseActivatedJob($user));

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                => $user->id,
                'enterprise_status' => 'enterprise_verified',
                'status'            => 'active',
            ],
            'message' => 'Enterprise account activated successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/users/admin
    // Create a new admin account
    // ─────────────────────────────────────────────────────────────────────────

    public function createAdmin(CreateAdminRequest $request): JsonResponse
    {
        // Auto-generate password if not provided
        $password = $request->password ?? Str::password(16);

        $user = User::create([
            'role'           => 'admin',
            'email'          => $request->email,
            'password'       => Hash::make($password),
            'status'         => 'active',
            'email_verified' => true,
            'prefer_lang'    => 'en',
        ]);

        Admin::create([
            'user_id' => $user->id,
            'name'    => $request->name,
            'phone'   => $request->phone,
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'    => $user->id,
                'email' => $user->email,
                'role'  => 'admin',
                // Only return generated password once (if auto-generated)
                'generated_password' => $request->password ? null : $password,
            ],
            'message' => 'Admin account created successfully.',
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE /api/users/{id}
    // Soft-delete: set status=deleted and revoke all tokens
    // ─────────────────────────────────────────────────────────────────────────

    public function destroy(int $id, Request $request): JsonResponse
    {
        $admin = $request->user();

        if ($admin->id === $id) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'FORBIDDEN', 'message' => 'You cannot delete your own account.'],
            ], 403);
        }

        $user = User::find($id);
        if (! $user) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_FOUND', 'message' => 'User not found.'],
            ], 404);
        }

        if ($user->status === 'deleted') {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'ALREADY_DELETED', 'message' => 'User is already deleted.'],
            ], 422);
        }

        $user->update(['status' => 'deleted']);
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'User has been deleted.',
        ]);
    }
}
