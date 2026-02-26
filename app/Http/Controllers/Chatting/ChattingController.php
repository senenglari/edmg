<?php

namespace App\Http\Controllers\Chatting;

use App\Model\Chatting\Conversation;
use App\Model\Chatting\Message;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Throwable;

class ChattingController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function open(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:sys_users,id'
        ]);

        $authUserId   = auth()->id();
        $targetUserId = $request->user_id;

        if ($authUserId == $targetUserId) {
            abort(400, 'Invalid target user');
        }

        $conversation = Conversation::where('type', 'private')
            ->whereHas('users', fn ($q) => $q->where('user_id', $authUserId))
            ->whereHas('users', fn ($q) => $q->where('user_id', $targetUserId))
            ->first();

        if (!$conversation) {
            $conversation = DB::transaction(function () use ($authUserId, $targetUserId) {

                $conversation = Conversation::create([
                    'type' => 'private'
                ]);

                $conversation->users()->attach([
                    $authUserId,
                    $targetUserId
                ]);

                return $conversation;
            });
        }

        abort_if(
            !$conversation->users->contains($authUserId),
            403
        );

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $authUserId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::where('conversation_id', $conversation->id)
            ->with('sender:id,name')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages'        => $messages
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message'         => 'required|string'
        ]);


        $authUserId = auth()->id();

        // 🔐 Security check: pastikan user adalah member conversation
        $conversation = Conversation::where('id', $request->conversation_id)
            ->whereHas('users', function ($q) use ($authUserId) {
                $q->where('user_id', $authUserId);
            })
            ->firstOrFail();

        DB::transaction(function () use ($request, $authUserId, $conversation, &$message) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $authUserId,
                'message'         => $request->message,
                'is_read'         => false
            ]);

            $conversation->update([
                'last_message'    => $message->message,
                'last_message_at' => $message->created_at,
            ]);
        });

        return response()->json([
            'id'         => $message->id,
            'message'    => $message->message,
            'sender_id'  => $message->sender_id,
            'created_at' => $message->created_at->toDateTimeString()
        ]);
    }

    /**
     * List semua user untuk sidebar chat
     */
    public function users()
    {
        $authUserId = auth()->id();

        $users = User::query()
            ->where('sys_users.id', '!=', $authUserId)
            ->select([
                'sys_users.id',
                'sys_users.name',

                // unread count
                DB::raw('
                (
                    SELECT COUNT(*)
                    FROM messages m
                    JOIN conversation_user cu ON cu.conversation_id = m.conversation_id
                    WHERE cu.user_id = sys_users.id
                      AND m.sender_id = sys_users.id
                      AND m.is_read = 0
                      AND m.conversation_id IN (
                          SELECT c.id
                          FROM conversations c
                          JOIN conversation_user cu2 ON cu2.conversation_id = c.id
                          WHERE cu2.user_id = '.$authUserId.'
                          AND c.type = "private"
                      )
                ) AS unread_count
            '),

                // last message time
                DB::raw('
                (
                    SELECT MAX(m2.created_at)
                    FROM messages m2
                    JOIN conversation_user cu3 ON cu3.conversation_id = m2.conversation_id
                    WHERE cu3.user_id = sys_users.id
                      AND m2.conversation_id IN (
                          SELECT c2.id
                          FROM conversations c2
                          JOIN conversation_user cu4 ON cu4.conversation_id = c2.id
                          WHERE cu4.user_id = '.$authUserId.'
                          AND c2.type = "private"
                      )
                ) AS last_message_at
            '),
                DB::raw("
                (
                    SELECT m3.message
                    FROM messages m3
                    JOIN conversation_user cu5 ON cu5.conversation_id = m3.conversation_id
                    JOIN conversation_user cu6 ON cu6.conversation_id = m3.conversation_id
                    JOIN conversations c3 ON c3.id = m3.conversation_id
                    WHERE cu5.user_id = sys_users.id
                      AND cu6.user_id = {$authUserId}
                      AND c3.type = 'private'
                    ORDER BY m3.created_at DESC
                    LIMIT 1
                ) AS last_message
            ")
            ])
            ->orderByDesc('unread_count')
            ->orderByDesc('last_message_at')
            ->limit(2)
            ->get();

        return response()->json($users);
    }

}
