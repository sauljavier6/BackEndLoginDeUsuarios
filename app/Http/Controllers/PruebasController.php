<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;

class PruebasController extends Controller
{
    public function index() {
        $titulo  = 'Animales';
        $animales = ['Perro','Gato','Tigre','El controlador sirve'];
        
        return view('pruebas.index', array(
            'titulo' => $titulo, 
           'animales' => $animales
        )); 
    }
    public function testOrm(){
    /*
        $posts= Post::all();
        foreach ($posts as $post){
            echo $post->title.'<br>';
            echo "<span style='color:gray'>{$post->User->name}-{$post->Category->name}</span>"."<br>";
            echo $post->content.'<br>'; 
            echo '<hr>';
        }
    */   
        
    $categories = Category::all();
    foreach ($categories as $category){
        echo "<h1>{$category->name}</h1>";
        
        foreach ($category->post as $post){
            echo $post->title.'<br>';
            echo "<span style='color:gray'>{$post->User->name}-{$post->Category->name}</span>"."<br>";
            echo $post->content.'<br>'; 
            echo '<hr>';
        }
        
    }
        
        die();
    }
}
