<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function getUserNotifications(Request $request)
    {
        try {
            $user = Auth::id(); // Get the authenticated user

            if (! $user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Fetch notifications for sales user
            $notifications = Notification::where(function ($query) use ($user) {
                $query->where('user_id', $user);
            })
                ->select('id', 'title', 'description', 'created_at', 'seenByUser')
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedNotifications = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    // 'user_id'     => $user,
                    'title' => $notification->title,
                    'description' => $notification->description,
                    'time' => Carbon::parse($notification->created_at)->format('h:i A'),
                    'created_at' => Carbon::parse($notification->created_at),
                    'is_seen' => (bool) $notification->seenByUser,
                ];
            });

            return response()->json([
                'notifications' => $formattedNotifications,
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching notifications: '.$e->getMessage());

            return response()->json([
                'message' => 'Something went wrong while fetching notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function showNotification($id)
    {
        try {
            $user = Auth::id();

            $notification = Notification::find($id);

            if (! $notification) {
                return response()->json(['message' => 'Notification not found'], 404);
            }

            if (! $notification->seenByUser) {
                $notification->seenByUser = true;
                $notification->save();
            }

            return response()->json([
                'data' => $notification,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error showing notification ID '.$id.': '.$e->getMessage());

            return response()->json([
                'message' => 'Something went wrong while fetching the notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function seenNotification(Request $request)
    {
        try {
            $updated = Notification::where('id', $request->id)->first();

            if ($updated) {
                $updated->seenByUser = 1;
                $updated->save();

                return response()->json([
                    'message' => 'Notification status updated successfully.',
                ], 200);
            }

            return response()->json([
                'message' => 'Failed to update notification status.',
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the notification status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function clearAll()
    {
        try {
            $userId = Auth::id();

            if (! $userId) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Delete all notifications for the authenticated user
            Notification::where('user_id', $userId)->delete();

            return response()->json([
                'message' => 'Notifications cleared successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error clearing notifications for user ID '.($userId ?? 'unknown').': '.$e->getMessage());

            return response()->json([
                'message' => 'Something went wrong while clearing notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
