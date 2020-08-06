<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RepublicRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Resources;
use App\Http\Resources\Republics as RepublicResource;

use App\User;
use App\Republic;
use App\Comment;

class RepublicController extends Controller {
  /*
   * Relationship One to One
   * User rents Republic
   * Republic can be rented by only 1 user
   */
    public function tenant($id) { //lista is locatarios
        $republic = Republic::findOrFail($id);
        $tenants = $republic->tenant()->get();
        return response()->json($tenants);
    }

    /*
     * Relationship One to Many
     * User announces Republic
     * A Republic can only be announced by 1 user
     */
    public function locator($id) { //locador
      $republic = Republic::findOrFail($id);
      return response()->json($republic->user);
    }

    /*
     * Relationship Many to Many
     * User favorites Republic
     * A Republic can be favorited by n Users
     */
     public function favoritedBy($id) {
       $republic = Republic::findOrFail($id);
       return response()->json($republic->favorites);
     }

    /*
     * Relationship One to Many
     * Republic owns Comment
     * Republic can own n Comments
     */
     public function owns($republic_id) {
       $republic = Republic::findOrFail($republic_id);
       $republic->owns($republic_id);
       return response()->json($comment);
     }

     public function listComments($id) {
       $response = [];
       $republic = Republic::findOrFail($id);
       $comments = Republic::findOrFail($id)->comments()->get();
       array_push( $response, ["republic" => $republic, "comments" => $comments]);
       return response()->json($response[0]);
     }

     /*
      * Basic CRUD for Republic
      * create, find, list, update, delete
      */

     //create a new republic
     public function createRepublic(RepublicRequest $request) {
       $republic = new Republic;
       $republic->createRepublic($request);

       return response()->json([$republic, 'Republica criada com sucesso!']);
     }

     //find a republic by id
     public function findRepublic($id) {
       $republic = Republic::findOrFail($id);
       return response()->json([$republic, 'Republica encontrada com sucesso!']);
     }

     //list all republics on the database
     public function listRepublic() {
       $republic = Republic::all();

       return response()->json([$republic]);
     }

     //update an existing republic
     public function updateRepublic(Request $request, $id) {
       $republic = Republic::find($id);
       $republic->updateRepublic($request);
       return response()->json([$republic, 'Republica atualizada com sucesso!']);
     }

     //destroy/delete an existing republic
     public function deleteRepublic($id) {
       Republic::destroy($id);

       return response()->json(['Republica deletada com sucesso!']);
     }

     /*
      * Busca com filtro para encontrar republicas usando queries do Eloquent
      *
      */
     public function filterRepublic(Request $request) {
       $query = Republic::query();

       if($request->has('title')) {
         $query->where('title', 'LIKE', '%' . $request->input('title') . '%');
       }

       if($request->has('address')) {
         $query->where('address', 'LIKE', '%' . $request->input('address') . '%');
       }

       if($request->has('comments')) {
         $query = Republic::with(['comments' => function($q) {
           $q->where('text', 'LIKE', '%');
         }])->get();
       }

       $query = $query->paginate(10);
       $republicResource = RepublicResource::collection($query->items());

       return response()->json($query);
     }

     /*
      * Retorna todas as republicas que foram soft deleted
      */
      public function findSoftDeletes() {
        $republics = Republic::onlyTrashed()->get();
        return response()->json($republics);
      }

      public function commentsCounter($id) {
        $count = Comment::with('republic')->where('republic_id', $id)->count();
        return response()->json($count);
      }
}
