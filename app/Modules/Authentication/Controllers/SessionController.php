<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    /** List all active sessions for the authenticated user */
    public function index(Request $request): Response
    {
        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn ($session) => [
                'id'            => $session->id,
                'ip_address'    => $session->ip_address,
                'user_agent'    => $session->user_agent,
                'last_activity' => $session->last_activity,
                'is_current'    => $session->id === $request->session()->getId(),
            ]);

        return Inertia::render('Candidate/Settings/Sessions', [
            'sessions' => $sessions,
        ]);
    }

    /** Revoke a specific session by ID */
    public function destroy(Request $request, string $sessionId): RedirectResponse
    {
        // Prevent revoking someone else's session
        $deleted = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->delete();

        abort_unless($deleted > 0, 403);

        activity()->causedBy($request->user())->log("Session revoked: {$sessionId}");

        return back()->with('status', __('auth.session_revoked'));
    }

    /** Revoke all sessions except the current one */
    public function destroyAll(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        activity()->causedBy($request->user())->log('All other sessions revoked');

        return back()->with('status', __('auth.all_sessions_revoked'));
    }
}
