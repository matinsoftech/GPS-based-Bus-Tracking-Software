<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'type' => ['nullable', 'string', 'max:50'],
            'unread' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = $user->notifications()
            ->orderByDesc('created_at');

        $query = $this->scopeByRole($query, $user);

        if (! empty($validated['type'])) {
            $query->where('data->type', $validated['type']);
        }

        if (isset($validated['unread']) && $validated['unread']) {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate($validated['per_page'] ?? 20);

        $unreadQuery = $user->unreadNotifications();
        $unreadQuery = $this->scopeByRole($unreadQuery, $user);
        $unreadCount = $unreadQuery->count();

        return response()->json([
            'message' => 'Notifications.',
            'data' => $notifications->through(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? null,
                'title' => $notification->data['title'] ?? null,
                'message' => $notification->data['message'] ?? null,
                'data' => collect($notification->data)->except(['type', 'title', 'message'])->toArray(),
                'read_at' => $notification->read_at?->toIso8601String(),
                'created_at' => $notification->created_at?->toIso8601String(),
            ]),
            'unread_count' => $unreadCount,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ],
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->unreadNotifications();
        $query = $this->scopeByRole($query, $user);
        $count = $query->count();

        return response()->json([
            'message' => 'Unread notification count.',
            'data' => [
                'count' => $count,
            ],
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $notification = $user->notifications()
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return response()->json([
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => [
                'id' => $notification->id,
                'read_at' => $notification->fresh()->read_at?->toIso8601String(),
            ],
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->unreadNotifications();
        $query = $this->scopeByRole($query, $user);
        $count = $query->count();

        $query->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read.',
            'data' => [
                'marked_count' => $count,
            ],
        ]);
    }

    private function scopeByRole(Builder|MorphMany $query, $user): Builder|MorphMany
    {
        if ($user->hasRole('Super Admin')) {
            return $query;
        }

        if ($user->hasRole('Driver')) {
            $routeIds = $user->driver?->routes()->pluck('driver_route.route_id') ?? collect();

            if ($routeIds->isEmpty()) {
                return $query->whereRaw('0 = 1');
            }

            return $query->where(function (Builder $q) use ($routeIds) {
                foreach ($routeIds as $routeId) {
                    $q->orWhereRaw("json_extract(data, '$.route_id') = ?", [$routeId]);
                }
            });
        }

        if ($user->hasRole('School Admin')) {
            $schoolId = $user->school_id;

            if (! $schoolId) {
                return $query->whereRaw('0 = 1');
            }

            $routeIds = Route::where('school_id', $schoolId)->pluck('id');

            if ($routeIds->isEmpty()) {
                return $query->whereRaw('0 = 1');
            }

            return $query->where(function (Builder $q) use ($routeIds) {
                foreach ($routeIds as $routeId) {
                    $q->orWhereRaw("json_extract(data, '$.route_id') = ?", [$routeId]);
                }
            });
        }

        if ($user->hasRole('Parent')) {
            $studentRouteIds = Student::whereHas('parent', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->pluck('route_id')->filter();

            if ($studentRouteIds->isEmpty()) {
                return $query->whereRaw('0 = 1');
            }

            return $query->where(function (Builder $q) use ($studentRouteIds) {
                foreach ($studentRouteIds as $routeId) {
                    $q->orWhereRaw("json_extract(data, '$.route_id') = ?", [$routeId]);
                }
            });
        }

        return $query->whereRaw('0 = 1');
    }
}
