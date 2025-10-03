<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAccessController extends Controller
{
    public function saveHours(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'No autenticado'], 401);
        }

        $data = $request->validate([
            'start_hour' => 'nullable|integer|min:0|max:23',
            'end_hour' => 'nullable|integer|min:0|max:23',
        ]);

        $user->access_start_hour = $data['start_hour'] ?? null;
        $user->access_end_hour = $data['end_hour'] ?? null;
        $user->save();

        return response()->json(['success' => true]);
    }
}