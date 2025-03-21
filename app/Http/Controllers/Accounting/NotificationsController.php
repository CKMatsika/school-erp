<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\NotificationLog;
use App\Models\Accounting\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = NotificationTemplate::all();
        $logs = NotificationLog::orderBy('created_at', 'desc')->paginate(20);
        
        return view('accounting.notifications.index', compact('templates', 'logs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounting.notifications.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:email,sms,whatsapp',
            'event' => 'required|string|max:100',
            'subject' => 'nullable|required_if:type,email|string|max:255',
            'content' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Get school ID if available
        $schoolId = null;
        if (auth()->user()->school) {
            $schoolId = auth()->user()->school->id;
        }

        NotificationTemplate::create([
            'school_id' => $schoolId,
            'name' => $request->name,
            'type' => $request->type,
            'event' => $request->event,
            'subject' => $request->subject,
            'content' => $request->content,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('accounting.notifications.index')
            ->with('success', 'Notification template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(NotificationTemplate $notification)
    {
        $logs = NotificationLog::where('template_id', $notification->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('accounting.notifications.show', compact('notification', 'logs'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NotificationTemplate $notification)
    {
        return view('accounting.notifications.edit', compact('notification'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NotificationTemplate $notification)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:email,sms,whatsapp',
            'event' => 'required|string|max:100',
            'subject' => 'nullable|required_if:type,email|string|max:255',
            'content' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $notification->update([
            'name' => $request->name,
            'type' => $request->type,
            'event' => $request->event,
            'subject' => $request->subject,
            'content' => $request->content,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('accounting.notifications.index')
            ->with('success', 'Notification template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NotificationTemplate $notification)
    {
        // Check if there are logs for this template
        if ($notification->logs()->count() > 0) {
            // Start a transaction to handle deleting related logs
            DB::beginTransaction();
            
            try {
                // Delete related logs
                $notification->logs()->delete();
                
                // Delete the template
                $notification->delete();
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->route('accounting.notifications.index')
                    ->with('error', 'Error deleting notification template: ' . $e->getMessage());
            }
        } else {
            // No logs, just delete the template
            $notification->delete();
        }

        return redirect()->route('accounting.notifications.index')
            ->with('success', 'Notification template deleted successfully.');
    }

    /**
     * Send a test notification.
     */
    public function sendTest(Request $request, NotificationTemplate $notification)
    {
        $request->validate([
            'recipient' => 'required|string',
        ]);

        try {
            // Log the test notification
            NotificationLog::create([
                'school_id' => $notification->school_id,
                'template_id' => $notification->id,
                'event' => 'test_' . $notification->event,
                'recipient_type' => 'test',
                'recipient_id' => 0,
                'channel' => $notification->type,
                'to' => $request->recipient,
                'subject' => $notification->subject,
                'content' => $notification->renderContent(['test' => 'This is a test notification']),
                'status' => 'sent',
            ]);

            // Here you would actually send the notification
            // This depends on your notification setup

            return redirect()->route('accounting.notifications.show', $notification)
                ->with('success', 'Test notification sent successfully.');
        } catch (\Exception $e) {
            return redirect()->route('accounting.notifications.show', $notification)
                ->with('error', 'Error sending test notification: ' . $e->getMessage());
        }
    }
}