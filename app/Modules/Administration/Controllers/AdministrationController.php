<?php

namespace App\Modules\Administration\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Administration\Requests\SettingRequest;
use App\Modules\Administration\Requests\SuspendUserRequest;
use App\Modules\Administration\Services\AdministrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdministrationController extends Controller
{
    public function __construct(private readonly AdministrationService $service) {}
    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            $request->user()->hasAnyRole(['Super Admin', 'Platform Admin'])
                || $request->user()->can('manage_administration'),
            403,
        );
    }
    public function overview(Request $request): JsonResponse { $this->authorizeAdmin($request); return response()->json(['overview' => $this->service->overview()]); }
    public function users(Request $request): JsonResponse { $this->authorizeAdmin($request); return response()->json(['users' => $this->service->users($request->string('status')->toString() ?: null)]); }
    public function suspend(SuspendUserRequest $request, User $user): JsonResponse { $this->authorizeAdmin($request); return response()->json(['suspension' => $this->service->suspend($request->user(), $user, $request->validated('reason'), $request->validated('suspended_until'))], 201); }
    public function lift(Request $request, User $user): JsonResponse { $this->authorizeAdmin($request); $this->service->liftSuspension($request->user(), $user); return response()->json(['message' => 'Suspension levée.']); }
    public function settings(Request $request): JsonResponse { $this->authorizeAdmin($request); return response()->json(['settings' => $this->service->settings($request->string('group')->toString() ?: null)]); }
    public function putSetting(SettingRequest $request, string $key): JsonResponse { $this->authorizeAdmin($request); return response()->json(['setting' => $this->service->putSetting($key, $request->validated('value'), $request->validated('group', 'general'))]); }
    public function auditLogs(Request $request): JsonResponse { $this->authorizeAdmin($request); return response()->json(['logs' => $this->service->auditLogs($request->string('log_name')->toString() ?: null)]); }
}