<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Http\Requests\NotificationRequest;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::with(['user'])->get();
        return NotificationResource::collection($notifications);
    }

    public function store(NotificationRequest $request)
    {
        $notification = Notification::create($request->validated());
        return new NotificationResource($notification->load(['user']));
    }

    public function show(Notification $notification)
    {
        return new NotificationResource($notification->load(['user']));
    }

    public function update(NotificationRequest $request, Notification $notification)
    {
        $notification->update($request->validated());
        return new NotificationResource($notification->load(['user']));
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return response()->json(['message' => 'Notification deleted successfully.']);
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['is_read' => true]);
        return new NotificationResource($notification->load(['user']));
    }

    public function markAllAsRead()
    {
        auth()->user()->notifications()->update(['is_read' => true]);
        return response()->json(['message' => 'All notifications marked as read.']);
    }
} 