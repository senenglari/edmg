<?php

namespace App\Model\Chatting;

use App\Model\UserManagement\UserModel;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    /**
     * @var string
     */
    protected $table = 'conversations';

    /**
     * @var string[]
     */
    protected $fillable = [
        'type',
        'last_message',
        'last_message_at',
    ];

    /**
     * Relasi ke user (customer)
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_user','conversation_id', 'user_id');
    }

    /**
     * Relasi ke messages
     * @return HasMany
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id')->orderBy('created_at', 'asc');
    }

    /**
     * Ambil pesan terakhir
     * @return HasOne
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id')->latest();
    }
}
