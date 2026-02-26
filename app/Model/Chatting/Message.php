<?php

namespace App\Model\Chatting;

use App\Model\UserManagement\UserModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    /**
     * @var string
     */
    protected $table = 'messages';

    /**
     * @var string[]
     */
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'is_read',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_read' => 'boolean'
    ];

    /**
     * Relasi ke conversation
     * @return BelongsTo
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /**
     * Relasi ke pengirim (user / admin)
     * @return BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(UserModel::class, 'sender_id');
    }
}
