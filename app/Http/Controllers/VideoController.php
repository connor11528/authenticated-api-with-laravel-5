<?php

namespace App\Http\Controllers;

use App\Video;
use App\API\ApiHelper;
use App\Repositories\Repository;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    use ApiHelper;

    /**
    * @var Repository
    */
    protected $model;

    public function __construct(Video $video)
    {
        $this->model = new Repository($video);

        // Protect all routes except reading
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
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
        $this->beforeCreate($request);

        if(!$request->user()->channels()->find($request->get('channel_id', 0)))
        {
            return $this->errorForbidden('You can only add video to your own channels');
        }

        return $request->user()->videos()->create(
            $request->only($this->model->getModel()->fillable)
        );
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

        if(!$this->model->update($request->only($this->model->getModel()->fillable), $id))
        {
            return $this->errorBadRequest('Unable to update.');
        }

        return $this->model->find($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!$request->user()->videos()->find($id))
        {
            return $this->errorNotFound('Video not found.');
        }

        return $this->model->delete($id) ? $this->noContent() : $this->errorBadRequest();
    }
}
