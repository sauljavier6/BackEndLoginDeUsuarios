<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show', 
            'getImage', 'getPostsByCategory', 'getPostsByUser']]);
    }

    public function index() {
        $post = Post::all()->load('category');

        return response()->json([
                    'code' => 200,
                    'status' => 'succes',
                    'posts' => $post
                        ], 200);
    }

    public function show($id) {
        $post = Post::find($id);

        if (is_object($post)) {
            $data = [
                'code' => 200,
                'status' => 'succes',
                'posts' => $post
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La entrada no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        //Recoger datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            //Conseguir usuario identificado
            $user = $this->getIdentity($request);

            //Validar los datos 
            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required',
                        'image' => 'required'
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado el POST, Faltan datos'
                ];
            } else {
                //Guardar el articulo
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();

                $data = [
                    'code' => 400,
                    'status' => 'succes',
                    'post' => $post
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Envia los datos correctamente'
            ];
        }
        //Devolver respuesta
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) {
        //Recoger datos por post 
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        //datos para devolver
        $data = array(
            'code' => 400,
            'status' => 'error',
            'post' => 'datos enviados incorrectamente'
        );

        if (!empty($params_array)) {
            //Validar datos
            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required'
            ]);

            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }
            //Eliminar lo que no queremos actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            //Conseguir usuario identificado
            $user = $this->getIdentity($request);

            //buscar el registro
            $post = Post::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

            if (!empty($post) && is_object($post)) {
                //Actualizar el registro en concreto
                $post->update($params_array);

                //Devolver respuesta
                $data = array(
                    'code' => 200,
                    'status' => 'succes',
                    'post' => $post,
                    'changes' => $params_array
                );
            }
            /*
              $where = [
              'id' => $id,
              'user_id' => $user->sub
              ];

              $post = Post::updateOrCreate($where, $params_array);
             * 
             */
        }
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request) {
        //Conseguir usuario identificado
        $user = $this->getIdentity($request);

        //conseguir el registro
        $post = Post::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

        if (!empty($post)) {
            //borrarlo
            $post->delete();

            //devolver algo
            $data = [
                'code' => 200,
                'status' => 'succes',
                'post' => $post
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'El post no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    private function getIdentity($request) {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function upload(Request $request) {
        //Recoger la imagen de la peticion 
        $image = $request->file('file0');

        //Validar imagen 
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //Guardar la imagen 
        if (!$image || $validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName();

            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'succes',
                'image' => $image_name
            ];
        }

        //Devolver datos
        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        //comprobar si existe el fichero
        $isset = \Storage::disk('images')->exists($filename);

        if ($isset) {
            //conseguir la imagen 
            $file = \Storage::disk('images')->get($filename);

            //devolver la imagen 
            return new Response($file, 200);
        } else {

            //mostrar error 
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'La imagen no existe'
            ];
        }

        //Devolver datos
        return response()->json($data, $data['code']);
    }

    public function getPostsByCategory($id) {
        $posts = Post::where('category_id', $id)->get();

        return response()->json([
                    'status' => 'succes',
                    'posts' => $posts
                        ], 200);
    }

    public function getPostsByUser($id) {
        $posts = Post::where('user_id', $id)->get();

        return response()->json([
                    'status' => 'succes',
                    'posts' => $posts
                        ], 200);
    }

}
