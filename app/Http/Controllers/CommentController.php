<?php

namespace App\Http\Controllers;
use App\Comment;
use App\API\ApiHelper;
use App\Repositories\Repository;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use ApiHelper;
    
    /**
     * @var Repository
     */
    protected $model;

    /**
     * CommentController constructor.
     *
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->model = new Repository($comment);

        // Protect all except reading
        $this->middleware('auth:api', ['except' => ['index', 'show'] ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->model->with('user')->latest()->paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // run the validation
        $this->beforeCreate($request);
        return $request->user()->channels()
            ->create( $request->only($this->model->getModel()->fillable));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->model->with('user')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->beforeUpdate($request);

        // validate the channel id belongs to user
        if( ! $request->user()->channels()->find($id) ) {
            return $this->errorForbidden('You can only edit your channel.');
        }
        if (! $this->model->update($request->only($this->model->getModel()->fillable), $id) ) {
            return $this->errorBadRequest('Unable to update.');
        }
        
        return $this->model->find($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        // run before delete checks
        if (! $request->user()->channels()->find($id)) {
            return $this->errorNotFound('Channel not found.');
        }
        return $this->model->delete($id) ? $this->noContent() : $this->errorBadRequest();
    }
}