<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->paginate(15),
        ]);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca, lalu buka shipment-nya.
     * Hanya notifikasi milik user yang login yang bisa ditemukan (selain itu 404).
     */
    public function show(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $shipmentId = $notification->data['shipment_id'] ?? null;

        if ($shipmentId && Shipment::whereKey($shipmentId)->exists()) {
            return redirect()->route('shipments.show', $shipmentId);
        }

        return redirect()->route('notifications.index');
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }
}