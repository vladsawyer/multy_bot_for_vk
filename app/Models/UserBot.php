<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBot extends Model
{
    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'voice' => 'man',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['vk_id', 'voice'];
}
