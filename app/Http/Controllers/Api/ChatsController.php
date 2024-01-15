<?php

namespace App\Http\Controllers\Api;


use App\Events\MessageSent;
use App\Models\Message;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use Exception;

class ChatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getMessages()
    {
        return Message::with('user')->orderBy('created_at', 'asc')->get();
    }

    public function sendMessage(Request $request)
    {
        $user = Auth::user();
        $message = $user->messages()->create([
            'message' => $request->message
        ]);
        broadcast(new MessageSent($user, $message))->toOthers();
        return ['status' => 'success'];
    }
}

