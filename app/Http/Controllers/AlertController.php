<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * Return unread alerts for the current user.
     */
    public function unread(Request $request)
    {
        $user = $request->user();

        $limit = (int) $request->get('limit', 20);
        $limit = max(1, min($limit, 50));

        $query = Alert::unread()->orderByDesc('created_at');

        if ($user && !$user->isAdmin() && !$user->isExecutive()) {
            // Directorate-scoped users only see their directorate + global alerts
            if ($user->directorate_id) {
                $query->where(function ($q) use ($user) {
                    $q->where('directorate_id', $user->directorate_id)
                      ->orWhereNull('directorate_id');
                });
            } else {
                $query->whereNull('directorate_id');
            }
        }

        $unreadCount = (clone $query)->count();

        $alerts = $query
            ->limit($limit)
            ->get([
                'id',
                'type',
                'severity',
                'title',
                'message',
                'directorate_id',
                'metadata',
                'created_at',
            ]);

        return response()->json([
            'unread_count' => $unreadCount,
            'alerts' => $alerts,
        ]);
    }

    /**
     * Mark a single alert as read.
     */
    public function markRead(Request $request, Alert $alert)
    {
        $this->authorizeAlertAccess($request, $alert);

        if (!$alert->is_read) {
            $alert->forceFill([
                'is_read' => true,
            ])->save();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Dismiss an alert (removes it from "unread").
     */
    public function dismiss(Request $request, Alert $alert)
    {
        $this->authorizeAlertAccess($request, $alert);

        if (!$alert->is_dismissed) {
            $alert->forceFill([
                'is_dismissed' => true,
            ])->save();
        }

        return response()->json(['success' => true]);
    }

    private function authorizeAlertAccess(Request $request, Alert $alert): void
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if ($user->isAdmin() || $user->isExecutive()) {
            return;
        }

        // Directorate heads (and other non-admin users) can only act on their own directorate alerts + global alerts
        if ($alert->directorate_id === null) {
            return;
        }

        if ($user->directorate_id && (int) $alert->directorate_id === (int) $user->directorate_id) {
            return;
        }

        abort(403, 'You do not have permission to access this alert.');
    }
}
