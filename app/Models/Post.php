<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {

    use HasFactory;

    protected $table = 'posts';
    protected $fillable = [
        'title', 'content', 'category_id'
        ];

    //Relacion de uno a muchos inversa (muchos a uno)
    public function user() {
        return $this->belongsTO('App\Models\User', 'user_id');
    }

    public function category() {
        return $this->belongsTO('App\Models\Category', 'category_id');
    }

}
